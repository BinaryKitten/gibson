<?php

namespace Arazu\Service;

use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Form\Annotation\AnnotationBuilder;
use Arazu\Controller\AbstractCrudController;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Hydrator\ObjectProperty;



class ArazuAbstractFactory implements AbstractFactoryInterface
{

    static $instance = null;

    protected $view_config = [];

    protected $arazuConfig = [];

    protected $arazuConfigDefaults = [
        'base-namespace' => 'Arazu'
    ];

    /**
     * Singleton access
     *
     * @return ArazuAbstractFactory
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Prepares the view layer
     *
     * @param  $event
     * @return void
     */
    public function onBootstrap(MvcEvent $event)
    {
        $application = $event->getApplication();
        $services = $application->getServiceManager();
        $config = $services->get('Config');
        $sharedEvents = $application->getEventManager()->getSharedManager();

        $this->view_config = isset($config['view_manager']) && (is_array($config['view_manager']) || $config['view_manager'] instanceof ArrayAccess)
            ? $config['view_manager']
            : [];

        $injectTemplateListener  = $this->getInjectTemplateListener();
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($injectTemplateListener, 'injectTemplate'), -85);

        $this->processArazuConfig($config);
    }

    /**
     * @return InjectTemplateListener
     */
    public function getInjectTemplateListener()
    {
        $listener = new InjectTemplateListener();
        if (isset($this->view_config['controller_map'])) {
            $listener->setControllerMap($this->view_config['controller_map']);
        }
        return $listener;
    }

    /**
     * @param array $config
     */
    public function processArazuConfig(array $config)
    {

        $arazuConfig = isset($config['arazu']) && (is_array($config['arazu']) || $config['arazu'] instanceof ArrayAccess)
            ? $config['arazu']
            : $this->arazuConfigDEfaults;
        $arazuConfig = ArrayUtils::merge($this->arazuConfigDefaults, $arazuConfig);

        if (!is_array($arazuConfig['base-namespace'])) {
            $arazuConfig['base-namespace'] = array($arazuConfig['base-namespace']);
        }
        $arazuConfig['base-namespace'][] = 'Arazu';

        $this->arazuConfig = $arazuConfig;
    }

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if (empty($this->arazuConfig)) {
            $this->processArazuConfig($serviceLocator->get('config'));
        }

        if (strpos($requestedName,'\\') === false) {
            return false;
        }

        $parts = explode('\\', $requestedName);
        if (count($parts) != 3) {
            return false;
        }

        if (!in_array($parts[0], $this->arazuConfig['base-namespace'])) {
            return false;
        }

        list($ns, $type, $what) = $parts;

        if (!in_array($type, array('Form', 'Model', 'Mapper', 'Controller'))) {
            return false;
        }

        $entity = $ns . '\\Model\\' . $what;

        if (!class_exists($entity, true)) {
            return false;
        }

        return true;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $parts = explode('\\', $requestedName);
        list($ns, $type, $what) = $parts;
        $entityClass = $ns . '\\Model\\' . $what;

        switch($type) {
            case 'Controller':

                $controllerClass = $ns . "\\Controller\\" . ucfirst($what) . 'Controller';
                if (class_exists($controllerClass)) {
                    $class = new $controllerClass();
                } else {
                    $class = new AbstractCrudController();
                }

                if (method_exists($class, 'setMapper')) {
                    $mapper = $ns . '\\Mapper\\' . $what;
                    $class->setMapper($serviceLocator->getServiceLocator()->get($mapper));
                }

                return $class;
                break;

            case 'Mapper':
                $mapperClass = $ns . "\\Mapper\\" . ucfirst($what) . 'Mapper';
                $mapper = new $mapperClass;
                $mapper->setDbAdapter($serviceLocator->get('Zend\Db\Adapter\Adapter'));
                $mapper->setHydrator(new ObjectProperty());
                $mapper->setEntityPrototype(new $entityClass);
                return $mapper;
                break;

            case 'Model':
                return new $entityClass;
                break;

            case 'Form':
                $formClass = $ns . '\\Form\\' . $what;
                if (class_exists($formClass, true)) {
                    return new $formClass;
                }

                $builder = new AnnotationBuilder();
                $entityObj = new $entityClass;

                $form = $builder->createForm($entityObj);
                $form->bind($entityObj);
                $form->add(array(
                    'name' => 'submit',
                    'attributes' => array(
                        'type' => 'submit',
                        'value' => 'Save',
                        'id' => 'submitbutton',
                    ),
                ));

                return $form;
        }
    }
}
