<?php

namespace AtDataGrid\DataGrid\Filter\Sql;

use AtDataGrid\DataGrid\Filter;
use AtDataGrid\DataGrid\Filter\Parameter\ParameterId;
use Doctrine\ORM\QueryBuilder;
use Nette\Diagnostics\Debugger;
use Doctrine\ORM\Query\Expr\Join;

class Equal extends Filter\AbstractFilter
{
    /**
     * @param $select
     * @param $column
     * @param mixed $value
     * @return mixed|void
     */
    public function apply($dataSource, $column, $value)
    {
        $value = $this->applyValueType($value);

        if (isset($value) && !empty($value)) {
            if($dataSource instanceof QueryBuilder) {
                $qb = $dataSource;
                $parameter = ParameterId::getParameter(__CLASS__, $column->getName());
                
                 if($column->getParent() == null) {
	                $qb->andWhere($qb->expr()->eq($qb->getRootAlias() . '.' . $column->getName(), ':' . $parameter))
	                ->setParameter($parameter, $value);
                } else {
                	
                	$parentColumn = $column->getParent();
                	
                
                	$fieldAssoc = $parentColumn->getName();;
                	$field = $column->getName();
                	

                	$parts[] = $fieldAssoc;
                	$parts[] = $field;
                	 
                	$alias = implode('__', $parts);
                	
                	$qb->join($qb->getRootAlias() . '.' . $fieldAssoc, $alias, Join::WITH, $qb->expr()->eq($alias . '.' . $field, ':' . $parameter));
                	
                	$qb->setParameter($parameter, $value);
                }
            } else {
                //$columnName = $this->_findTableColumnName($select, $column->getName());
                $dataSource->getSelect()->where(array($column->getName() => $value));
            }
        	
        }

        return $dataSource;
    }
}