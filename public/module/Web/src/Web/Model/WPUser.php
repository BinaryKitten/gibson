<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 28/03/2015
 * Time: 16:57
 */

namespace Web\Model;


use Web\Mapper\WPUserMetaMapper;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WPUser implements ServiceLocatorAwareInterface
{
    public $ID;
    public $user_login;
    public $user_nicename;
    public $user_email;
    public $user_url;
    public $user_registered;
    public $user_activation_key;
    public $user_status;
    public $display_name;

    /** @var ServiceLocatorInterface $serviceLocator  */
    protected $serviceLocator;

    /** @var array $metaData */
    protected $metaData = [];

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return $this
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

    function __get($name)
    {
        /** @var WPUserMetaMapper $wpMetaMapper */
        $wpMetaMapper = $this->getServiceLocator()->get('Web\Mapper\WPUserMetaMapper');

        if (empty($this->metaData)) {
            $this->metaData = $wpMetaMapper->getMetaForUser($this);
            if ($this->metaData instanceof HydratingResultSet) {
                $this->metaData = $wpMetaMapper->metaArray($this->metaData);
            }
        }

        if ($name == 'roles') {
            $roles = $wpMetaMapper->maybe_unserialize($this->metaData['wp_capabilities']);
            return array_keys($roles);
        }

        if (!isset($this->metaData[$name])) {
            return '';
        }
        return $wpMetaMapper->maybe_unserialize($this->metaData[$name]);
    }


}
