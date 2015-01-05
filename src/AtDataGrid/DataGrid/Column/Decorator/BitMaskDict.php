<?php

namespace AtDataGrid\DataGrid\Column\Decorator;

use AtDataGrid\DataGrid\Column\Column;
class BitMaskDict extends AbstractDecorator
{
    protected $choises = array();
    protected $delimiter = '<br/>';
    
    public function __construct($options = array(), Column $column)
    {
        if (array_key_exists('choises', $options)) {
            $this->setChoises($options['choises']);
        }

        if (array_key_exists('delimiter', $options)) {
            $this->setDelimiter($options['delimiter']);
        }
        
        parent::__construct($column);
    }

    public function setChoises($choises = array())
    {
        $this->choises = $choises;
    }

    public function setDelimiter($delimiter = '<br/>')
    {
        $this->delimiter = $delimiter;
    }
    
    public function render($value, $row = false, $dataSource = false)
    {
        $rs = array();
        foreach ($this->choises as $k => $v) {
            if (($value & $k) == $k) {
                $rs[] = $v;
            }
        }
        return parent::render(implode($this->delimiter, $rs));
    }
}