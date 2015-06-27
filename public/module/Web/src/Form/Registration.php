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
            'samAccountName' => 'Username',
            'email' => new Element\Text('email', ['label' => 'Email Address']),
            'password' => new PasswordElement('password', ['label' => 'Password']),
            'passwordCheck' => new PasswordElement('passwordCheck', ['label' => 'Password (again)']),

            'phone' => 'Phone Number',
            'nickname' => 'Nickname',
            new Element\Textarea('address', ['label' => 'Address', 'attributes' => ['rows' => 3]]),

            new Element\Textarea('medical_information', ['label' => 'Medical Details (optional)', 'attributes' => ['rows' => 3]]),
            new Element\Textarea('emergency_details', ['label' => 'Emergency Contact Details', 'attributes' => ['rows' => 3]]),

            'submit' => new SubmitElement('submit')
        ];

        $elements['submit']->setValue('Register');

        foreach($elements as $name => $elementOfLabel) {
            if (!($elementOfLabel instanceof Element) && !is_array($elementOfLabel)) {
                $elementOfLabel = new TextElement($name, ['label' => $elementOfLabel]);
            }
            $this->add($elementOfLabel);
        }


    }
}
