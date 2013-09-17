<?php
namespace AtDataGrid\DataGrid\Column\Decorator;

use Nette\Diagnostics\Debugger;
use Doctrine\ORM\PersistentCollection;
use Zend\View\Model\ViewModel;
use AtDataGrid\DataGrid\Column\Column;

class DbReference extends AbstractDecorator
{

    /**
     *
     * @var \AtDataGrid\DataGrid\DataSource\DoctrineDbTableGateway
     */
    protected $dataSource = null;

    /**
     *
     * @param \Zend\Db\TableGateway\TableGateway $tableGateway            
     * @param
     *            $referenceField
     * @param
     *            $resultFieldName
     */
    public function __construct($dataSource, Column $column)
    {
        $this->dataSource = $dataSource;
        parent::__construct($column);
    }

    /**
     *
     * @param
     *            $value
     * @param
     *            $row
     * @return
     *
     *
     */
    public function render($value, $row = false, $dataSource = false)
    {
        $containsColumns = false;
        $columns = $this->column->getColumns();
        foreach ((array) $columns as $column) {
            if ($column->isVisible()) {
                $containsColumns = true;
                break;
            }
        }
        
        $allEntities = $this->dataSource->getEm()
            ->getConfiguration()
            ->getMetadataDriverImpl()
            ->getAllClassNames();
        
        if ($containsColumns) {
            $models = array();
            foreach ($columns as $column) {
                if ($column->isVisible()) {
                    if ($value instanceof PersistentCollection) {
                        $models[] = $column->render($value, $row = false, $dataSource = false);
                    } else {
                        $name = ucfirst($column->getName());
                        if (! method_exists($value, "get{$name}")) {
                            // el valor es nulo por lo tanto seguiomos en el array
                            if (! array_search(get_parent_class($value), $allEntities)) {
                                $models[] = $column->render('');
                                continue;
                            }
                            
                            throw new \Exception('No existe el metodo ' . "'get{$name}' en el objeto " . get_parent_class($value));
                        }
                        $models[] = $column->render($value->{"get{$name}"}());
                    }
                }
            }
            
            return $models;
        } else {
            switch (true) {
                case $value instanceof PersistentCollection:
                    $value = $value->count();
                    break;
                case in_array((string) get_parent_class($value), $allEntities) !== false:
                    $value = $value->getId();
                    break;
				default:
					break;
			}
		}
		
		return parent::render($value, $row = false, $dataSource = false);
		
	}
}