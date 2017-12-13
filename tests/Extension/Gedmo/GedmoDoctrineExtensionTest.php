<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FSi\Component\DataGrid\Tests\Extension\Doctrine;

use FSi\Component\DataGrid\Extension\Gedmo\GedmoDoctrineExtension;
use Doctrine\Common\Persistence\ManagerRegistry;

class GedmoDoctrineExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadedTypes()
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $extension = new GedmoDoctrineExtension($registry);

        $this->assertTrue($extension->hasColumnType('gedmo_tree'));
        $this->assertFalse($extension->hasColumnType('foo'));
    }
}
