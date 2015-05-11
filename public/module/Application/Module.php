<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 28/04/2015
 * Time: 17:01
 */

namespace Api;


class Module
{
    /**
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}
