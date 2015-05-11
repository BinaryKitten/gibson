<?php

namespace Application;


use Zend\ServiceManager\ServiceManager;
use Zend\Authentication\Adapter\Ldap as LdapAuthAdapter;

class Module
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/',
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getServiceConfig()
    {
        return [
            'factories' => [
                'ldap_auth_adapter' => [$this, 'factory_auth_adapter_ldap'],
                'ldap' => [$this, 'factory_ldap'],
                'Application/Mapper/LdapMapper' => [$this, 'factory_ldap_mapper'],
            ]
        ];
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
     * @return LdapMapper
     */
    public function factory_ldap_mapper(ServiceManager $sm)
    {
        $class = new LdapMapper();
        $class->setLdap($sm->get('ldap'));
        return $class;
    }
}
