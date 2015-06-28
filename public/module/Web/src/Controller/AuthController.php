<?php

namespace Web\Controller;

use Application\Exception\RFIDException;
use Application\Mapper\UserDataMapper;
use Application\Mapper\UserRFIDMapper;
use Application\Mapper\WPUserMapper;
use Application\Model\WPUser;
use Zend\Authentication\AuthenticationService;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;


class AuthController extends AbstractActionController
{

    /** @var null|Container $wpSession */
    protected $wpSession = null;

    public function __construct()
    {
        $this->wpSession = new Container('wp_auth');
    }

    public function loginAction()
    {
//        if($this->identity()->
        $prg = $this->postRedirectGet('login');
        if ($prg instanceof Response) {
            return $prg;
        } else {
            /** @var \Zend\Form\Form $form */
            $form = $this->getServiceLocator()->get('form\loginForm');
            if ($prg) {
                $form->setData($prg);
                if ($form->isValid()) {

                    $username = $form->get('username')->getValue();
                    $password = $form->get('password')->getValue();

                    /** @var \Zend\Authentication\Adapter\Ldap $ldapAdapter */
                    $ldapAdapter = $this->getServiceLocator()->get('ldap_auth_adapter');

                    /** @var AuthenticationService $authService */
                    $authService = $this->getServiceLocator()->get('service/auth');

                    $ldapResult = $authService->authenticate($ldapAdapter->setIdentity($username)->setCredential($password));
                    if (!$ldapResult->isValid()) {
                        /** @var \Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter $wpAdapter */
                        $wpAdapter = $this->getServiceLocator()->get('auth_adapter_wordpress');
                        $wpResult = $wpAdapter->setIdentity($username)->setCredential($password)->authenticate();

                        if ($wpResult->isValid()) {
                            $authResultObject = $wpAdapter->getResultRowObject(null, array('user_pass'));

                            $this->wpSession['wpUser'] = $authResultObject;
                            return $this->redirect()->toRoute('login/migrate'); //return redirection object
                        } else {
                            $this->flashMessenger()->addMessage('The username and/or password is invalid');
                            return $this->redirect()->refresh();
                        }
                    } else {
                        $authService->getStorage()->write($ldapAdapter->getAccountObject());
                        return $this->redirect()->toRoute('home');
                    }
                }
            }

            return array(
                'loginForm' => $form,
            );
        }
    }

    public function migrateAction()
    {
        if (!$this->wpSession->offsetExists('wpUser')) {
            return $this->redirect()->toRoute('login');
        }
        /** @var WPUser $wpUser */
        $wpUser = $this->wpSession->wpUser;
        /** @var \Application\Mapper\WPUserMapper $wpUserMapper */
        $wpUserMapper = $this->getServiceLocator()->get('Application\Mapper\WPUserMapper');
        $wpUser = $wpUserMapper->convertAuthResult($wpUser);

//        return $this->getResponse()->setContent(var_export($wpUser, true));

        $prg = $this->postRedirectGet('login/migrate');
        if ($prg instanceof Response) {
            return $prg;
        } else {
            // if Form Valid then
            //      migrate user
            //      remove user from wp db
            //      set the user as logged in
            //      redirect to home
            // end if;
            /** @var \Zend\Form\Form $form */
            $form = $this->getServiceLocator()->get('form\migration');

            if ($prg) {
                $form->setData($prg);
                if ($form->isValid()) {
                    $roles = $wpUser->roles;
                    $newPassword = $form->get('password')->getValue();

                    /** @var UserRFIDMapper $rfidDataMapper */
                    $rfidDataMapper = $this->getServiceLocator()->get('Application\Mapper\UserRFID');
                    try {
                        $result = $rfidDataMapper->addRFIDtoUser($wpUser, $wpUser->rfid_code, 'Primary RFID');
                    } catch(RFIDException $rfidException) {
                        //ignore the exception - UI will handle no RFID for user later
                    }

                    /** @var UserDataMapper $userDataMapper */
                    $userDataMapper = $this->getServiceLocator()->get('Application\Mapper\UserData');
                    try {
                        $userDataMapper->createUser($wpUser);
                    } catch(\Exception $e) {
//                        \Zend\Debug\Debug::dump($e);
                    }

                    /** @var \Application\Mapper\LdapMapper $ldapMapper */
                    $ldapMapper = $this->getServiceLocator()->get('Application\Mapper\Ldap');

                    $ldapMapper->createUser($wpUser, $newPassword);

                    die();

//                    return $this->redirect()->refresh();
//                    $dn = $ldap->getCanonicalAccountName($username, ZendLdap::ACCTNAME_FORM_DN);
//                    $ldapPasswordArray = [];
//                    LdapAttribute::setPassword($ldapPasswordArray, $password, LdapAttribute::PASSWORD_UNICODEPWD);
//                    try {
//                        $ldap->update($dn, $ldapPasswordArray);
//                    } catch (LdapException $e) {
//                        Debug::dump($e->getMessage());
//                        die();
//                    }
                }
            }
        }

        return array(
            'migrationForm' => $form
        );
    }


    public function logoutAction()
    {
        /** @var AuthenticationService $authService */
        $authService = $this->getServiceLocator()->get('service/auth');
        $authService->clearIdentity();
    }
}
