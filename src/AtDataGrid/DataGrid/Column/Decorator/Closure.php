<?php

namespace AtDataGrid\DataGrid\Column\Decorator;

use AtDataGrid\DataGrid\Column\Column;
class Closure extends AbstractDecorator
{
	protected $closure;
	
    public function __construct($closure, Column $column, $placement = self::APPEND)
    {
    	parent::__construct($column, $placement);
    	
    	$this->closure = $closure;
    }
    
    /**
     * Render escaping the value
     */
    public function render($value, $row = false, $dataSource = false)
    {
    	$closure = $this->closure;
    	
    	$value = $closure($value, $row, $dataSource);
    	
        return parent::render($value, $row = false, $dataSource = false);
    }
}