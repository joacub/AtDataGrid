<?php

namespace AtDataGrid\DataGrid\Column\Decorator;

class Long2Ip extends AbstractDecorator
{
    /**
     * @param $value
     * @param $row
     * @return string
     */
    public function render($value, $row = false, $dataSource = false)
    {
        if ($value) {
            return parent::render(long2ip($value));
        }
        
        return parent::render('');
    }
}