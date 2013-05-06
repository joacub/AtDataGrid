<?php

namespace AtDataGrid\DataGrid\Filter\Sql;

use AtDataGrid\DataGrid\Filter;
use AtDataGrid\DataGrid\Filter\Parameter\ParameterId;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

class GreaterThan extends Filter\AbstractFilter
{
    /**
     * Returns the result of applying $value
     *
     * @param  mixed $value
     * @return mixed
     */
    public function apply($dataSource, $column, $value)
    {
        $value = $this->applyValueType($value);
        
        if (strlen($value) > 0) {
            if($dataSource instanceof QueryBuilder) {
                $qb = $dataSource;
                $parameter = ParameterId::getParameter(__CLASS__, $column->getName());
                
                $parts = explode('__', $column->getName());
                
                if(count($parts) == 1) {
                	$qb->andWhere($qb->expr()->gt($qb->getRootAlias() . '.' . $column->getName(), ':' . $parameter));
               		 $qb->setParameter($parameter, $value);
                } else {
                	 
                	$alias = implode('__', $parts);
                	 
                	$fieldAssoc = array_shift($parts);
                	$field = array_shift($parts);
                	 
                	$qb->join($qb->getRootAlias() . '.' . $fieldAssoc, $alias, Join::WITH, $qb->expr()->gt($alias . '.' . $field, ':' . $parameter));
                	 
                	$qb->setParameter($parameter, $value);
                }
                
            } else {
                $dataSource->getSelect()->where(
                    new \Zend\Db\Sql\Predicate\Operator($column->getName(), \Zend\Db\Sql\Predicate\Operator::OP_GT, $value)
                );
            }
            
        }

        return $dataSource;
    }
}