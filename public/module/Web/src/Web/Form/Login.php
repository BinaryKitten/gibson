<?php


namespace Web\Form;

use Zend\Form\Form as ZendForm;
use Zend\Form\Element\Text as TextElement;
use Zend\Form\Element\Password as PasswordElement;
use Zend\Form\Element\Submit as SubmitElement;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class Login extends ZendForm
{

    public function __construct()
    {
        parent::__construct('UserLogin');

        $username = new TextElement('username');
        $username->setAttribute('placeholder', 'Username');

        $password = new PasswordElement('password');
        $password->setAttribute('placeholder', 'Password');

        $submit = new SubmitElement('submit', array(
            'label' => 'Login'
        ));


        $this->add($username)->add($password)->add($submit);
    }

    public function getInputFilter()
    {
        $inputFilter = new InputFilter();
        $username = new Input();
        $username->setName('username')->setRequired(true);
        $username->setErrorMessage('A Username is required to Login');
        $inputFilter->add($username);

        $password = new Input();
        $password->setName('password')->setRequired(true);
        $password->setErrorMessage('A Password is required to Login');
        $inputFilter->add($password);

        return $inputFilter;

    }


} 