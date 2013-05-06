<?php

namespace AtDataGrid\DataGrid\Column\Decorator;

use AtDataGrid\DataGrid\Column\Column;
class Alias extends AbstractDecorator
{
    /**
     * @var null
     */
    protected $renameTo = null;
    
    /**
     * @param null $renameTo
     */
    public function __construct($renameTo = null, Column $column)
    {
        if (null != $renameTo) {
            $this->setRenameTo($renameTo);
        }
        
       parent::__construct($column);
    }
    
    /**
     * @param $value
     * @param $row
     * @return
     */
    public function render($value)
    {
        if (!isset($this->renameTo)) {
            return $value;
        }
        
        if (isset($this->renameTo[$value])) {
            return $this->renameTo[$value];
        }
        
        parent::render($value);
        		
    }

    /**
     * @param array $renameTo
     * @return void
     */
    public function setRenameTo(Array $renameTo = array())
    {
        $this->renameTo = $renameTo;
    }       
}