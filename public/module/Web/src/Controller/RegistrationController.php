<?php

namespace Web\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RegistrationController extends AbstractActionController
{
    public function indexAction()
    {
//        $vm =  new ViewModel();
//        $vm->setTemplate('web/registration/disabled');
//        return $vm;
        /** @var \Web\Form\Registration $form */
        $form = $this->getServiceLocator()->get('form/registration');


//        $prg = [
//            'samAccountName' => 'newUser',
//            'email' => 'newUser@gmail.com',
//            'password' => 'password4LDAP',
//            'phone' => 7521582510,
//            'address_line1' => '47 Grafton Street',
//            'address_town' => 'Manchester',
//            'address_city' => 'Manchester',
//            'address_county' => 'Greater Manchester',
//            'address_postcode' => 'M35 9DP',
//            'emergency_details' => '36546546456564',
//            'passwordCheck' => 'password4LDAP',
//            'address_line2' => 'Failsworth',
//            'medical_information' => '',
//            'submit' => 'Register',
//        ];
//
        $prg = $this->postRedirectGet('register');
        if ($prg instanceof Response) {
            return $prg;
        } else {
            if ($prg) {
                $form->setData($prg);
                if ($form->isValid()) {

                    $data = $form->getData();

                    /** @var \Application\Mapper\UserDataMapper $userDataMapper */
                    $userDataMapper = $this->getServiceLocator()->get('Application\Mapper\UserData');
                    $userDataMapper->createUser($data);

                    /** @var \Application\Mapper\LdapMapper $ldapMapper */
                    $ldapMapper = $this->getServiceLocator()->get('Application\Mapper\Ldap');
                    $ldapMapper->createUser($data, $data['password']);

                }
            }
        }
        return [
            'form' => $form
        ];
    }
}