<?php
namespace AtDataGrid\DataGrid;

use AtDataGrid\DataGrid\DataSource;
use AtDataGrid\DataGrid\Column\Column;
use Nette\Diagnostics\Debugger;
use Zend\EventManager\EventManager;
use Zend\Json\Json;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceManager;

/**
 * Class DataGrid
 * 
 * @package AtDataGrid\DataGrid
 */
class DataGrid implements \Countable, \IteratorAggregate, \ArrayAccess
{

    /**
     * Grid caption
     *
     * @var string
     */
    protected $caption = '';

    /**
     * Grid caption
     *
     * @var string
     */
    protected $captionBackTo = 'Volver al listado';

    /**
     * Data grid columns
     *
     * @var array
     */
    protected $columns = array();

    /**
     *
     * @var array
     */
    protected $columnsOrderer = array();

    /**
     *
     * @var string
     */
    protected $identifierColumnName = 'id';

    /**
     *
     * @var null
     */
    protected $currentOrderColumn = null;

    /**
     *
     * @var string
     */
    protected $currentOrderDirection = 'asc';

    /**
     * Current page
     *
     * @var integer
     */
    protected $currentPage = 1;

    /**
     * Items per page
     *
     * @var integer
     */
    protected $itemsPerPage = 20;

    /**
     * Page range
     *
     * @var integer
     */
    protected $pageRange = 10;

    /**
     * Array of rows (items)
     *
     * @var array
     */
    protected $data = array();

    /**
     * Data source
     *
     * @var
     *
     */
    protected $dataSource;

    /**
     * Data panels
     *
     * @var array
     */
    protected $dataPanels = array();

    /**
     *
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     *
     * @param
     *            $dataSource
     * @param array $options            
     */
    public function __construct($dataSource, $options = array())
    {
        $this->setDataSource($dataSource);
        
        if ($options instanceof \Zend\Config\Config) {
            $options = $options->toArray();
        }
        
        $this->setOptions($options);
        
        /**
         * @todo use event instead
         */
        $this->init();
    }

    /**
     * Initialize data grid (used by extending classes)
     *
     *
     * @return void
     */
    public function init()
    {}
    
    // OPTIONS
    
    /**
     * Set data grid options
     *
     * @param array $options            
     * @return DataGrid
     */
    public function setOptions(array $options)
    {
        unset($options['options']);
        unset($options['config']);
        
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        
        return $this;
    }
    
    // METADATA
    
