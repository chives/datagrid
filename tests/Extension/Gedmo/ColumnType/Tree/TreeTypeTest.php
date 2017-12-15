<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Component\DataGrid\Tests\Extension\Gedmo\ColumnType;

use FSi\Component\DataGrid\Tests\Fixtures\EntityTree;
use FSi\Component\DataGrid\Tests\Fixtures\EntityManagerMock;
use FSi\Component\DataGrid\Tests\Fixtures\EventManagerMock;
use FSi\Component\DataGrid\Extension\Gedmo\ColumnType\Tree;
use FSi\Component\DataGrid\Extension\Core\ColumnTypeExtension\DefaultColumnOptionsExtension;
use FSi\Component\DataGrid\DataMapper\DataMapperInterface;
use FSi\Component\DataGrid\DataGridInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Persistence\ManagerRegistry;
use Gedmo\Tree\TreeListener;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Gedmo\Tree\Strategy;

class TreeTypeTest extends TestCase
{
    public function testWrongValue()
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $column = new Tree($registry);
        $column->setName('tree');
        $column->initOptions();

        $extension = new DefaultColumnOptionsExtension();
        $extension->initOptions($column);

        $object = 'This is string, not object';

        $this->expectException('InvalidArgumentException');
        $column->getValue($object);
    }

    public function testGetValue()
    {
        $dataGrid = $this->createMock(DataGridInterface::class);
        $registry = $this->getManagerRegistry();
        $dataMapper = $this->createMock(DataMapperInterface::class);

        $dataMapper->expects($this->any())
            ->method('getData')
            ->will($this->returnValue(new EntityTree('foo')));

        $column = new Tree($registry);
        $column->setName('tree');
        $column->initOptions();

        $extension = new DefaultColumnOptionsExtension();
        $extension->initOptions($column);

        $column->setDataMapper($dataMapper);
        $column->setOption('field_mapping', ['foo']);
        $column->setDataGrid($dataGrid);
        $object = new EntityTree("foo");

        $column->getValue($object);

        $view = $column->createCellView($object, '0');
        $column->buildCellView($view);

        $this->assertSame(
            [
                "row" => "0",
                "id" => "foo",
                "root" => "root",
                "left" => "left",
                "right" => "right",
                "level" => "level",
                "children" => 2,
                "parent" => "bar",
            ],
            $view->getAttributes()
        );
    }

    protected function getManagerRegistry()
    {
        $self = $this;

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnCallback(function() use ($self) {
                $manager = $self->createMock(ObjectManager::class);
                $manager->expects($self->any())
                    ->method('getMetadataFactory')
                    ->will($self->returnCallback(function() use ($self) {
                        $metadataFactory = $self->createMock(ClassMetadataFactory::class);

                        $metadataFactory->expects($self->any())
                            ->method('getMetadataFor')
                            ->will($self->returnCallback(function($class) use ($self) {
                                switch ($class) {
                                    case EntityTree::class :
                                        $metadata = $self->createMock(ClassMetadataInfo::class, [], [$class]);
                                        $metadata->expects($self->any())
                                            ->method('getIdentifierFieldNames')
                                            ->will($self->returnValue([
                                                'id'
                                            ]));
                                        break;
                                }

                                return $metadata;
                            }));

                        $metadataFactory->expects($self->any())
                            ->method('getClassMetadata')
                            ->will($self->returnCallback(function($class) use ($metadataFactory) {
                                return $metadataFactory->getMetadataFor($class);
                            }));

                        return $metadataFactory;
                    }));

                $manager->expects($self->any())
                    ->method('getClassMetadata')
                    ->will($self->returnCallback(function($class) use ($self) {
                        switch ($class) {
                            case EntityTree::class :
                                $metadata = $self->createMock(ClassMetadataInfo::class, [], [$class]);
                                $metadata->expects($self->any())
                                    ->method('getIdentifierFieldNames')
                                    ->will($self->returnValue([
                                        'id'
                                    ]));
                                $metadata->isMappedSuperclass = false;
                                $metadata->rootEntityName = $class;
                                break;
                        }

                        return $metadata;
                    }));

                return $manager;
            }));

        $treeListener = $this->createMock(TreeListener::class);
        $strategy = $this->createMock(Strategy::class);

        $treeListener->expects($this->once())
            ->method('getStrategy')
            ->will($this->returnValue($strategy));

        $treeListener->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue(
                [
                    'left' => 'left',
                    'right' => 'right',
                    'root' => 'root',
                    'level' => 'level',
                    'parent' => 'parent'
                ]
            ));

        $strategy->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('nested'));

        $evm = new EventManagerMock([$treeListener]);
        $em = new EntityManagerMock();
        $em->_setEventManager($evm);

        $managerRegistry->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($em));

        return $managerRegistry;
    }
}
