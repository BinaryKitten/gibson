<?php

namespace Application\Controller;

use Zend\Authentication\AuthenticationService;
use Zend\Http\PhpEnvironment\Response;
use Zend\Ldap\Attribute as LdapAttribute;
use Zend\Ldap\Ldap as ZendLdap;
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
                            $wpUser = $wpAdapter->getResultRowObject(null, array('user_pass'));
                            $this->wpSession['wpUser'] = $wpUser;
                            return $this->redirect()->toRoute('login/migrate'); //return redirection object
                        } else {
                            $this->flashMessenger()->addMessage('The username and/or password is invalid');
                            return $this->redirect()->refresh();
                        }
                    } else {
                        $authService->getStorage()->write($ldapAdapter->getAccountObject());
                        return $this->redirect()->refresh();
                    }
                }
            }

            return array(
                'loginForm' => $form
            );
        }
    }

    public function migrateAction()
    {
        if (!$this->wpSession->offsetExists('wpUser')) {
            return $this->redirect()->toRoute('login');
        }
        $wpUser = $this->wpSession->wpUser;

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
                $x = $form->getInputFilter();
//                $d = \Zend\Debug\Debug::dump($x, 'inputFilter', false);
                if ($form->isValid()) {
//                    $this->flashMessenger()->addMessage('flop');
//                    $this->flashMessenger()->addMessage($d);
//                    return $this->redirect()->refresh();

                    /** @var \Application\Mapper\WPUserMeta $wpMeta */
                    $wpMeta = $this->getServiceLocator()->get('mapper/wpusermeta');
//            $groups = unserialize($wpMeta->getMetaForUser($wpUser, 'wp_capabilities')->meta_value);
                    $rfid = $wpMeta->getMetaForUser($wpUser, 'rfid_code')->meta_value;
                    $newPassword = $form->get('password')->getValue();
//
                    $entry = [];
                    LdapAttribute::setAttribute($entry, 'cn', $wpUser->user_login);
                    LdapAttribute::setAttribute($entry, 'rfidCode', $rfid);
                    LdapAttribute::setAttribute($entry, 'mail', $wpUser->user_email);
                    LdapAttribute::setAttribute($entry, 'objectClass', 'User');
                    LdapAttribute::setAttribute($entry, 'samAccountName', $wpUser->user_login);
                    LdapAttribute::setPassword($entry, $newPassword, LdapAttribute::PASSWORD_UNICODEPWD);
                    LdapAttribute::setAttribute($entry, 'userAccountControl', 512);
//
////                            $ldap = $ldapAdapter->getLdap();
                    /** @var ZendLdap $ldap * */
                    $ldap = $this->getServiceLocator()->get('ldap');
                    $dn = sprintf('CN=%s,CN=Users,DC=hackspace,DC=internal', $wpUser->user_login);
                    $ldap->save($dn, $entry);
//                    $ldap->add($dn, $entry);

                    /** @var \Zend\Authentication\AuthenticationService $authService */
                    $authService = $this->getServiceLocator()->get('service/auth');
                    /** @var \Zend\Authentication\Adapter\Ldap $ldapAuthAdapter */
                    $ldapAuthAdapter = $this->getServiceLocator()->get('ldap_auth_adapter');

                    $result = $ldapAuthAdapter->setIdentity($wpUser->user_login)->setCredential($newPassword)->authenticate();
//                    $result = $authService->authenticate($ldapAuthAdapter);
                    if ($result->isValid()) {
//                        $data1 = \Zend\Debug\Debug::dump($result, 'result', true);
//                        $data2 = \Zend\Debug\Debug::dump($ldapAuthAdapter->getAccountObject(), 'account', true);
//                        return $this->getResponse()->setContent($data1 . $data2);
                        $authService->getStorage()->write($ldapAuthAdapter->getAccountObject());
                        return $this->redirect()->toRoute('login');
                    }
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
