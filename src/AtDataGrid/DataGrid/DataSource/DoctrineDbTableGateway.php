<?php

namespace AtDataGrid\DataGrid\DataSource;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use AtDataGrid\DataGrid\Column;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Gedmo\Translatable\TranslatableListener;
use Nette\Diagnostics\Debugger;
use AtDataGrid\DataGrid\Column\DbReference;

class DoctrineDbTableGateway extends AbstractDataSource
{
    /**
     * @var Adapter
     */
    protected $dbAdapter;

    /**
     * @var TableGateway
     */
    protected $tableGateway;

    /**
     * @var \Zend\Db\Sql\Select
     */
    protected $select;

    /**
     * Base table columns
     *
     * @var array
     */
    protected $tableColumns = array();

    /**
     * Joined tables
     *
     * @var array
     */
    protected $joinedTables = array();

    /**
     * Joined table columns
     *
     * @var array
     */
    protected $joinedColumns = array();
    
    /**
     * 
     * @var QueryBuilder
     */
    protected $qb;
    
    /**
     * 
     * @var bool
     */
    protected $isTranslationTable;
    
    /**
     * 
     * @var array
     */
    protected $translatableColumns;
    
    protected $parentDataSource;

    /**
     * @param $options
     */
	public function __construct($options)
	{
		$insertJoinedColums = true;
		if(isset($options['insertJoinedColums'])) {
			$insertJoinedColums = $options['insertJoinedColums'];
			unset($options['insertJoinedColums']);
		}
			
		parent::__construct($options);
		
		// detectamos si la tabla contiene o no traducciones y lo anotamos
		$translatableListener = new TranslatableListener();
		$config = $translatableListener->getConfiguration($this->getEm(), $this->getEntity());
		$this->setIsTranslationTable((bool) isset($config['fields']));
		
		if($this->isTranslationTable())
		    $this->setColumnsTranslatable($config['fields']);

		$this->setQueryBuilder();
		
        $this->columns = $this->loadColumns($insertJoinedColums);
	}
	
	public function setParentDataSource($parentDataSource)
	{
		$this->parentDataSource = $parentDataSource;
		return $this;
	}
	
	public function getParentDataSource()
	{
		return $this->parentDataSource;
	}

    /**
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @return ZendDbTableGateway
     */
    public function setEm(EntityManager $em)
    {
        $this->em = $em;
        return $this;
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }
    
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
    
    public function getEntity()
    {
        return $this->entity;
    }
    
    /**
     * 
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }
    
    /**
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function setQueryBuilder()
    {
        $this->qb = $this->getEm()->createQueryBuilder();
        $this->qb->from($this->getEntity(), $this->getEntity());
        return $this;
    
    }

    /**
     * @param \Zend\Db\TableGateway\TableGateway $table
     * @return ZendDbTableGateway
     */
    public function setTableGateway(TableGateway $table)
    {
        $this->tableGateway = $table;
        return $this;
    }

    /**
     * @return \Zend\Db\TableGateway\TableGateway
     */
    public function getTableGateway()
    {
        return $this->tableGateway;
    }

    /**
     * 
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getSelect()
    {
        return $this->getQueryBuilder();
    }

    /**
     * @return mixed|\Zend\Paginator\Paginator
     */
    public function getPaginator()
    {
        if (!$this->paginator) {
            $dp = new Paginator($this->getQueryBuilder());
            $dp->setUseOutputWalkers(false);
            $this->paginator = new \Zend\Paginator\Paginator(
                new DoctrinePaginator($dp)
            );
        }

        return $this->paginator;
    }

    /**
     * Join other table and collect joined columns
     *
     * @param $tableClassName
     * @param $alias
     * @param $keyName
     * @param $foreignKeyName
     * @param null $columns
     * @throws \Exception
     */
    public function with($joinedTableName, $alias, $keyName, $foreignKeyName, $columns = null)
    {
        $tableMetadata = new \Zend\Db\Metadata\Metadata($this->getDbAdapter());
        $joinedTableColumns = $tableMetadata->getColumns($joinedTableName);

        $joinedColumns = array();
        
        foreach ($joinedTableColumns as $columnObject) {
            $columnName = $columnObject->getName();

            if (null != $columns) {
                if (in_array($columnName, $columns)) {
                   $joinedColumns[$alias . '__' . $columnName] = $columnName;
                   $this->joinedColumns[] = $alias . '__' . $columnName;
                }
            } else {
                $joinedColumns[$alias . '__' . $columnName] = $columnName;
                $this->joinedColumns[] = $alias . '__' . $columnName;
            }
        }

        $this->getSelect()->join(
            array($alias => $joinedTableName),
            $this->getTableGateway()
                ->getTable() . '.' . $keyName . ' = ' . $alias . '.' .
                 $foreignKeyName, $joinedColumns);
    }

