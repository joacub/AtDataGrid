<?php

namespace AtDataGrid\DataGrid\Column\Decorator;

use AtDataGrid\DataGrid\Column\Column;
// @todo Use zf2 i18n component
class DateFormat extends AbstractDecorator
{
    /**
     * @var string
     */
    protected $format = 'd.m.Y H:i';
    
    /**
     * @param string $format
     */
    public function __construct(Column $column, $format = null)
    {
    	if ($format) {
    		$this->setFormat($format);
    	}
    	
    	parent::__construct($column);
    }

    /**
     * @param  $format
     * @return void
     */
    public function setFormat($format)
    {
    	$this->format = $format;
    }

    /**
     * @param  $value
     * @param  $row
     * @return string
     */
    public function render($value)
    {
    	if($value instanceof \DateTime) {
           return parent::render($value->format($this->format));
        }
            
        if ($value) {
            return parent::render(date($this->format, strtotime($value)));
        }
    }
}