<?php

namespace Application\Mapper;

use Application\Model\WPUser;
use Zend\Db\ResultSet\HydratingResultSet;
use ZfcBase\Mapper\AbstractDbMapper;

class WPUserMapper extends AbstractDbMapper
{
    /**
     * @var string
     */
    protected $tableName = 'wp_users';


    /**
     * @param $ID
     * @return WPUser|bool|HydratingResultSet
     */
    public function getUser($ID)
    {
        return $this->getUserBy('ID', $ID);
    }


    /**
     * @param $field
     * @param $value
     * @return bool|WPUser|HydratingResultSet
     */
    public function getUserBy($field, $value)
    {
        $where = [];
        $where[$field] = $value;
        $s = $this->getSelect()->columns(['*'])->where($where);
        $result =  $this->select($s);
        if ($result->count() == 0) {
            return false;
        } elseif ($result->count() == 1) {
            return $result->current();
        } else {
            return $result;
        }
    }
}