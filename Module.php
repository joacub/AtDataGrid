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

        $sl = $e->getApplication()->getServiceManager();

        $em = $sl->get('Doctrine\ORM\EntityManager');

        $defaultLang = str_replace('_', '-', \Locale::getDefault());

        try {
            $detector = $sl->get('SlmLocale\Locale\Detector');
            $configSlm =$sl->get('config');
            $configSlm = $configSlm['slm_locale'];
            /**
             * @var $detector Detector
             */

            if(isset($configSlm['aliases'][$detector->getDefault()])) {
                $defaultLang = $configSlm['aliases'][$detector->getDefault()];
            } else {
                $defaultLang = $detector->getDefault();
            }
        } catch (\Exception $e) {
        }

        $events = $em
            ->getEventManager()
            ->getListeners();
        foreach ($events as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof TranslatableListener) {
                    $listener->setTranslatableLocale($defaultLang);
                    $listener->setDefaultLocale($defaultLang);
                    $listener->setPersistDefaultLocaleTranslation(true);
                    $listener->setTranslationFallback(true);
                }
            }
        }

    }
}
