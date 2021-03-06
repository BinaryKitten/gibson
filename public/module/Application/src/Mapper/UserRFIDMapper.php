<?php

namespace Application\Mapper;

use Application\Exception\RFIDException;
use Application\Model\UserData as UserDataModel;
use Application\Model\WPUser as WPUserModel;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use ZfcBase\Mapper\AbstractDbMapper;

class UserRFIDMapper extends AbstractDbMapper
{
    /**
     * @var string
     */
    protected $tableName = 'gibson_user_rfid';

    /**
     * @param $user
     * @param $rfid
     * @param string $description
     * @param bool $enabled
     * @return \Zend\Db\Adapter\Driver\ResultInterface
     */
    public function addRFIDtoUser($user, $rfid, $description = '', $enabled = true)
    {
        $samAccountName = $this->getSamAccountNameFromUser($user);

        $data = [
            'rfidCode' => $rfid,
            'description' => $description,
            'samAccountName' => $samAccountName,
            'enabled' => (int)$enabled,
        ];
        try {
            return $this->insert($data);
        } catch(InvalidQueryException $queryException) {
            throw RFIDException::getRFIDExceptionFromQueryException($queryException);
        }

    }

    protected function getSamAccountNameFromUser($user)
    {
        if ($user instanceof UserDataModel || property_exists($user, 'samAccountName')) {
            $samAccountName = $user->samAccountName;
        } elseif ($user instanceof WPUserModel || property_exists($user, 'user_login')) {
            $samAccountName = $user->user_login;
        } elseif (is_string($user)) {
            $samAccountName = $user;
        }

        if (!isset($samAccountName)) {
            throw new \InvalidArgumentException('Cannot Discern samAccountName from User Object/Data');
        }
        return strtolower($samAccountName);
    }
}
