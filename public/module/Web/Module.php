<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Web;

use SebastianBergmann\Environment\Console;
use Web\Form\Login as LoginForm;
use Web\Form\Registration as RegistrationForm;
use Web\InputFilter\Registration as RegistrationInputFilter;
use Web\Mapper\LdapMapper;
use Web\Mapper\WPUser as WPUserMapper;
use Web\Mapper\WPUserMeta as WPUserMetaMapper;
use Application\Model\WPUser as WPUserModel;
use Application\Model\WPUserMeta as WPUserMetaModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage\Session as AuthenticationSessionStorage;
use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Application;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\ObjectProperty as ObjectPropertyHydrator;
use Zend\View\Helper\HeadLink as HeadLinkViewHelper;
use Zend\View\Helper\HeadScript as HeadScriptViewHelper;
use Zend\View\Helper\Placeholder\Container\AbstractContainer as AbstractPlaceholderContainer;

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
                'service/auth' => [$this, 'factory_service_auth'],
                'form/loginform' => [$this, 'factory_form_login'],
                'form/migration' => [$this, 'factory_form_migration'],
                'form/registration' => [$this, 'factory_form_registration'],
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

    /**
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $sharedEvents = $eventManager->getSharedManager();
        $sharedEvents->attach(AbstractActionController::class, 'dispatch', [$this, 'require_login'], 1000);
        $sharedEvents->attach(Application::class, 'render', [$this, 'setViewScripts'], 5);
    }

    /**
     * @param MvcEvent $e
     */
    public function setViewScripts(MvcEvent $e)
    {
        $controller = $e->getTarget();
        $request = $controller->getRequest();
        if ($request instanceof ConsoleRequest) {
            return;
        }

        $serviceManager = $e->getApplication()->getServiceManager();
        $viewRenderer = $serviceManager->get('Zend\View\Renderer\RendererInterface');

        /** @var HeadScriptViewHelper $headScriptViewHelper */
        $headScriptViewHelper = $viewRenderer->headScript();

        /** @var AbstractPlaceholderContainer $placeHolderViewHelper */
        $placeHolderViewHelper = $viewRenderer->placeHolder('footerScripts');

        /** @var HeadLinkViewHelper $headLinkViewHelper */
        $headLinkViewHelper = $viewRenderer->headLink();

        $headLinkViewHelper([
            'rel' => 'shortcut icon', 'type' => 'image/vnd.microsoft.icon',
            'href' => $viewRenderer->basePath() . '/favicon.ico'
        ])
//            ->prependStylesheet($renderer->basePath('application/css/style.css'))
            ->prependStylesheet($viewRenderer->basePath('css/bootstrap-theme.min.css'))
            ->prependStylesheet($viewRenderer->basePath('css/bootstrap.min.css'))
        ;

        $headScriptViewHelper
            ->appendFile("/js/jquery.min.js")
            ->appendFile("/js/bootstrap.min.js")
            ->appendFile("/application/js/script.js")
        ;

        $placeHolderViewHelper->set($headScriptViewHelper->__toString());

        $headScriptViewHelper->exchangeArray([]);

        $headScriptViewHelper->appendFile('https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js', 'text/javascript', ['conditional' => 'lt IE 9']);
        $headScriptViewHelper->appendFile('https://oss.maxcdn.com/respond/1.4.2/respond.min.js', 'text/javascript', ['conditional' => 'lt IE 9']);

        $viewRenderer->headTitle('HacMan - Membership System');
    }

    /**
     * @param MvcEvent $e
     */
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
     * @return LoginForm
     */
    public function factory_form_login(ServiceManager $sm)
    {
        return new LoginForm();
    }

    /**
     * @param ServiceManager $sm
     * @return Form\LdapMigrate
     */
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
     * @param ServiceManager $sm
     * @return RegistrationForm
     */
    public function factory_form_registration(ServiceManager $sm)
    {
        $form = new RegistrationForm();
        $registrationFilter = new RegistrationInputFilter($sm->get('Zend\Db\Adapter\Adapter'));
        $form->setInputFilter($registrationFilter);
        return $form;
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src',
                ],
            ],
        ];
    }

}
