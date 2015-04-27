<?php

namespace Application\Mapper;

use Application\Model\WPUser;
use Zend\Db\Adapter\Driver\ResultInterface as DbResult;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcBase\Mapper\AbstractDbMapper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

class UserDataMapper extends AbstractDbMapper implements ServiceLocatorAwareInterface
{
    /**
     * @var string
     */
    protected $tableName = 'gibson_user_data';

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;


    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
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
     * @param WPUser $wpUser
     *
     * @return DbResult
     */
    public function createUserDataFromWordpress($wpUser)
    {
        /** @var WPUserMetaMapper $wpUserMeta */
        $wpUserMeta = $this->getServiceLocator()->get('Application\Mapper\WPUserMetaMapper');
        $metaData = $wpUserMeta->getMetaForUser($wpUser);

        return $this->insert(array(
           'samAccountName' => $wpUser->user_login,
            'phone' => '',
            'emergency_details' => '',
            'emergency_details_expiry' => '',
            'medical_information' => '',
            'medical_information_expiry' => '',
            'address_line1' => '',
            'address_line2' => '',
            'address_town' => '',
            'address_city' => '',
            'address_county' => '',
            'address_postcode' => '',
        ));
    }

}