    /**
     *
     * @return array mixed
     */
    public function loadColumns($insertJoinedColums = true, $parentCollum = null)
    {
        $mapping = $this->getEm()->getClassMetadata($this->getEntity());
        
        $this->setIdentifierFieldName(current($mapping->identifier));
        
        foreach ($mapping->fieldMappings as $map) {
            
            $columnName = $map['fieldName'];
            $columnDataType = $map['type'];
            
            $this->tableColumns[] = $columnName;
            
            switch (true) {
                case in_array($columnDataType, array('datetime', 'timestamp', 'time')):
                    $column = new Column\DateTime($columnName, $this);
                    break;
            
                case in_array($columnDataType, array('date', 'year')):
                    $column = new Column\Date($columnName, $this);
                    break;
            
                case in_array($columnDataType, array('mediumtext', 'text')):
                    $column = new Column\Textarea($columnName, $this);
                    break;
            
                default:
                    $column = new Column\Literal($columnName, $this);
                    break;
            }
            
            $column->setLabel($columnName);
            if($parentCollum !== null)
           		$column->setParent($parentCollum);
            
            $columns[$columnName] = $column;
            
        }
        
        if($insertJoinedColums) {
	        foreach($mapping->associationMappings as $map) {
	        	
	        	$columnName = $map['fieldName'];
	        	$columnDataType = $map['type'];
	        	
	        	$dataSource = new self(array(
	        		'entity' => $map['targetEntity'],
	        		'em' => $this->getEm(),
	        		'insertJoinedColums' => false,
	        		'parentDataSource' => $this
	        	));
	        	
	        	$column = new Column\DbReference($columnName, $dataSource);
	        	$column->setLabel($columnName);
	        	
	        	$columns[$columnName] = $column;
	        	
	        	//$columnsJoined = $dataSource->getColumns();
	        	
	            $columnName = $map['fieldName'];
	            $columnDataType = $map['type'];
	            
	            /*foreach($columnsJoined as $_column) {
	            	$_column->setLabel($columnName);
	            	$column->addColumn($_column);
	            }*/
	            
	        }
	        
        }
        
        
        
        // Setup default settings for joined table column fields
        /*foreach ($this->joinedColumns as $columnName) {
            $column = new Column\Literal($columnName);
            $column->setLabel($columnName);
        
            $columns[$columnName] = $column;
        }*/
        
        //$this->setCommentAsLabel($columns);
        
        return $columns;
    }

    /**
     * @param $columns
     * @return void
     */
    protected function setCommentAsLabel($columns)
    {
        // Get current database name
        $query = 'SELECT DATABASE();';
        $schema = $this->getDbAdapter()->query($query);

        // Set table field comments as column label.
        $select = new Select('information_schema.COLUMNS');
        $select->columns(array('name' => 'COLUMN_NAME', 'comment' => 'COLUMN_COMMENT'))
               ->where(array('TABLE_SCHEMA' => $schema))
               ->where(array('TABLE_NAME', $this->getTableGateway()->getTable()));
        
        $columnsInfo = $this->getDbAdapter()->query($select->getSqlString(), \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);

        if ($columnsInfo) {
            foreach ($columnsInfo as $column) {
                if (!empty($column['comment'])) {
                    $columns[$column['name']]->setLabel($column['comment']);
                }
            }
        }
    }

    /**
     * Return row by identifier (primary key)
     *
     * @param $key
     * @return array|mixed
     */
    public function find($key)
    {
        $repo = $this->getEm()->getRepository($this->getEntity());
        $qb = $repo->createQueryBuilder('table');
        
        $qb->where($qb->expr()->eq('table.id', ':id'));
        $qb->setParameter('id', $key);
        $entity = $qb->getQuery()
            ->setHint(TranslatableListener::HINT_INNER_JOIN, true)->getOneOrNullResult();
        $qb->getQuery()->setHint(
            TranslatableListener::HINT_FALLBACK,
            1 // fallback to default values in case if record is not translated
        );
//         $entity = $this->getEm()->find($this->getEntity(), $key);
        return $entity;
    }

