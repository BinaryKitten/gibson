<?php

namespace Web\Form;

use Zend\Form\Element;
use Zend\Form\Form as ZendForm;
use Zend\Form\Element\Text as TextElement;
use Zend\Form\Element\Password as PasswordElement;
use Zend\Form\Element\Submit as SubmitElement;

class Registration extends ZendForm
{
    public function __construct()
    {
        parent::__construct('UserRegistration');

        $elements = [
            'username' => 'Username',
            'email' => new Element\Email('email', ['label' => 'Email Address']),
            'password' => new PasswordElement('password', ['label' => 'Password']),
            'passwordCheck' => new PasswordElement('passwordCheck', ['label' => 'Password (again)']),

            new Element\Textarea('emergency_details', ['label' => 'Emergency Contact Details']),
            new Element\Textarea('medical_information', ['label' => 'Emergency Contact Details']),

            'phone' => 'Phone Number',
            'address_line1' => 'Address Line 1',
            'address_line2' => 'Address Line 2',
            'address_town' => 'Town',
            'address_city' => 'City',
            'address_county' => 'County',
            'address_postcode' => 'Post Code',

            new SubmitElement('submit', ['label' => 'Register'])
        ];

        foreach($elements as $name => $elementOfLabel) {
            if (!($elementOfLabel instanceof Element)) {
                $elementOfLabel = new TextElement($name, ['label' => $elementOfLabel]);
            }
            $this->add($elementOfLabel);
        }


    }
}
