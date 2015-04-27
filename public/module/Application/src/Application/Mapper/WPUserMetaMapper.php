<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 28/03/2015
 * Time: 16:40
 */

namespace Application\Mapper;

use Application\Model\WPUser;
use Zend\Db\ResultSet\HydratingResultSet;
use ZfcBase\Mapper\AbstractDbMapper;

class WPUserMetaMapper extends AbstractDbMapper
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
        if ($user instanceof \stdClass || $user instanceof WPUser) {
            if (!isset($user->ID) || empty($user->ID)) {
                throw new \InvalidArgumentException('User does not have an ID');
            } else {
                $id = $user->ID;
            }
        } elseif (is_array($user)) {
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

    public function metaArray(HydratingResultSet $metaData)
    {
        $data = array();
        $preData = $metaData->toArray();
        foreach($preData as $item) {
            $value = $this->maybe_unserialize($item['meta_value']);
            $key = $item['meta_key'];
            if (array_key_exists($key, $data)) {
                $data[$key] = (array)$data[$key];
                $data[$key][] = $value;
            } else {
                $data[$key] = $value;
            }
        }
        return $data;
    }



    /**
     * Unserialize value only if it was serialized.
     *
     * @since 2.0.0
     *
     * @param string $original Maybe unserialized original, if is needed.
     * @return mixed Unserialized data can be any type.
     */
    function maybe_unserialize($original)
    {
        if ($this->is_serialized($original)) {
            // don't attempt to unserialize data that wasn't serialized going in
            return @unserialize($original);
        }
        return $original;
    }

    /**
     * Check value to find if it was serialized.
     *
     * If $data is not an string, then returned value will always be false.
     * Serialized data is always a string.
     *
     * @since 2.0.5
     *
     * @param string $data Value to check to see if was serialized.
     * @param bool $strict Optional. Whether to be strict about the end of the string. Default true.
     * @return bool False if not serialized and true if it was.
     */
    function is_serialized($data, $strict = true)
    {
        // if it isn't a string, it isn't serialized.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if ('N;' == $data) {
            return true;
        }
        if (strlen($data) < 4) {
            return false;
        }
        if (':' !== $data[1]) {
            return false;
        }
        if ($strict) {
            $lastc = substr($data, -1);
            if (';' !== $lastc && '}' !== $lastc) {
                return false;
            }
        } else {
            $semicolon = strpos($data, ';');
            $brace = strpos($data, '}');
            // Either ; or } must exist.
            if (false === $semicolon && false === $brace)
                return false;
            // But neither must be in the first X characters.
            if (false !== $semicolon && $semicolon < 3)
                return false;
            if (false !== $brace && $brace < 4)
                return false;
        }
        $token = $data[0];
        switch ($token) {
            case 's' :
                if ($strict) {
                    if ('"' !== substr($data, -2, 1)) {
                        return false;
                    }
                } elseif (false === strpos($data, '"')) {
                    return false;
                }
            // or else fall through
            case 'a' :
            case 'O' :
                return (bool)preg_match("/^{$token}:[0-9]+:/s", $data);
            case 'b' :
            case 'i' :
            case 'd' :
                $end = $strict ? '$' : '';
                return (bool)preg_match("/^{$token}:[0-9.E-]+;$end/", $data);
        }
        return false;
    }

    /**
     * Check whether serialized data is of string type.
     *
     * @since 2.0.5
     *
     * @param string $data Serialized data.
     * @return bool False if not a serialized string, true if it is.
     */
    function is_serialized_string($data)
    {
        // if it isn't a string, it isn't a serialized string.
        if (!is_string($data)) {
            return false;
        }
        $data = trim($data);
        if (strlen($data) < 4) {
            return false;
        } elseif (':' !== $data[1]) {
            return false;
        } elseif (';' !== substr($data, -1)) {
            return false;
        } elseif ($data[0] !== 's') {
            return false;
        } elseif ('"' !== substr($data, -2, 1)) {
            return false;
        } else {
            return true;
        }
    }

}