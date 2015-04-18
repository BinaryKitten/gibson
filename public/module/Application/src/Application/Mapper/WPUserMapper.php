<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 28/03/2015
 * Time: 16:40
 */

namespace Application\Mapper;

use ZfcBase\Mapper\AbstractDbMapper;

class WPUserMapper extends AbstractDbMapper
{
    /**
     * @var string
     */
    protected $tableName = 'wp_users';
}