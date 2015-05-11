<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 10/05/2015
 * Time: 14:09
 */

namespace Web\InputFilter;

use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class Registration extends InputFilter
{
    function __construct()
    {
        $username = new Input();
        $username->setName('username')->setRequired(true);
        $username->setErrorMessage('A Username is required to Login');

        $this->add($username);
    }

}