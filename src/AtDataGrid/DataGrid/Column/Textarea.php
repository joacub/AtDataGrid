<?php

namespace AtDataGrid\DataGrid\Column;

use AtDataGrid\DataGrid\Column\Decorator;
use AtDataGrid\DataGrid\Column\Decorator\Literal;

class Textarea extends Column
{
    public function init()
    {
        parent::init();

        $this->setFormElement(new \Zend\Form\Element\Textarea($this->getName()))->addDecorator(new Literal($this));
    }
}