<?php

namespace Application\Controller;

use Application\Mapper\WPUser as WPUserMapper;
use Application\Model\WPUser;
use Zend\Debug\Debug;
use Zend\Http\PhpEnvironment\Response;
use Zend\Ldap\Attribute as LdapAttribute;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap as ZendLdap;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;

class AuthController extends AbstractActionController
{
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

                    /** @var \Zend\Authentication\Adapter\Ldap $ldapAdapter */
                    $ldapAdapter = $this->getServiceLocator()->get('ldap_auth_adapter');
                    $username = $form->get('username')->getValue();
                    $password = $form->get('password')->getValue();

                    $ldapResult = $ldapAdapter->setIdentity($username)->setCredential($password)->authenticate();
                    if (!$ldapResult->isValid()) {
                        /** @var \Zend\Authentication\Adapter\DbTable\CallbackCheckAdapter $wpAdapter */
                        $wpAdapter = $this->getServiceLocator()->get('auth_adapter_wordpress');
                        $wpResult = $wpAdapter->setIdentity($username)->setCredential($password)->authenticate();

                        if ($wpResult->isValid()) {
                            $wpUser = $wpAdapter->getResultRowObject(null, array('user_pass'));
                            /** @var \Application\Mapper\WPUserMeta $wpMeta */
                            $wpMeta = $this->getServiceLocator()->get('mapper/wpusermeta');
                            $groups = unserialize($wpMeta->getMetaForUser($wpUser, 'wp_capabilities')->meta_value);
                            $rfid = $wpMeta->getMetaForUser($wpUser, 'rfid_code')->meta_value;

                            $entry = [];
                            LdapAttribute::setAttribute($entry, 'cn', $wpUser->user_login);
                            LdapAttribute::setAttribute($entry, 'rfidCode', $rfid);
                            LdapAttribute::setAttribute($entry, 'mail', $wpUser->user_email);
                            LdapAttribute::setAttribute($entry, 'objectClass', 'User');
                            LdapAttribute::setAttribute($entry, 'samAccountName', $wpUser->user_login);
                            LdapAttribute::setPassword($entry, $password, LdapAttribute::PASSWORD_UNICODEPWD);
                            LdapAttribute::setAttribute($entry, 'userAccountControl', 512);

//                            $ldap = $ldapAdapter->getLdap();
                            /** @var ZendLdap $ldap */
                            $ldap = $this->getServiceLocator()->get('ldap');
                            $dn = sprintf('CN=%s,CN=Users,DC=hackspace,DC=internal', $wpUser->user_login);
                            $ldap->add($dn, $entry);

                            $dn = $ldap->getCanonicalAccountName($username, ZendLdap::ACCTNAME_FORM_DN);
                            $ldapPasswordArray = [];
                            LdapAttribute::setPassword($ldapPasswordArray, $password, LdapAttribute::PASSWORD_UNICODEPWD);
                            try {
                                $ldap->update($dn, $ldapPasswordArray);
                            } catch(LdapException $e) {
//                                $ldapAdapter->getLdap()->delete($dn);
                                Debug::dump($e->getMessage());
                                die();
                            }
//                            Debug::dump($hm);
//                            Debug::dump($hm);
//                            \Zend\Debug\Debug::dump($groups);
//                            \Zend\Debug\Debug::dump($rfid);
                            //$ldapAdapter->getLdap()->add();
                        } else {
                            $this->flashMessenger()->addMessage('The username and/or password is invalid');
                            foreach($ldapResult->getMessages() as $message) {
                                $this->flashMessenger()->addMessage($message);
                            }
                            $this->redirect()->refresh();
                        }
                    } else {
                        $ldap = $this->getServiceLocator()->get('ldap');
                        $this->flashMessenger()->addMessage('Logged in via LDAP!');
                        $dn = sprintf('CN=%s,CN=Users,DC=hackspace,DC=internal', $username);
                        $ldapPasswordArray = [];
                        LdapAttribute::setPassword($ldapPasswordArray, 'Frogs22ontheroof', LdapAttribute::PASSWORD_UNICODEPWD);
                        try {
                            $ldap->update($dn, $ldapPasswordArray);
                        } catch(LdapException $e) {
                            $this->flashMessenger()->addMessage($e->getMessage());
                        }
                        $this->redirect()->refresh();
                    }
                }
            }

            return array(
                'loginForm' => $form
            );
        }
    }
}
