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
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex as RegexValidator;

class Registration extends InputFilter
{
    function __construct()
    {
        $username = new Input();
        $username->setName('username')->setRequired(true);
        $username->setErrorMessage('A Username is required to Login');

        $password = new Input();
        $passwordCheck = new Input();

        $notEmptyValidator1 = new NotEmpty();
        $notEmptyValidator1->setMessages([
            NotEmpty::INVALID => 'Please enter a password',
            NotEmpty::IS_EMPTY => 'Please enter q password',
        ]);

        $pattern = '/(?=^.{8,255}$)(?:(?=.*\d)(?=.*[A-Z])(?=.*[a-z])|(?=.*\d)(?=.*[^A-Za-z0-9])(?=.*[a-z])|(?=.*[^A-Za-z0-9])(?=.*[A-Z])(?=.*[a-z])|(?=.*\d)(?=.*[A-Z])(?=.*[!@#\$%\^&\*\(\)_\+=]))^.*/';
        $ldapPasswordComplexity = new RegexValidator($pattern);
        $ldapPasswordComplexity->setMessages([
            RegexValidator::NOT_MATCH => 'Your Password does not meet required complexity',
            RegexValidator::INVALID => 'Your Password does not meet required complexity',
            RegexValidator::ERROROUS => 'Your Password does not meet required complexity',
        ]);

        $password
            ->setName('password')
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($notEmptyValidator1)
            ->attach($ldapPasswordComplexity);

        $passwordCheck->setName('passwordCheck')->setRequired(true);

        $Identical = new Identical('password');

        $notEmptyValidator = new NotEmpty();
        $notEmptyValidator->setMessages([
            NotEmpty::INVALID => 'Please re-enter the password',
            NotEmpty::IS_EMPTY => 'Please re-enter the password',
        ]);

        $passwordCheck
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($notEmptyValidator, true)
            ->attach($Identical);

        $inputs = [
            $username,
            $password,
            $passwordCheck
        ];

        foreach ($inputs as $input) {
            $this->add($input);
        }
    }

}