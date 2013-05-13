<?php

namespace AtDataGrid\DataGrid\Filter\Sql;

use AtDataGrid\DataGrid\Filter;
use AtDataGrid\DataGrid\DataSource\DoctrineDbTableGateway;
use AtDataGrid\DataGrid\Filter\Parameter\ParameterId;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Nette\Diagnostics\Debugger;

class Closure extends Filter\AbstractFilter
{
	protected $closure;
	public function __construct($closure, $name = null, $valueType = self::FILTER_VALUE_TYPE_STRING)
	{
		parent::__construct($name, $valueType);
		
		$this->closure = $closure;
		
	}
	
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
            
            $closure = $this->closure;
            
            $closure($dataSource, $column, $value);
            
        }

        return $dataSource;
    }
}