<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 29/03/2015
 * Time: 23:55
 */

namespace Web\Form;

use Zend\Form\Form as ZendForm;
use Zend\Form\Element\Text as TextElement;
use Zend\Form\Element\Password as PasswordElement;
use Zend\Form\Element\Submit as SubmitElement;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex as RegexValidator;

class LdapMigrate extends ZendForm
{
    public function __construct()
    {
        parent::__construct('LdapMigrate');

        $password = new PasswordElement('password');
        $password->setAttribute('placeholder', 'Password');

        $password2 = new PasswordElement('passwordCheck');
        $password2->setAttribute('placeholder', 'Password Again');

        $submit = new SubmitElement('submit', array(
            'label' => 'Migrate Login'
        ));

        $this
            ->add($password)
            ->add($password2)
            ->add($submit)
        ;
    }

    public function getInputFilter()
    {
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
                ->attach($ldapPasswordComplexity)
        ;

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
                ->attach($Identical)
        ;

        $inputFilter = new InputFilter();
        return $inputFilter
            ->add($password)
            ->add($passwordCheck);
    }

}