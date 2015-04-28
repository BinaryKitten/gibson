<?php

namespace Web\Mapper;


use Web\Model\WPUser;
use Zend\Ldap\Ldap;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Ldap\Attribute as LdapAttribute;
use Zend\Ldap\Ldap as ZendLdap;

class LdapMapper implements ServiceLocatorAwareInterface
{

    /** @var Ldap $ldap */
    protected $ldap = null;

    /** @var ServiceLocatorInterface */
    protected $serviceLocator = null;

    /**
     * @param Ldap $ldap
     */
    public function setLdap(Ldap $ldap)
    {
        $this->ldap = $ldap;
    }

    /**
     * @return Ldap
     */
    public function getLdap()
    {
        return $this->ldap;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return LdapMapper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param $userData
     * @param $newPassword
     * @return ZendLdap
     */
    public function createUser($userData, $newPassword)
    {
        $entry = [];

        if ($userData instanceof WPUser) {
            $userData = array(
                'username' => $userData->user_login,
                'email' => $userData->user_email,
                'nickname' => $userData->nickname
            );
        }

        LdapAttribute::setAttribute($entry, 'cn', $userData['username']);
        LdapAttribute::setAttribute($entry, 'mail', $userData['email']);
        LdapAttribute::setAttribute($entry, 'objectClass', 'User');
        LdapAttribute::setAttribute($entry, 'samAccountName', $userData['username']);
        LdapAttribute::setAttribute($entry, 'displayName', $userData['nickname']);
        LdapAttribute::setPassword($entry, $newPassword, LdapAttribute::PASSWORD_UNICODEPWD);
        LdapAttribute::setAttribute($entry, 'userAccountControl', 512);

        /** @var ZendLdap $ldap * */
        $ldap = $this->getServiceLocator()->get('ldap');
        $dn = sprintf('CN=%s,CN=Users,DC=hackspace,DC=internal', $userData['username']);
        return $ldap->save($dn, $entry);
    }

    public function authenticate($username, $password, $redirect='')
    {
        /** @var \Zend\Authentication\AuthenticationService $authService */
        $authService = $this->getServiceLocator()->get('service/auth');
        /** @var \Zend\Authentication\Adapter\Ldap $ldapAuthAdapter */
        $ldapAuthAdapter = $this->getServiceLocator()->get('ldap_auth_adapter');

        $result = $ldapAuthAdapter->setIdentity($username)->setCredential($password)->authenticate();
        if ($result->isValid()) {
            $authService->getStorage()->write($ldapAuthAdapter->getAccountObject());
            return $this->redirect()->toRoute('home');
        }
    }
}
