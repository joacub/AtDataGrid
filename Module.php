<?php

namespace AtDataGrid;
use Zend\Mvc\MvcEvent;
use Gedmo\Translatable\TranslatableListener;

/**
 * Class Module
 * @package AtDataGrid
 */
class Module
{
    /**
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    /**
     * @return array
     */
    public function getControllerConfig()
    {
        return array(
            'invokables' => array(
                'AtDataGrid\Controller\DataGrid' => 'AtDataGrid\Controller\DataGridController'
            ),
        );
    }

    /**
     * @return array
     */
    public function getControllerPluginConfig()
    {
        return array(
            'invokables' => array(
                'backTo' => 'AtBase\Mvc\Controller\Plugin\BackTo'
            ),
        );
    }

    public function onBootstrap(MvcEvent $e)
    {
        $translator = $e->getApplication()->getServiceManager()->get('translator');
        $translator->setLocale(str_replace('-', '_', \Locale::getDefault()));

        $em = $e->getApplication()->getServiceManager()->get('Doctrine\ORM\EntityManager');

        $events = $em
            ->getEventManager()
            ->getListeners();
        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof TranslatableListener) {
                    $listener->setTranslatableLocale('es-ES');
                    $listener->setDefaultLocale('es-ES');
                    $listener->setPersistDefaultLocaleTranslation(true);
                    $listener->setTranslationFallback(true);
                }
            }
        }

    }
}
