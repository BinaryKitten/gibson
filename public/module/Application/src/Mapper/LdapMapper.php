<?php

namespace Application\Mapper;


use Application\Model\WPUser;
use Zend\Ldap\Attribute as LdapAttribute;
use Zend\Ldap\Collection as LdapCollection;
use Zend\Ldap\Exception\LdapException;
use Zend\Ldap\Filter as LdapFilter;
use Zend\Ldap\Ldap as ZendLdap;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LdapMapper implements ServiceLocatorAwareInterface
{

    /** @var ZendLdap $ldap */
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
     *
     * @return LdapMapper
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        return $this;
    }

    /**
     * @return ZendLdap
     */
    public function getLdap()
    {
        return $this->ldap;
    }

    /**
     * @param ZendLdap $ldap
     */
    public function setLdap(ZendLdap $ldap)
    {
        $this->ldap = $ldap;
    }

    /**
     * @param $samAccountName
     *
     * @return string
     */
    protected function getUserDn($samAccountName)
    {
        return sprintf('CN=%s,CN=Users,DC=hackspace,DC=internal', strtolower($samAccountName));
    }

    /**
     * @param $groupName
     *
     * @return string
     */
    protected function getGroupDn($groupName)
    {
        return sprintf("CN=%s,CN=Users,DC=hackspace,DC=internal", strtolower($groupName));
    }

    /**
     * @param $userData
     * @param $newPassword
     *
     * @throws LdapException
     * @throws \Exception
     */
    public function createUser($userData, $newPassword)
    {
        $entry = [];

        if ($userData instanceof WPUser) {
            $userData = array(
                'samAccountName' => $userData->user_login,
                'email'          => $userData->user_email,
                'nickname'       => $userData->nickname
            );
        }

        $userData['samAccountName'] = strtolower($userData['samAccountName']);
        $userData['email']          = strtolower($userData['email']);

        LdapAttribute::setAttribute($entry, 'cn', $userData['samAccountName']);
        LdapAttribute::setAttribute($entry, 'mail', $userData['email']);
        LdapAttribute::setAttribute($entry, 'objectClass', 'User');
        LdapAttribute::setAttribute($entry, 'samAccountName', $userData['samAccountName']);
        LdapAttribute::setAttribute($entry, 'displayName', $userData['nickname']);
        LdapAttribute::setPassword($entry, $newPassword, LdapAttribute::PASSWORD_UNICODEPWD);
        LdapAttribute::setAttribute($entry, 'userAccountControl', 512);

        try {
            $this->getLdap()->save($this->getUserDn($userData['samAccountName']), $entry);
        } catch (LdapException $lde) {
            switch ($lde->getCode()) {
                case LdapException::LDAP_NOT_ALLOWED_ON_RDN:
                    break;
                default:
                    throw $lde;
                    break;
            }
        }
    }

    /**
     * @param $samAccountName
     * @param $password
     * @param string $redirect
     *
     * @return bool
     */
    public function authenticate($samAccountName, $password, $redirect = '')
    {
        /** @var \Zend\Authentication\AuthenticationService $authService */
        $authService = $this->getServiceLocator()->get('service/auth');
        /** @var \Zend\Authentication\Adapter\Ldap $ldapAuthAdapter */
        $ldapAuthAdapter = $this->getServiceLocator()->get('ldap_auth_adapter');

        $samAccountName = strtolower($samAccountName);
        $result         = $ldapAuthAdapter->setIdentity($samAccountName)->setCredential($password)->authenticate();
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
     *
     * @throws LdapException
     */
    public function createGroup($groupName, $description = '')
    {
        $groupName = strtolower($groupName);
        $newGroup  = [
            'cn'             => $groupName,
            'objectClass'    => ["top", "group"],
            'groupType'      => '-2147483646', // security group
            "sAMAccountName" => $groupName,
//        // use this to add members when creating group.  If you dont want to just remove it
//        $addgroup_ad['member']= array();
        ];
        if ( ! empty( $description )) {
            $newGroup["description"] = $description;
        }

        try {
            $this->getLdap()->add($this->getGroupDn($groupName), $newGroup);
        } catch (LdapException $lde) {
            if ($lde->getCode() != LdapException::LDAP_ALREADY_EXISTS) {
                throw $lde;
            }
        }
    }

    /**
     * @param $samAccountName
     * @param $groupName
     *
     * @throws LdapException
     */
    public function addUserToGroup($samAccountName, $groupName)
    {
        $groupDN = $this->getGroupDn($groupName);

        /** @var ZendLdap $ldap */
        $ldap = $this->getLdap();

        $entry = $ldap->getEntry($groupDN);

        if ( ! array_key_exists('member', $entry)) {
            $entry['member'] = array();
        }

        $keys = ['objectSid', 'sAMAccountType', 'cn'];
        foreach ($keys as $key) {
            unset( $entry[$key], $entry[strtolower($key)] );
        }
        $entry['member'][] = $this->getUserDn($samAccountName);

        try {
            $this->getLdap()->update($this->getGroupDn($groupName), $entry);
        } catch (LdapException $lde) {
            if ($lde->getCode() != LdapException::LDAP_TYPE_OR_VALUE_EXISTS) {
                throw $lde;
            }
            // else "already added to group"
        }
    }

    /**
     * @param string $samAccountName
     *
     * @return array
     * @throws LdapException
     */
    public function getGroupsOfUser($samAccountName)
    {
        $ldap       = $this->getLdap();
        $filter     = LdapFilter::equals('samaccountname', strtolower($samAccountName));
        $baseDn     = 'CN=Users,DC=hackspace,DC=internal';
        $attributes = array('memberOf');
        $scope      = ZendLdap::SEARCH_SCOPE_SUB;

        $results = $ldap->search($filter, $baseDn, $scope, $attributes);
        if ($results->count() != 1) {
            return [];
        }
        $results = $results->getFirst();

        if ( ! array_key_exists('memberof', $results)) {
            return [];
        }
        $groupDNs = $results['memberof'];
        $groups   = [
            'names'   => [],
            'entries' => []
        ];
        foreach ($groupDNs as $idx => $groupDn) {
            $groupEntry          = $ldap->getEntry($groupDn);
            $groups['entries'][] = $groupEntry;
            $groupName           = $groupEntry['name'];
            if (is_array($groupName) && count($groupName) == 1) {
                $groupName = array_pop($groupName);
            }
            $groups['names'][] = $groupName;
        }

        return $groups;
    }

    public function getUsersOfGroup($groupName)
    {

    }


    public function removeUserFromGroup($samAccountName, $groupName)
    {
        // get group from LDAP
        // filter out user DN from member array
        // update group back to ldap
        //
        $groupDN = $this->getGroupDn($groupName);
        $userDn  = $this->getUserDn($samAccountName);

        /** @var ZendLdap $ldap */
        $ldap = $this->getLdap();

        $entry = $ldap->getEntry($groupDN);

        if ($entry === null) {
            return;
        }
        if ( ! array_key_exists('member', $entry)) {
            return;
        }

        $userIdx = array_search($userDn, $entry['member']);
        if ($userIdx !== false) {
            unset( $entry['member'][$userIdx] );
            $keys = ['objectSid', 'sAMAccountType', 'cn'];
            foreach ($keys as $key) {
                unset( $entry[$key], $entry[strtolower($key)] );
            }
            $ldap->update($groupDN, $entry);
        }

    }

    public function removeUser($samAccountName)
    {
        // ldap delete user DN
    }


    public function removeGroup($groupName)
    {
        // ldap delete group dn
        $this->getLdap()->delete($this->getGroupDn($groupName));
    }

    public function getAllGroups()
    {
        $ldap       = $this->getLdap();
        $filter     = LdapFilter::contains('objectClass', 'group');
        $baseDn     = 'CN=Users,DC=hackspace,DC=internal';
        $attributes = array();
        $scope      = ZendLdap::SEARCH_SCOPE_SUB;

        $results = $ldap->search($filter, $baseDn, $scope, $attributes);
        $groups  = [
            'names'   => [],
            'entries' => []
        ];

        foreach ($results as $group) {
            $groupName = $group['name'];
            if (is_array($groupName) && count($groupName) == 1) {
                $groupName = array_pop($groupName);
            }
            $groups['names'][]   = $groupName;
            $groups['entries'][] = $group;
        }

        return $groups;
    }

    /**
     * @param string $role
     *
     * @return bool|string
     */
    public function mapRoleToGroups($role)
    {
        $rolesToDn = [

        ];

        if (in_array(strtolower($role), $rolesToDn)) {
            return $rolesToDn[$role];
        }

        return false;
    }
}
