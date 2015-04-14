<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Application\Form\Login as LoginForm;
use Application\Mapper\WPUser as WPUserMapper;
use Application\Mapper\WPUserMeta as WPUserMetaMapper;
use Application\Model\WPUser as WPUserModel;
use Application\Model\WPUserMeta as WPUserMetaModel;
use Zend\Authentication\Adapter\Ldap as LdapAuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as AuthenticationSessionStorage;
use Zend\Console\Request as ConsoleRequest;
use Zend\Debug\Debug;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ObjectProperty as ObjectPropertyHydrator;

class Module
{
    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'aliases' => [
                'Zend\Authentication\AuthenticationService' => 'service/auth',
            ],
            'factories' => [
                'ldap_auth_adapter' => [$this, 'factory_auth_adapter_ldap'],
                'ldap' => [$this, 'factory_ldap'],
                'service/auth' => [$this, 'factory_service_auth'],
                'form/loginform' => [$this, 'factory_form_login'],
                'form/migration' => [$this, 'factory_form_migration'],
                'mapper/wpuser' => [$this, 'factory_mapper_wpuser'],
                'mapper/wpusermeta' => [$this, 'factory_mapper_wpusermeta'],
            ]
        ];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $sharedEvents = $eventManager->getSharedManager();
        $sharedEvents->attach(AbstractActionController::class, 'dispatch', [$this, 'require_login'], 1000);
    }

    public function require_login(MvcEvent $e)
    {
        $controller = $e->getTarget();
        $request = $controller->getRequest();
        /** @var AuthenticationService $authService */
        $authService = $e->getApplication()->getServiceManager()->get('service/auth');

        // Skip ACL checks for Console based requests
        if (!$request instanceof ConsoleRequest) {
            $matchedRoute = $controller->getEvent()->getRouteMatch()->getMatchedRouteName();
            $allowedRoutes = array('login', 'login/migrate', 'logout', 'register');
            if (in_array($matchedRoute, $allowedRoutes) || $authService->hasIdentity()) {
                return; // they're logged in or on the login page, allow
            }
            // otherwise, redirect to the login page
            return $controller->redirect()->toRoute('login');
        }
    }

    /**
     * @param ServiceManager $sm
     * @return LdapAuthAdapter
     */
    public function factory_auth_adapter_ldap(ServiceManager $sm)
    {
        $config = $sm->get('Config');
        $ldapConfig = $config['ldap'];
        return new LdapAuthAdapter(['server' => $ldapConfig]);
    }

    /**
     * @param ServiceManager $sm
     * @return LoginForm
     */
    public function factory_form_login(ServiceManager $sm)
    {
        return new LoginForm();
    }

    public function factory_form_migration(ServiceManager $sm)
    {
        return new Form\LdapMigrate();
    }

    /**
     * @param ServiceManager $sm
     * @return AuthenticationService
     */
    public function factory_service_auth(ServiceManager $sm)
    {
        return new AuthenticationService(new AuthenticationSessionStorage('Authentication'));
    }

    /**
     * @param ServiceManager $sm
     * @return Ldap
     */
    public function factory_ldap(ServiceManager $sm)
    {
        $config = $sm->get('Config');
        $ldapConfig = $config['ldap'];
        try {
            $ldap = new Ldap($ldapConfig);
            $ldap->bind($ldapConfig['username'], $ldapConfig['password']);
        } catch (LdapException $e) {
            Debug::dump($e->getMessage());
            die();
        }

        return $ldap;
    }


    /**
     * @param ServiceManager $sm
     * @return WPUser
     */
    public function factory_mapper_wpuser(ServiceManager $sm)
    {
        $class = new WPUserMapper();
        $class->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
        $class->setEntityPrototype(new WPUserModel());
        $class->setHydrator(new ObjectPropertyHydrator());
        return $class;
    }

    /**
     * @param ServiceManager $sm
     * @return WPUserMeta
     */
    public function factory_mapper_wpusermeta(ServiceManager $sm)
    {
        $class = new WPUserMetaMapper();
        $class->setDbAdapter($sm->get('Zend\Db\Adapter\Adapter'));
        $class->setEntityPrototype(new WPUserMetaModel());
        $class->setHydrator(new ObjectPropertyHydrator());
        return $class;
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }
}
