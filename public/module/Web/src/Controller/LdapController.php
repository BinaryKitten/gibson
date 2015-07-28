<?php

namespace Web\Controller;

use Zend\Debug\Debug as ZDebug;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class LdapController extends AbstractActionController
{
    /** @var \Application\Mapper\LdapMapper $ldapMapper */
    protected $ldapMapper = null;

    public function onDispatch(MvcEvent $e)
    {
        $this->ldapMapper = $this->getServiceLocator()->get('Application\Mapper\Ldap');

        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        return $this->getResponse()->setContent('<h1>LDAP TESTING!</h1>');
    }

    public function creategroupAction()
    {

    }

    public function userGroupAction()
    {

        $groupName = 'userGroupAction';
        $userName  = 'demouser';

        $this->ldapMapper->createGroup($groupName);

        $groups = $this->ldapMapper->getGroupsOfUser($userName);
        ZDebug::dump($groups['names'], 'Before Add');

        $this->ldapMapper->addUserToGroup($userName, $groupName);
        $groups = $this->ldapMapper->getGroupsOfUser($userName);
        ZDebug::dump($groups['names'], 'After Add');

        $this->ldapMapper->removeUserFromGroup($userName, $groupName);

        $groups = $this->ldapMapper->getGroupsOfUser($userName);
        ZDebug::dump($groups['names'], 'After Remove');

        return $this->getResponse()->setContent('');
    }

    public function deluserfromgroupAction()
    {

        $groups = $this->ldapMapper->getGroupsOfUser('demouser');
        ZDebug::dump($groups['names'], 'Before');

        $this->ldapMapper->createGroup('adduser2groupAction');
        $this->ldapMapper->removeUserFromGroup('demouser', 'floppy');
        $this->ldapMapper->removeUserFromGroup('demouser', 'adduser2groupAction');
        $groups = $this->ldapMapper->getGroupsOfUser('demouser');

        ZDebug::dump($groups['names'], 'After');

        $this->ldapMapper->removeGroup('adduser2groupAction');

        $allGroups = $this->ldapMapper->getAllGroups();
        ZDebug::dump($allGroups);

        return $this->getResponse()->setContent('');
    }

    public function allgroupsAction()
    {
        $g = $this->ldapMapper->getAllGroups();
        ZDebug::dump($g);

        return $this->getResponse()->setContent('');
    }

    public function demousergroupsAction()
    {
        $groups = $this->ldapMapper->getGroupsOfUser('demouser');

        return $this->getResponse()->setContent(ZDebug::dump($groups, '', false));
    }
}