    /**
     *
     * @param
     *            $caption
     * @return DataGrid
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     *
     * @param
     *            $caption
     * @return DataGrid
     */
    public function setCaptionBackTo($caption)
    {
        $this->captionBackTo = $caption;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getCaptionBackTo()
    {
        return $this->captionBackTo;
    }
    
    // COLUMNS
    
    /**
     *
     * @param
     *            $name
     * @return DataGrid
     */
    public function setIdentifierColumnName($name)
    {
        $this->identifierColumnName = (string) $name;
        $this->getDataSource()->setIdentifierFieldName($this->identifierColumnName);
        
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getIdentifierColumnName()
    {
        return $this->identifierColumnName;
    }

    /**
     * Check if is column present in column list
     *
     * @param
     *            $name
     * @return bool
     */
    protected function isColumn($name)
    {
        return array_key_exists($name, $this->columns);
    }

    /**
     * Add a column to data grid
     *
     * @param Column $column            
     * @param bool $overwrite            
     * @return DataGrid
     * @throws \Exception
     */
    public function addColumn(Column $column, $overwrite = false, $forceCreate = false)
    {
        if ((false == $overwrite) && (false === $forceCreate && $this->isColumn($this->getColumnId($column)))) {
            throw new \Exception('Column `' . $column->getName() . '` already in a column list. Use other name.');
        }
        
        $this->columns[$column->getName()] = $column;
        
        // If label is not set, set column name as label
        if (null == $column->getLabel()) {
            $column->setLabel($column->getName());
        }
        
        return $this;
    }

    /**
     * Set column by given name with overwriting.
     * Alias for addColumn($column, true)
     *
     * @param Column $column            
     * @return DataGrid
     */
    public function setColumn(Column $column)
    {
        $this->addColumn($column, true);
        return $this;
    }

    /**
     * Add columns to grid
     *
     * @param array $columns            
     * @param bool $overwrite            
     * @return DataGrid
     */
    public function addColumns(array $columns, $overwrite = false)
    {
        foreach ($columns as $column) {
            $this->addColumn($column, $overwrite);
        }
        
        return $this;
    }

    /**
     * Return column object specified by it name
     *
     * @param
     *            $name
     * @throws \Exception
     * @return Column
     */
    public function getColumn($name)
    {
        if ($this->isColumn($name)) {
            return $this->columns[$name];
        }
        
        $columnName = array();
        foreach ($this->columns as $columnName => $column)
            $columns[] = $columnName;
        
        throw new \Exception("Column '" . $name . "' doesn't exist in column list => " . implode(', ', $columns));
    }

    /**
     * Return all column objects
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Remove column specified by it name
     *
     * @param
     *            $name
     * @return DataGrid
     */
    public function removeColumn($name)
    {
        if ($this->isColumn($name)) {
            unset($this->columns[$name]);
        }
        
        return $this;
    }

    /**
     * Remove columns specified by its names
     *
     * @param array $names            
     * @return DataGrid
     */
    public function removeColumns(array $names)
    {
        foreach ($names as $name) {
            $this->removeColumn($name);
        }
        
        return $this;
    }

    /**
     * Set column invisible in grid
     *
     * @param
     *            $name
     * @return DataGrid
     */
    public function hideColumn($name)
    {
        $this->getColumn($name)->setVisible(false);
        
        return $this;
    }

    /**
     * Set columns invisible in grid
     *
     * @param array $names            
     * @return DataGrid
     */
    public function hideColumns(array $names)
    {
        foreach ($names as $name) {
            $this->hideColumn($name);
        }
        
        return $this;
    }

    /**
     * Set column invisible in add/edit form
     *
     * @param
     *            $name
     * @return DataGrid
     */
    public function hideColumnInForm($name)
    {
        $this->getColumn($name)->setVisibleInForm(false);
        
        return $this;
    }

    /**
     * Set columns invisible in form
     *
     * @param
     *            $names
     * @return DataGrid
     */
    public function hideColumnsInForm($names)
    {
        foreach ($names as $name) {
            $this->hideColumnInForm($name);
        }
        
        return $this;
    }
    
    // SORTING
    
    /**
     *
     * @param string $order
     *            columnName~orderDirection
     */
    public function setOrder($order)
    {
        $order = explode('~', $order);
        
        if (isset($order[1])) {
            list ($columnName, $orderDirection) = $order;
            $this->setCurrentOrderColumn($columnName, $orderDirection);
        }
    }

    /**
     *
     * @param
     *            $columnName
     * @param
     *            $orderDirection
     */
    public function setCurrentOrderColumn($columnName, $orderDirection = 'asc')
    {
        try {
            $columnParse = Json::decode($columnName);
            
            $column = $this->getColumn(array_shift($columnParse));
            
            foreach ($columnParse as $columnName) {
                $column = $column->getColumn($columnName);
            }
            
            $this->currentOrderColumn = $column;
            $this->currentOrderDirection = $orderDirection;
            
            $column->setOrderDirection($orderDirection);
            $column->revertOrderDirection();
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     *
     * @return null
     */
    public function getCurrentOrderColumn()
    {
        return $this->currentOrderColumn;
    }

    /**
     *
     * @return string
     */
    public function getCurrentOrderDirection()
    {
        return $this->currentOrderDirection;
    }
    
    // DATA SOURCE
    
    /**
     * Set data source and load columns defined in it
     *
     * @param DataSource\AbstractDataSource $dataSource            
     * @return DataGrid
     */
    public function setDataSource(DataSource\AbstractDataSource $dataSource)
    {
        $this->dataSource = $dataSource;
        $this->columns = $this->getDataSource()->getColumns();
        
        return $this;
    }

    /**
     * Get data source object
     *
     * @return mixed
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * Find row by primary key
     *
     * @param
     *            $key
     * @return mixed
     */
    public function getRow($key)
    {
        return $this->getDataSource()->find($key);
    }

    /**
     * Returns rows demands on list type.
     * It may be list or tree
     *
     * @param string $listType            
     * @return mixed
     */
    public function getData($listType = DataSource\AbstractDataSource::LIST_TYPE_PLAIN)
    {
        $order = null;
        
        if ($this->getCurrentOrderColumn()) {
            $order['column'] = $this->getCurrentOrderColumn();
            $order['direction'] = $this->getCurrentOrderDirection();
        }
        
        $this->data = $this->getDataSource()->fetch($listType, $order, $this->currentPage, $this->itemsPerPage, $this->pageRange);
        
        return $this->data;
    }
    
    // CRUD
    
    /**
     * Insert new row to grid
     */
    public function insert($data)
    {
        return $this->getDataSource()->insert($data);
    }

    /**
     * Update row in a grid
     *
     * @param
     *            $data
     * @param
     *            $primary
     */
    public function update($data, $primary)
    {
        return $this->getDataSource()->update($data, $primary);
    }

    /**
     *
     * @param
     *            $data
     * @param null $identifier            
     */
    public function save(EventManager $eventManager, $data, $identifier = null)
    {
        if ($identifier) {
            $id = $this->update($data, $identifier);
        } else {
            $id = $this->insert($data);
        }
        
        $params = compact('data', 'id');
        $eventManager->trigger(__FUNCTION__, $this, $params);
        
        return $id;
    }

    /**
     *
     * @param
     *            $identifier
     */
    public function delete($identifier)
    {
        $this->getDataSource()->delete($identifier);
    }
    
    // FILTERS
    
    /**
     * Add filter to column
     *
     * @param
     *            $column
     * @param
     *            $filter
     * @return DataGrid
     */
    public function addFilter($column, $filter)
    {
        $this->getColumn($column)->addFilter($filter);
        return $this;
    }

    /**
     * Apply filters.
     * Modify select object.
     *
     * @param
     *            $values
     */
    public function applyFilters($values, Column $column = null)
    {
        if ($column === null) {
            $columns = $this->getColumns();
        } else {
            $columns = $column->getColumns();
            
            $values = @$values[$column->getName()];
        }
        
        /**
         * @var \Zend\Db\Sql\Select $select
         */
        $select = $this->getDataSource()->getSelect();
        
        foreach ($columns as $_column) {
            $filters = $_column->getFilters();
            $subColumns = $_column->getColumns();
            if (count($subColumns) > 0)
                $this->applyFilters($values, $_column);
            
            foreach ($filters as $filter) {
                $filter->apply($select, $_column, @$values[$filter->getName()]);
            }
        }
        
        // var_dump($select->getSqlString());exit;
        
        // exit;
    }

    /**
     *
     * @param array $options            
     * @return \Zend\Form\Form
     */
    public function getFiltersForm($options = array(), Column $column = null, $fieldset = false )
    {
        $name = 'filters-form';
        if ($column !== null)
            $name = $column->getName();
        
        if($fieldset) {
            $form = new \Zend\Form\Fieldset($name, $options);
        } else {
            $form = new \Zend\Form\Form($name, $options);
        }
        
        $form->setUseAsBaseFieldset(true);
        
        $insertApplyElement = false;
        if ($column === null) {
            $columns = true;
            $columns = $this->getColumns();
        } else {
            $columns = $column->getColumns();
        }
        
        foreach ($columns as $column) {
            
//             if (! $column->isVisible())
//                 continue;
            
            $subColumns = $column->getColumns();
            
            if (count($subColumns) > 0) {
                $form->add($this->getFiltersForm($options, $column, $form));
                
                if ($column->hasFilters()) {
                    $filters = $column->getFilters();
                    foreach ($filters as $filter) {
                        $form->add($column->getFilterFormElement($filter));
                    }
                }
            } else {
                
                if ($column->hasFilters()) {
                    $filters = $column->getFilters();
                    foreach ($filters as $filter) {
                        $form->add($column->getFilterFormElement($filter));
                    }
                }
            }
        }
        
        if ($insertApplyElement) {
            // Apply button
            $apply = new \Zend\Form\Element\Submit('apply');
            $apply->setLabel('Поиск');
            $form->add($apply);
        }
        
        return $form;
    }

    /**
     *
     * @param
     *            $number
     * @return DataGrid
     */
    public function setCurrentPage($number)
    {
        if (! is_null($number)) {
            $this->currentPage = (int) $number;
        }
        
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     *
     * @param integer $count            
     */
    public function setItemsPerPage($count)
    {
        if (! is_null($count)) {
            $this->itemsPerPage = (int) $count;
        }
        
        return $this;
    }

    /**
     *
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     *
     * @param
     *            $count
     * @return DataGrid
     */
    public function setPageRange($count)
    {
        if (! is_null($count)) {
            $this->pageRange = (int) $count;
        }
        
        return $this;
    }
    
    // DATA PANELS
    
    /**
     *
     * @param
     *            $key
     * @param
     *            $name
     * @param bool $isAjax            
     * @return DataGrid
     */
    public function addDataPanel($key, $name, $isAjax = true)
    {
        $this->dataPanels[$key] = array(
            'name' => $name,
            'is_ajax' => $isAjax
        );
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getDataPanels()
    {
        return $this->dataPanels;
    }
    
    // Interfaces implementation
    
    /**
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->columns);
    }

    /**
     *
     * @return bool
     */
    public function valid()
    {
        return ($this->current() !== false);
    }

    /**
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->columns);
    }

    /**
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->columns);
    }

    /**
     *
     * @return mixed
     */
    public function rewind()
    {
        return reset($this->columns);
    }

    /**
     *
     * @return int
     */
    public function count()
    {
        return count($this->columns);
    }

    /**
     *
     * @param mixed $offset            
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->columns[$offset]);
    }

    /**
     *
     * @param mixed $offset            
     * @return bool mixed
     */
    public function offsetGet($offset)
    {
        if (isset($this->columns[$offset])) {
            return $this->columns[$offset];
        }
        
        return false;
    }

    /**
     *
     * @param mixed $offset            
     * @param mixed $column            
     */
    public function offsetSet($offset, $column)
    {
        if ($offset !== null) {
            $this->columns[$offset] = $column;
        } else {
            $this->columns[] = $column;
        }
    }

    /**
     *
     * @param mixed $offset            
     */
    public function offsetUnset($offset)
    {
        if (isset($this->columns[$offset])) {
            unset($this->columns[$offset]);
        }
    }

    /**
     * Get an iterator for iterating over the elements in the collection.
     *
     * @return \ArrayIterator \Traversable
     */
    public function getIterator()
    {
        $em = $this->getDataSource()->getEm();
        
        $em instanceof EntityManager;
        $repo = $em->getRepository('AtAdmin\Entity\ColumnState');
        $auth = $this->getServiceManager()->get('zfcuser_auth_service');
        $user = $auth->getIdentity();
        $tmpColumns = array();
        
        foreach ($this->columns as $k => $v) {
            if ($v->isVisible()) {
                $tmpColumns[$this->getColumnId($v)] = $v;
                $entity = $repo->findOneBy(array(
                    'column' => $this->getColumnId($v),
                    'user' => $user
                ));
                
                if ($entity) {
                    $v instanceof Column;
                    $v->setIsHidden(($entity->getStatus() === false));
                    $this->setOrderColumn($entity->getPosition(), $v);
                }
            }
        }
        
        $tmpOrderer = $this->columnsOrderer;
        
        foreach ($tmpOrderer as $column) {
            $entity = $repo->findOneBy(array(
                'column' => $this->getColumnId($column),
                'user' => $user
            ));
            
            if ($entity) {
                $column->setIsHidden(($entity->getStatus() === false));
                $this->setOrderColumn($entity->getPosition(), $column);
            }
        }
        
        $tmpOrderer = $this->columnsOrderer;
        
        foreach ($tmpOrderer as $column) {
            unset($tmpColumns[$this->getColumnId($column)]);
        }
        
        $newColumns = array();
        
        $i = 0;
        while (1) {
            if (empty($tmpOrderer))
                break;
            current($tmpOrderer);
            $k = key($tmpOrderer);
            if ($k != $i) {
                $i ++;
                if (empty($tmpColumns))
                    continue;
                $column = array_shift($tmpColumns);
                $newColumns[$this->getColumnId($column)] = $column;
                unset($tmpColumns[$this->getColumnId($column)]);
                continue;
            }
            
            $column = $tmpOrderer[$k];
            $newColumns[$this->getColumnId($column)] = $column;
            if (isset($tmpColumns[$this->getColumnId($column)]))
                unset($tmpColumns[$this->getColumnId($column)]);
            
            unset($tmpOrderer[$k]);
            $i ++;
        }
        
        foreach ($tmpColumns as $column) {
            $newColumns[$this->getColumnId($column)] = $column;
        }
       
        return new \ArrayIterator($newColumns);
    }

    public function setTitleColumnName($name)
    {
        $this->titleColumnName = $name;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getTitleColumnName()
    {
        return $this->titleColumnName;
    }

    public function setOrderColumn($orderColumn, $column)
    {
        $this->columnsOrderer[$orderColumn] = $column;
        ksort($this->columnsOrderer);
    }

    public function setServiceManager(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    public function getColumnCompleteName($column, $name = array())
    {
        if ($column->getParent()) {
            $name = $this->getColumnCompleteName($column->getParent(), $name);
        }
        $name[] = $column->getName();
        return $name;
    }

    public function getColumnId($column)
    {
        return md5(Json::encode($this->getColumnCompleteName($column)));
    }
}