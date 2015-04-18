<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 28/03/2015
 * Time: 16:40
 */

namespace Application\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;

class WPUserMeta extends  AbstractDbMapper
{
    /**
     * @var string
     */
    protected $tableName = 'wp_usermeta';


    /**
     * @param $user
     * @return \Zend\Db\ResultSet\HydratingResultSet
     */
    public function getMetaForUser($user, $meta_key = null)
    {
        if ($user instanceof \stdClass) {
            if (!isset($user->ID) || empty($user->ID)) {
                throw new \InvalidArgumentException('User does not have an ID');
            } else {
                $id = $user->ID;
            }
        } elseif(is_array($user)) {
            if (!isset($user['ID']) || empty($user['ID'])) {
                throw new \InvalidArgumentException('User does not have an ID');
            } else {
                $id = $user['ID'];
            }
        } elseif (is_numeric($user)) {
            $id = $user;
        } else {
            throw new \InvalidArgumentException('User does not have an ID');
        }

        $where = ['user_id' => $id];
        if (isset($meta_key)) {
            $where['meta_key'] = $meta_key;
        }

        $results = $this->select($this->getSelect()->where($where));
        if ($results->count() == 1) {
            return $results->current();
        } else {
            return $results;
        }
    }


}