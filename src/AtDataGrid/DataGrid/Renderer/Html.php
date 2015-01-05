<?php

namespace AtDataGrid\DataGrid\Renderer;

use Zend\View\Renderer\RendererInterface;
use Zend\View\Model\ViewModel;
use Zend\Mvc\View\Http\InjectTemplateListener;
use Nette\Diagnostics\Debugger;
use Zend\Filter\Word\CamelCaseToDash;

/**
 * Class Html
 * @package AtDataGrid\DataGrid\Renderer
 */
class Html extends AbstractRenderer
{
    /**
     * Template rendering engine
     * @var \Zend\View\Renderer\RendererInterface
     */
    protected $engine;

    /**
     * Html template
     *
     * @var string
     */
    protected $template = 'at-datagrid/grid/list';
    
    /**
     *
     * @var string;
     */
    protected $sm = null;

    /**
     * Additional CSS rules
     *
     * @var string
     */
    protected $cssFile = '';

    /**
     * @param \Zend\View\Renderer\RendererInterface $engine
     * @return $this
     */
    public function setEngine(RendererInterface $engine)
    {
    	$this->engine = $engine;
    	return $this;
    }

    /**
     * @return \Zend\View\Renderer\RendererInterface
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param $template
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
    
    /**
     *
     * @param string $originalTemplate
     * @return \ZfJoacubCrud\DataGrid\Renderer\Html
     */
    public function setServiceManager($sm)
    {
    	$this->sm = $sm;
    	return $this;
    }
    
    public function getServiceManager()
    {
    	return $this->sm;
    }

    /**
     * @param $path
     * @return $this
     */
    public function setCssFile($path)
    {
        $this->cssFile = $path;
        return $this;
    }

    /**
     * @param array $options
     * @return
     */
    public function render($variables = array())
    {
        $engine = $this->getEngine();
        
        $sm = $this->getServiceManager();
        $originalTemplateBase = $variables['gridManager']->getOriginalTemplateBase();
        
        $viewResolver = $engine->resolver();
        
        //list
        $viewModel = new ViewModel($variables);
        
        $viewModel->setTemplate($originalTemplateBase . '/grid/list');
        if(false === $viewResolver->resolve($viewModel->getTemplate()))
            $viewModel->setTemplate($this->getTemplate());
        
        //filters
        $viewGridflashMessenger = new ViewModel($variables);
        $viewGridflashMessenger->setTemplate($originalTemplateBase . '/grid/flash-messenger');
        if(false === $viewResolver->resolve($viewGridflashMessenger->getTemplate()))
        	$viewGridflashMessenger->setTemplate('at-datagrid/grid/flash-messenger');
        
        //filters
        $viewGridFilters = new ViewModel($variables);
        $viewGridFilters->setTemplate($originalTemplateBase . '/grid/filters');
        if(false === $viewResolver->resolve($viewGridFilters->getTemplate()))
            $viewGridFilters->setTemplate('at-datagrid/grid/filters');
        
        //row list
        $viewGridRowsList = new ViewModel($variables);
        $viewGridRowsList->setTemplate($originalTemplateBase . '/grid/rows/list');
        if(false === $viewResolver->resolve($viewGridRowsList->getTemplate())) {
            $viewGridRowsList->setTemplate('at-datagrid/grid/rows/list');
        }
        
        //row group actions
        $viewGridRowsGoupActions = new ViewModel($variables);
        $viewGridRowsGoupActions->setTemplate($originalTemplateBase . '/grid/rows/group-actions');
        if(false === $viewResolver->resolve($viewGridRowsGoupActions->getTemplate())) {
            $viewGridRowsGoupActions->setTemplate('at-datagrid/grid/group-actions');
        }
        
        $viewGridPaginator = new ViewModel($variables);
        $viewGridPaginator->setTemplate($originalTemplateBase . '/grid/paginator');
        if(false === $viewResolver->resolve($viewGridPaginator->getTemplate())) {
            $viewGridPaginator->setTemplate('at-datagrid/grid/paginator');
        }
        
        // pagination control
        $viewGridPaginationControl = new ViewModel();
        $viewGridPaginationControl->setTemplate($originalTemplateBase . '/grid/pagination-control');
        if(false === $viewResolver->resolve($viewGridPaginationControl->getTemplate())) {
			$viewGridPaginationControl->setTemplate(
					'at-datagrid/grid/pagination-control');
		}
		
		$viewGridPaginator->setVariable('viewGridPaginationControl', $viewGridPaginationControl->getTemplate());
		
		$viewModel->setVariable('viewGridflashMessenger', $this->getEngine()
			->render($viewGridflashMessenger))
			->setVariable('viewGridFilters', $this->getEngine()
			->render($viewGridFilters))
			->setVariable('viewGridRowsList', $this->getEngine()
			->render($viewGridRowsList))
			->setVariable('viewGridRowsGoupActions', $this->getEngine()
			->render($viewGridRowsGoupActions))
			->setVariable('viewGridPaginator', $this->getEngine()
			->render($viewGridPaginator));

        /*if (!empty($this->cssFile)) {
            $this->getView()->headLink()->appendStylesheet($this->cssFile);
        }*/

        return $engine->render($viewModel);
    }
}