    /**
     * @param $listType
     * @param $order
     * @param $currentPage
     * @param $itemsPerPage
     * @param $pageRange
     * @return mixed|\Zend\Db\ResultSet\ResultSet
     */
    public function fetch($listType, $order, $currentPage, $itemsPerPage, $pageRange)
    {
    	if ($listType == AbstractDataSource::LIST_TYPE_PLAIN) {
			if ($order) {
				
				$columnOrder = $order['column'];
				
				$handlerOrderCondition = $columnOrder->getHandlerOrderCondition();
				
				if($handlerOrderCondition) {
				    $handlerOrderCondition($this->getSelect(), $order['direction']);
				} else {
				    if ($columnOrder instanceof DbReference) {
				        $columnOrderEntity = $columnOrder->getdataSource()->getEntity();
				        $this->getSelect()
				        ->leftJoin($this->getEntity() . '.' . $columnOrder->getName(),
				            $columnOrderEntity)
				            ->addSelect('COUNT(' . $columnOrderEntity . '.id) AS '.$columnOrderEntity.'_cnt')
				            ->groupBy($this->getEntity() . '.id')
				            ->orderBy($columnOrderEntity . '_' . 'cnt', $order['direction']);
				    } else {
				        if($order['column']->getParent()) {
				            $columnOrderEntity = $order['column']->getdataSource()->getEntity();
				            $this->getSelect()
				            ->leftJoin($this->getEntity() . '.' . $order['column']->getParent()->getName(),
				                $columnOrderEntity)
				                ->groupBy($this->getEntity() . '.id')
				                ->addOrderBy($columnOrderEntity . '.' . $order['column']->getName(), $order['direction']);
				        } else {
				            $this->getSelect()->addOrderBy(
				                $this->getEntity() . '.' . $columnOrder->getName(), $order['direction']);
				        }
				        	
				    }
				}
				
			}
			
			$this->getQueryBuilder()->addSelect($this->getEntity())->getQuery()->setHint(TranslatableListener::HINT_INNER_JOIN, true);
			
			$paginator = $this->getPaginator();
			$paginator->setCurrentPageNumber($currentPage)
				->setItemCountPerPage($itemsPerPage)
				->setPageRange($pageRange);
			
			return $paginator->getItemsByPage($currentPage);
		} elseif ($listType == AbstractDataSource::LIST_TYPE_TREE) {
			$items = $this->getTableGateway()->select();
			return $items;
		}
    }
    
    protected function _createOrderQueryRecursive(\AtDataGrid\DataGrid\Column\Column $column, $direction)
    {
    	if($column->getParent()) {
    		Debugger::dump($column);Exit;
    		$columnOrderEntity = $column->getdataSource()->getEntity();
    		$this->getSelect()
						->leftJoin($this->getEntity() . '.' . $column->getName(), 
							$columnOrderEntity)
						->addSelect('COUNT(' . $columnOrderEntity . '.id) AS '.$columnOrderEntity.'_cnt')
						->groupBy($this->getEntity() . '.id')
						->orderBy($columnOrderEntity . '_' . 'cnt', $order['direction']);
    	} else {
    		$this->getSelect()->orderBy(
    				$this->getEntity() . '.' . $columnOrder->getName(), $direction);
    	}
    	
    }

    /**
     * Get only fields which present in table
     *
     * @param array $data
     * @return array
     */
    protected function cleanDataForSql($data = array())
    {
        $cleanData = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $this->tableColumns)) {
                $cleanData[$key] = $value;
            }
        }

        return $cleanData;
    }

    /**
     * @param $data
     * @return int|mixed
     */
    public function insert($data)
    {
    	$em = $this->getEm();
    	$entityName = $this->getEntity();
    	if(is_object($data) && $entityName == get_class($data)) {
    	    $entity = $data;
    	} else {
    	    $entity = new $entityName();
    	    $entity->populateData($data);
    	}
    	
        $em->persist($entity);
        $em->flush();
        
        return $entity;
    }

    /**
     * @param $data
     * @param $key
     * @return int|mixed
     */
    public function update($data, $key)
    {
        $em = $this->getEm();
        
        $entityName = $this->getEntity();
        if(is_object($data) && $entityName == get_class($data)) {
            $entity = $data;
        } else {
            $entity = $this->find($key);
            $entity->populateData($data);
        }
       
        $em->persist($entity);
        $em->flush();
        return $entity;
    }

    /**
     * @param $key
     * @return int|mixed
     */
    public function delete($key)
    {
        $em = $this->getEm();
        $entity = $this->find($key);
        $em->remove($entity);
        $em->flush();
        return $this;
    }    
    
    /**
     * 
     * @param bool $isTranslationTable
     * @return \ZfJoacubCrud\DataGrid\DataSource\DoctrineDbTableGateway
     */
    protected function setIsTranslationTable($isTranslationTable)
    {
        $this->isTranslationTable = $isTranslationTable;
        return $this;
    }
    
    /**
     * nos dice si la tabla(s) contiene alguna traduccion
     * @return boolean
     */
    public function isTranslationTable()
    {
        return $this->isTranslationTable;
    }
    
    public function setColumnsTranslatable($columns)
    {
        $this->translatableColumns = $columns;
        return $this;
    }
    
    public function getColumnsTranslatable()
    {
        return $this->translatableColumns;
    }
    
    
}