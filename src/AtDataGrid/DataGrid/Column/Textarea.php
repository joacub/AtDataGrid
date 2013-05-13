<?php

namespace AtDataGrid\DataGrid\Column;

use AtDataGrid\DataGrid\Column\Decorator\Literal as DLiteral;

class Textarea extends Column
{
    public function init()
    {
        parent::init();

        $this->setFormElement(new \Zend\Form\Element\Textarea($this->getName()))->addDecorator(new DLiteral($this));
    }
}