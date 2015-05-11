<?php

namespace Application\Mapper;


use Application\Model\WPUser;
use Zend\Ldap\Attribute as LdapAttribute;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Ldap;
use Zend\Ldap\Ldap as ZendLdap;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LdapMapper implements ServiceLocatorAwareInterface
{

    /** @var Ldap $ldap */
    protected $ldap = null;

    /** @var ServiceLocatorInterface */
    protected $serviceLocator = null;

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
     * @return Ldap
     */
    public function getLdap()
    {
        return $this->ldap;
    }

    /**
     * @param Ldap $ldap
     */
    public function setLdap(Ldap $ldap)
    {
        $this->ldap = $ldap;
    }

    /**
     * @param $username
     * @return string
     */
    protected function getUserDn($username)
    {
        return sprintf('CN=%s,CN=Users,DC=hackspace,DC=internal', strtolower($username));
    }

    /**
     * @param $groupName
     * @return string
     */
    protected function getGroupDn($groupName)
    {
        return sprintf("CN=%s,DC=Groups,DC=hackspace,DC=internal", strtolower($groupName));
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

        $userData['username'] = strtolower($userData['username']);
        $userData['email'] = strtolower($userData['email']);

        LdapAttribute::setAttribute($entry, 'cn', $userData['username']);
        LdapAttribute::setAttribute($entry, 'mail', $userData['email']);
        LdapAttribute::setAttribute($entry, 'objectClass', 'User');
        LdapAttribute::setAttribute($entry, 'samAccountName', $userData['username']);
        LdapAttribute::setAttribute($entry, 'displayName', $userData['nickname']);
        LdapAttribute::setPassword($entry, $newPassword, LdapAttribute::PASSWORD_UNICODEPWD);
        LdapAttribute::setAttribute($entry, 'userAccountControl', 512);

        return $this->getLdap()->save($this->getUserDn($userData['userName']), $entry);
    }

    /**
     * @param $username
     * @param $password
     * @param string $redirect
     * @return bool
     */
    public function authenticate($username, $password, $redirect = '')
    {
        /** @var \Zend\Authentication\AuthenticationService $authService */
        $authService = $this->getServiceLocator()->get('service/auth');
        /** @var \Zend\Authentication\Adapter\Ldap $ldapAuthAdapter */
        $ldapAuthAdapter = $this->getServiceLocator()->get('ldap_auth_adapter');

        $username = strtolower($username);
        $result = $ldapAuthAdapter->setIdentity($username)->setCredential($password)->authenticate();
        if ($result->isValid()) {
            $authService->getStorage()->write($ldapAuthAdapter->getAccountObject());
            /** @todo: get redirect helper and use it to redirect */
            return $this->redirect()->toRoute($redirect);
        } else {
            return false;
        }
    }


    /**
     * @param $groupName
     * @param $description
     * @return Ldap
     * @throws LdapException
     */
    public function createGroup($groupName, $description)
    {
        $groupName = strtolower($groupName);
        $newGroup = [
            'cn' => $groupName,
            'objectClass' => ["top", "group"],
            'groupType' => '-2147483646', // security group
            "sAMAccountName" => $groupName,
//        // use this to add members when creating group.  If you dont want to just remove it
//        $addgroup_ad['member']= array();
        ];
        if (!empty($description)) {
            $newGroup["description"] = $description;
        }

//        ldap_add($this->connection, $groupDn, $newGroup);
        return $this->getLdap()->add($this->getGroupDn($groupName), $newGroup);
    }

    /**
     * @param $username
     * @param $groupName
     * @return Ldap
     * @throws LdapException
     */
    public function addUserToGroup($username, $groupName)
    {
        $entry = ["member" => $this->getUserDn($username)];
        return $this->getLdap()->update($this->getGroupDn($groupName), $entry);
//        $result = @ldap_mod_add($this->connection, $this->getGroupDn($groupName), $entry);
    }

    public function removeUserFromGroup($username, $groupName)
    {

    }

    public function removeUser($username)
    {

    }


    public function removeGroup($groupName)
    {

    }
}
