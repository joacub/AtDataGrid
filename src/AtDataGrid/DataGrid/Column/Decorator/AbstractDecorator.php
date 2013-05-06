<?php

namespace AtDataGrid\DataGrid\Column\Decorator;

use Zend\View\Model\ViewModel;
use AtDataGrid\DataGrid\Column\Column;
abstract class AbstractDecorator implements DecoratorInterface
{
    /**
     * Placement constants
     */
    const APPEND  = 'append';
    const PREPEND = 'prepend';
    const REPLACE = 'replace';

    /**
     * Default placement: append
     * @var string
     */
    protected $placement;

    /**
     * Default separator: ' '
     * @var string
     */
    protected $separator = ' ';
    
    protected $column;

    /**
     * @param string $placement
     */
    public function __construct(Column $column, $placement = self::APPEND)
    {
        $this->setPlacement($placement);
        $this->column = $column;
    }

    /**
     * @param $placement
     * @return AbstractDecorator
     */
    public function setPlacement($placement)
    {
        $this->placement = $placement;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlacement()
    {
        return $this->placement;
    }

    /**
     * @param $separator
     * @return AbstractDecorator
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
        return $this;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }
    
    public function render($value)
    {
    	$viewModel = new ViewModel();
    	
    	$viewModel->setTemplate('at-datagrid/grid/rows/item');
    	$viewModel->setVariable('value', $value)->setVariable('column', $this->column);
    	return $viewModel;
    }
}