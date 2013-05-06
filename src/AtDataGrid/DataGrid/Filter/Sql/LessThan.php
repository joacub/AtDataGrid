<?php

namespace AtDataGrid\DataGrid\Filter\Sql;

use AtDataGrid\DataGrid\Filter;
use AtDataGrid\DataGrid\Filter\Parameter\ParameterId;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;

class LessThan extends Filter\AbstractFilter
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
                
                if($column->getParent() == null) {
                	$qb->andWhere($qb->expr()->lt($qb->getRootAlias() . '.' . $column->getName(), ':' . $parameter));
                	$qb->setParameter($parameter, $value);
                } else {
                
                	$parentColumn = $column->getParent();
                	
                
                	$fieldAssoc = $parentColumn->getName();;
                	$field = $column->getName();
                	

                	$parts[] = $fieldAssoc;
                	$parts[] = $field;
                	 
                	$alias = implode('__', $parts);
                
                	$qb->join($qb->getRootAlias() . '.' . $fieldAssoc, $alias, Join::WITH, $qb->expr()->lt($alias . '.' . $field, ':' . $parameter));
                
                	$qb->setParameter($parameter, $value);
                }
                
            } else {
                $dataSource->where(
                    new \Zend\Db\Sql\Predicate\Operator($column->getName(), \Zend\Db\Sql\Predicate\Operator::OP_LT, $value)
                );
            }
            
        }

        return $dataSource;
    }
}