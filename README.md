# FSi DataGrid Component #

List component that displays the items from collection (data source) by using
special column objects.
Displaying objects list is one of the most common tasks in web applications and 
probably the easiest one, so you could ask how this component can help you?

FSi DataGrid Component allows you to create one action that handle
disply all kind of lists in your application without duplicating code. 
It can be very useful in all kind of admin panel generators.

## Basic Usage ##

The sample code shows more than a thousand words so lets start!

Before you can display data you need to add columns into DataGrid component. 
Columns pull data from objects using ``DataMappers`` (They will be described later). 

Imagine a situation where we need to display a list of news. List must contains 
such information as:

- News ID
- News Title
- Author Name, Surname and email
- Publication Date
- Some basic actions like edit and delete

So we should get a table similar to the following:
 
<table>
    <tr>
        <td>Id</td>
        <td>News Title</td>
        <td>Author</td>
        <td>Publication Date</td>
        <td>Actions</td>
    </tr>
    <tr>
        <td>1</td>
        <td>First News</td>
        <td>
            Norbert<br/>
            Orzechowicz<br/>
            norbert@fsi.pl<br/>
        </td>
        <td>2012.05.01 15:13:52</td>
        <td>
            <i>Edit</i><br>
            <i>Delete</i>
        </td>
    </tr>
    <tr>
        <td>2</td>
        <td>Second News</td>
        <td>
            Norbert<br/>
            Orzechowicz<br/>
            norbert@fsi.pl<br/>
        </td>
        <td>2012.05.06 15:13:52</td>
        <td>
            <i>Edit</i><br>
            <i>Delete</i>
        </td>
    </tr>
</table>

And here is our News object.  

``` php
<?php

namespace FSi\SiteBundle\Entity\News;

class News
{
    /**
     * @var integer
     */
    protected $id;
    
    /**
     * @var string
     */
    protected $title;
    
    /**
     * @var string
     */
    protected $author_name;
    
    /**
     * @var string
     */
    protected $author_surname;
    
    /**
     * @var string
     */
    protected $author_email;
    
    /**
     * @var DateTime
     */
    protected $publication_date;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function getAuthorName()
    {
        return $this->author_name;
    }
    
    public function getAuthorSurname()
    {
        return $this->author_surname;
    }
            
    public function getAuthorEmail()
    {
        return $this->author_email;
    }
    
    public function getPublicationDate()
    {
        return $this->publication_date;
    }
    
    /*        
    Here we should have setters 
    public function setTitle($title)
    public function setAuthorName($name)
    ...
    */
}
```
    
Suppose that we use Doctrine ORM and we can get a list of objects as follows:

``` php
$data = $this->getEntityManager()->getRepository('FSi\SiteBundle\Entity\News')->findAll();
```

So if we have data we need to build grid and pass data into it. 

We assume that datagrid.factory is a service with DataGridFactory object instance.
More about creating DataGridFactory and loading columns into it will be described later.

``` php
$grid = $this->get('datagrid.factory')->createDataGrid();
```
    
DataGrid::addColumn($name, $type = 'text', $otpions = array());

``` php
$grid->addColumn('id', 'number', array('label' => 'Id'))
     ->addColumn('title', 'text', array('label' => 'News Title'))
     ->addColumn('author', 'text', array(
            'mapping_fields' => array(
                'author_name',
                'author_surname',
                'author_email'
            ),
            'glue' => '<br/>',
            'label' => 'Author'
        )
    )
    ->addColumn('publication', 'datetime', array(
            'mapping_fields' => array('publication_date'),
            'format' => 'Y.m.d H:i:s',
            'label' => 'Author'  
        )
    )
    ->addColumn('action', 'action', array(
            'label' => 'Actions',
            'mapping_fields' => array('id'),
            'actions' => array(
                'edit' => array(
                    'anchor' => 'Edit',
                    'route_name' => '_edit_news',
                    'parameters' => array('id' => 'id'),
                ),
                'delete' => array(
                    'anchor' => 'Delete',
                    'route_name' => '_delete_news',
                    'parameters' => array('id' => 'id'),
                )
            )
        )
    );
```

Ok, so now when we have grid its time to create view. This can be done by calling ``DataGrid::CreateView()``

``` php
$view = $gird->createView();
```
   
$view is ``DataGridView`` object that implements ``\SeekableIterator``, ``\Countable`` 
and ``\ArrayAccess`` iterfaces so have easy access to each row in view.<br>
Every single row in view must be ``DataGridRowView`` object that also implements 
``\SeekableIterator``, ``\Countable`` and ``\ArrayAccess`` iterfaces so you have 
access to each column in row.<br>
And the last view part, column is an object with methods like ``getAttribute``, 
``getValue`` and few other that will help you to build pefrect view. 

Here is a simple view implementation:

    <table>
        <tr>
        <?php foreach ($view->getColumns() as $column):?>
            <td><?php echo $column->getOption('label'); ?></td>
        <?php endforeach; ?>
        </tr>
        <tr>
        <?php foreach ($view as $row) :?>
        <tr>
            <?php foreach ($row as $column):?>
            <td>
                <?php if ($column->getType() == 'action'): ?>
                    <?php foreach ($column->getValue() as $link): ?>
                        <a href="<?php echo $link['url']; ?>"><?php echo $link['anchor']; ?></a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php echo $column->getValue(); ?>
                <?php endif;?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
    </table>
    
## Installation ##

This section will describe how to add datagrid into your project. 
First thing is to get DataGrid sources, we recomend you to use [composer](https://github.com/composer/composer#composer---package-management-for-php)
After that you need to do a couple of things to create ``DataGridFactory`` that 
finally alows you to create ``DataGrid`` objects.

Here are sample scenarios of component usage:
    
- [standalone](https://github.com/norzechowicz/datagrid-standalone)
- [symfony](https://github.com/fsi-open/datagrid/blob/master/doc/en/installation/symfony.md)
    
    
## Built-in Column Types ##

- [text](https://github.com/fsi-open/datagrid/blob/master/doc/en/columns/text.md)
- [number] (https://github.com/fsi-open/datagrid/blob/master/doc/en/columns/number.md)
- [money] (https://github.com/fsi-open/datagrid/blob/master/doc/en/columns/money.md)
- [datetime] (https://github.com/fsi-open/datagrid/blob/master/doc/en/columns/datetime.md)
- [action] (https://github.com/fsi-open/datagrid/blob/master/doc/en/columns/action.md)
- [entity] (https://github.com/fsi-open/datagrid/blob/master/doc/en/columns/entity.md)
- [tree] (https://github.com/fsi-open/datagrid/blob/master/doc/en/columns/tree.md)