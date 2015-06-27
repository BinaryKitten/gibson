<?php

namespace Application\Mapper;

use Application\Model\UserData as UserDataModel;
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
//        /** @var WPUserMetaMapper $wpUserMeta */
//        $wpUserMeta = $this->getServiceLocator()->get('Application\Mapper\WPUserMetaMapper');

        $dte = new \DateTime();
        $dte->add(new \DateInterval('P6M'));

        return [
           'samAccountName' => $wpUser->user_login,
            'phone' => $wpUser->phone_number,
            'emergency_details' => $wpUser->emergency_contact_name . ' ' . $wpUser->emergency_contact_details,
            'emergency_details_expiry' => $dte->format(DATE_ISO8601),
            'medical_information' => $wpUser->anything_else,
            'medical_information_expiry' => $dte->format(DATE_ISO8601),
            'address_line1' => '',
            'address_line2' => '',
            'address_town' => '',
            'address_city' => '',
            'address_county' => '',
            'address_postcode' => '',
        ];
    }


    /**
     * @param array $data
     * @return DbResult
     */
    public function createUser($data)
    {
        if ($data instanceof WPUser) {
            $data = $this->createUserDataFromWordpress($data);
        }

        $hydrator = $this->getHydrator();
        $properties = $hydrator->extract($this->getEntityPrototype());
        $processData = array_intersect_key($data, $properties);
        $dte = new \DateTime();
        $dte->add(new \DateInterval('P6M'));
        $processData['medical_information_expiry'] = $dte->format(DATE_ISO8601);
        $processData['emergency_details_expiry'] = $dte->format(DATE_ISO8601);
        return $this->insert($processData);;
    }

}
