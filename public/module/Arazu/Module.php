<?php

namespace Arazu;

use Arazu\Service\ArazuAbstractFactory;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class Module {

    public function getConfig()
    {
        return array();
    }

    public function onBootstrap(MvcEvent $event)
    {
        ArazuAbstractFactory::getInstance()->onBootstrap($event);
        $this->bootstrapRouteBasedViews($event);
    }

    public function bootstrapRouteBasedViews(MvcEvent $event)
    {
        // borrowed from EdpModuleLayouts
        $sharedEventManager = $event->getApplication()->getEventManager()->getSharedManager();

        $sharedEventManager->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function(MvcEvent $dispatchEvent) {
            $controller = $dispatchEvent->getTarget();
            $request = $controller->getRequest();
            $matchedRoute = $dispatchEvent->getRouteMatch()->getMatchedRouteName();

            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config = $dispatchEvent->getApplication()->getServiceManager()->get('config');

            $layout = 'layout/layout';

            if (array_key_exists('module_layouts', $config)) {
                if (array_key_exists($moduleNamespace, $config['module_layouts'])) {
                    $layout = $config['module_layouts'][$moduleNamespace];
                }
            }

            if (array_key_exists('route_layouts', $config)) {
                if (array_key_exists($matchedRoute, $config['route_layouts'])) {
                    $layout = $config['route_layouts'][$matchedRoute];
                }
            }

            if ($layout !== 'layout/layout') {
                if (empty($layout)) {
                    $result = $dispatchEvent->getResult();
                    if ($result instanceof ViewModel) {
                        $result->setTerminal(true);
                    }
                } else {
                    $controller->layout($layout);
                }
            }
        }, 99);

        $sharedEventManager->attach('Zend\View\View', 'render', function($e) {
            $viewModel = $e->getTarget();
        });
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src',
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'abstract_factories' => array(
                ArazuAbstractFactory::getInstance()
            ),
        );
    }

    public function getControllerConfig()
    {
        return array(
            'abstract_factories' => array(
                ArazuAbstractFactory::getInstance()
            ),
        );
    }

} 