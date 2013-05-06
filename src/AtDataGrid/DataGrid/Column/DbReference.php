<?php
namespace AtDataGrid\DataGrid\Column;

use AtDataGrid\DataGrid\Column\Decorator\DbReference as DecoratorDbReference;
use Nette\Diagnostics\Debugger;
class DbReference extends Column
{
    /**
     * @var \AtDataGrid\DataGrid\DataSource\DoctrineDbTableGateway
     */
    protected $dataSource = null;

    /**
     * @param $name
     * @param ATF_Db_Table_Abstract $table
     * @param $referenceField
     * @param $resultFieldName
     */
    public function __construct($name, $dataSource)
    {
        $this->dataSource = $dataSource;

        parent::__construct($name);
    }

    /**
     *
     */
    public function init()
    {
        parent::init();

        $mapping = $this->dataSource->getParentDataSource()->getEm()->getClassMetadata($this->dataSource->getParentDataSource()->getEntity());
        
        $map = $mapping->associationMappings[$this->getName()];
        
        $select = $this->dataSource->getSelect();
        
        
        // Decorator
        $decorator = new DecoratorDbReference($this->dataSource, $this);
        
        $this->addDecorator($decorator);
        switch ($map['type']) {
        	case '4':
        	case '2':
        		
//         		// Form element
//         		$select = $select->leftJoin($this->dataSource->getEntity() . '.' . $this->getName(), 'alias');
//         		$allRecords = $this->dataSource->getAdapter()->fetchPairs($select);
        		
        		$formElement = new \Zend\Form\Element\Select($this->getName());
        		$formElement->setValueOptions(array('', '--'));
//         		->addMultiOptions($allRecords);
        		$this->setFormElement($formElement);
        		
        		break;
        		/**
        		 * @todo esto es un ejemplo no es funcional
        		 */
        	default:
        		// Form element
        		$select = $select->leftJoin($this->dataSource->getEntity() . '.' . $this->getName(), 'alias');
        		$allRecords = $this->dataSource->getAdapter()->fetchPairs($select);
        		
        		$formElement = new \Zend\Form\Element\Select($this->getName());
        		$formElement->addMultiOption('', '--')
        		->addMultiOptions($allRecords);
        		$this->setFormElement($formElement);
        		break;
        }
    }
}