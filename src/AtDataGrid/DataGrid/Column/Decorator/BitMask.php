<?php

namespace AtDataGrid\DataGrid\Column\Decorator;

use AtDataGrid\DataGrid\Column\Column;
class BitMask extends AbstractDecorator
{
    /**
     * @var array
     */
    protected $statuses = array();
    
    /**
     * @param array $statuses
     */
    public function __construct($statuses = array(), Column $column)
    {
        if ($statuses) {
            $this->setStatuses($statuses);            
        }
        
       parent::__construct($column);
    }

    /**
     * @param array $statuses
     */
    public function setStatuses($statuses = array())
    {
        $this->statuses = $statuses;
    }
    
    /**
     * @param $value
     * @param $row
     * @return string
     */
    public function render($value)
    {
        $str = '';
        foreach ($this->statuses as $name => $status) {
            if ($this->checkStatus($status, $value)) {
                $str .= '<div>' . $name . ': <b>Yes</b></div>';
            } else {
                $str .= '<div>' . $name . ': No</div>';
            }    
        }
        
        parent::render($str);
        
    }
    
    /**
     * @param $status
     * @param $value
     * @return int
     */
    protected function checkStatus($status, $value)
    {
        return $value & $status;
    }    
}