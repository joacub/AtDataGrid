<?php

namespace AtDataGrid\DataGrid\Filter\Sql;

use AtDataGrid\DataGrid\Column\DbReference;
use AtDataGrid\DataGrid\Filter;
use AtDataGrid\DataGrid\DataSource\DoctrineDbTableGateway;
use AtDataGrid\DataGrid\Filter\Parameter\ParameterId;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Nette\Diagnostics\Debugger;

class Like extends Filter\AbstractFilter
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

        if (isset($value) && !empty($value)) {
            
            //$columnName = $this->findTableColumnName($select, $column->getName());
            $columnName = $column->getName();

            if($dataSource instanceof QueryBuilder) {
            	$dataSource instanceof \Doctrine\ORM\QueryBuilder;
                $parameter = ParameterId::getParameter(__CLASS__, $columnName);
                $qb = $dataSource;
                
                if($column->getParent() == null) {

                    if($column instanceof DbReference) {
                        $_columName = 'IDENTITY(' . $qb->getRootAlias() . '.' . $columnName . ')';
                    } else {
                        $_columName = $qb->getRootAlias() . '.' . $columnName;
                    }
                	$qb->andWhere(
                    $qb->expr()
                        ->orx(
                        $qb->expr()
                            ->like($_columName, ':' . $parameter)))->setParameter($parameter, '%' . $value . '%');
                } else {
                
                	$parentColumn = $column->getParent();
                	
                
                	$fieldAssoc = $parentColumn->getName();;
                	$field = $column->getName();
                	

                	$parts[] = $fieldAssoc;
                	$parts[] = $field;
                	 
                	$alias = implode('__', $parts);
                
                	$qb->join($qb->getRootAlias() . '.' . $fieldAssoc, $alias, Join::WITH, $qb->expr()
                        ->orx(
                        $qb->expr()
                            ->like($alias . '.' . $field, ':' . $parameter)));
                
                	$qb->setParameter($parameter, '%' . $value . '%');
                }
            } else {
                // @todo Add param for like template
                $spec = function (\Zend\Db\Sql\Where $where) use($columnName, 
                $value)
                {
                    $where->like($columnName, '%' . $value . '%');
                };
                
                $dataSource->getSelect()->where($spec);
            }
            
        }

        return $dataSource;
    }
}