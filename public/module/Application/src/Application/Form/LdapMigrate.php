<?php
/**
 * Created by PhpStorm.
 * User: Kat
 * Date: 29/03/2015
 * Time: 23:55
 */

namespace Application\Form;

use Zend\Form\Form as ZendForm;
use Zend\Form\Element\Text as TextElement;
use Zend\Form\Element\Password as PasswordElement;
use Zend\Form\Element\Submit as SubmitElement;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class LdapMigrate extends ZendForm
{
    public function __construct()
    {
        parent::__construct('LdapMigrate');

        $password = new PasswordElement('password');
        $password->setAttribute('placeholder', 'Password');

        $password2 = new PasswordElement('password2');
        $password2->setAttribute('placeholder', 'Password Repeat');

        $submit = new SubmitElement('submit', array(
            'label' => 'Migrate Login'
        ));

//
//        $this
//            ->add($password)
//            ->add($password2)
//            ->add($submit)
//        ;

        $this->add([
            'name' => 'password', // add first password field
            'attributes' => [
                'placeholder' => 'Password'
            ],
            'required' => true
        ]);
        $this->add([
            'name' => 'passwordCheck', // add second password field
            /* ... other params ... */
            'validators' => [
                [
                    'name' => 'Identical',
                    'options' => [
                        'token' => 'password', // name of first password field
                    ],
                ],
            ],
        ]);
    }

}