<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 18/04/2015
 * Time: 17:25
 */

namespace Application\Exception;


use Zend\Db\Adapter\Exception\InvalidQueryException;

class RFIDException extends \Exception
{
    const RFID_EXISTS_FOR_USER = 1;
    const RFID_EXISTS_FOR_OTHER_USER = 2;

    const MSG_RFID_EXISTS_FOR_USER = 'The RFID already Exists for this user';
    const MSG_RFID_EXISTS_FOR_OTHER_USER = 'The RFID already Exists for a user';

    public static function getRFIDExceptionFromQueryException(InvalidQueryException $queryException)
    {
        $exception = $queryException->getPrevious();
        switch ($exception->getCode() . '-' . $exception->errorInfo[1]) {
            case '23000-1062':
                if (strpos($queryException->getMessage(), "for key 'PRIMARY'") !== false) {
                    $newCode = RFIDException::RFID_EXISTS_FOR_USER;
                    $newMsg = RFIDException::MSG_RFID_EXISTS_FOR_USER;
                } elseif (strpos($queryException->getMessage(), "for key 'no_rfid_same_across_users'") !== false) {
                    $newCode = RFIDException::RFID_EXISTS_FOR_OTHER_USER;
                    $newMsg = RFIDException::MSG_RFID_EXISTS_FOR_OTHER_USER;
                }
                return new RFIDException(
                    $newMsg,
                    $newCode,
                    $queryException
                );
                break;

            default:
                return $queryException;
                break;
        }
    }
}