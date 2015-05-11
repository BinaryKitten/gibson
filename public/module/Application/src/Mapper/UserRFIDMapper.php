<?php

namespace Web\Mapper;

use Web\Exception\RFIDException;
use Web\Model\UserData as UserDataModel;
use Web\Model\WPUser as WPUserModel;
use Zend\Db\Adapter\Exception\InvalidQueryException;
use ZfcBase\Mapper\AbstractDbMapper;

class UserRFIDMapper extends AbstractDbMapper
{
    /**
     * @var string
     */
    protected $tableName = 'gibson_user_rfid';

    public function addRFIDtoUser($user, $rfid, $description = '')
    {
       $samAccountName = $this->getSamAccountNameFromUser($user);

        $data = [
            'rfidCode' => $rfid,
            'description' => $description,
            'samAccountName' => $samAccountName
        ];
        try {
            $result = $this->insert($data);
        } catch(InvalidQueryException $queryException) {
            throw RFIDException::getRFIDExceptionFromQueryException($queryException);
        }
        \Zend\Debug\Debug::dump($result);
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
        return $samAccountName;
    }
}
