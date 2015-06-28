<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 10/05/2015
 * Time: 14:09
 */

namespace Web\InputFilter;

use Zend\I18n\Validator\PhoneNumber;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\Db\NoRecordExists as NoRecordExistsValidator;
use Zend\Validator\EmailAddress;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex as RegexValidator;
use Zend\Db\Adapter\Adapter as DbAdapter;
use Zend\Validator\StringLength as StringLengthValidator;


class Registration extends InputFilter
{
    function __construct(DbAdapter $dbAdapter)
    {
        $inputs = [];
        $fields = [
            'samAccountName' => 'Username',
            'nickname' => 'Nickname',
            'email' => 'Email Address',
            'password' => 'Password',
            'phone' => 'Phone Number',
            'address' => 'Address',
            'emergency_details' => 'Emergency Contact Details',
        ];


        foreach($fields as $field => $label) {
            $notEmptyValidator = new NotEmpty();
            $notEmptyValidator->setMessage('Please fill in your ' . $label);

            $input = new Input();
            $input->setName($field)->getValidatorChain()->attach($notEmptyValidator);
            $inputs[$field] = $input;
        }

        $notExists = new NoRecordExistsValidator([
            'table' => 'gibson_user_data',
            'field' => 'samAccountName',
            'adapter' => $dbAdapter,
        ]);
        $notExists->setMessages([NoRecordExistsValidator::ERROR_RECORD_FOUND => 'Username already Exists']);
        $inputs['samAccountName']->getValidatorChain()->attach($notExists);

        $pattern = '/(?=^.{8,255}$)(?:(?=.*\d)(?=.*[A-Z])(?=.*[a-z])|(?=.*\d)(?=.*[^A-Za-z0-9])(?=.*[a-z])|(?=.*[^A-Za-z0-9])(?=.*[A-Z])(?=.*[a-z])|(?=.*\d)(?=.*[A-Z])(?=.*[!@#\$%\^&\*\(\)_\+=]))^.*/';
        $ldapPasswordComplexity = new RegexValidator($pattern);
        $ldapPasswordComplexity->setMessages([
            RegexValidator::NOT_MATCH => 'Your Password does not meet required complexity',
            RegexValidator::INVALID => 'Your Password does not meet required complexity',
            RegexValidator::ERROROUS => 'Your Password does not meet required complexity',
        ]);

        $minLength = new StringLengthValidator(['min' => 7]);
        $minLength->setMessages([
            StringLengthValidator::TOO_SHORT => 'Your Password is too short, Please enter a password at least 7 characters in length'
        ]);

        $inputs['password']
            ->getValidatorChain()
            ->attach($ldapPasswordComplexity);

        $passwordCheck = new Input();
        $passwordCheck->setName('passwordCheck')->setRequired(true);
        $inputs['passwordCheck'] = $passwordCheck;

        $Identical = new Identical('password');

        $notEmptyValidator = new NotEmpty();
        $notEmptyValidator->setMessages([
            NotEmpty::INVALID => 'Please re-enter your password',
            NotEmpty::IS_EMPTY => 'Please re-enter your password',
        ]);

        $passwordCheck
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($notEmptyValidator, true)
            ->attach($Identical);



        $emailValidator = new EmailAddress();
        $messages = array_fill_keys(array_keys($emailValidator->getMessageTemplates()), 'Please enter a valid Email Address');
//        $messages['regexNotMatch'] = 'Please Enter a bbb Email Address';
        $emailValidator->setMessages($messages);

        $hostnameValidator = $emailValidator->getHostnameValidator();
        $messages = array_fill_keys(array_keys($hostnameValidator->getMessageTemplates()), 'Please enter a valid Email Address');
        $hostnameValidator->setMessages($messages);

        $inputs['email']
            ->getValidatorChain()
                ->attach($emailValidator)
        ;

        $phoneNumberValidator = new PhoneNumber();
        $phoneNumberValidator
            ->setCountry('GB')
//            ->allowedTypes(['personal', 'mobile'])
            ->setMessages([
                PhoneNumber::INVALID => 'Please enter a valid phone number',
                PhoneNumber::NO_MATCH => 'Please enter a matching phone number'
            ]);

        $inputs['phone']
            ->getValidatorChain()
            ->attach($phoneNumberValidator);

        $i = new Input();
        $inputs['phone']->getFilterChain()->attach(new \Zend\Filter\ToInt());

//        $pcodeValidator = new PostCode();
//        $pcodeValidator->setLocale('en_GB');
//        $inputs['address_postcode']->getValidatorChain()->attach($pcodeValidator);



        /** @var Input $input */
        foreach ($inputs as $input) {
            $this->add($input);
        }
    }

}