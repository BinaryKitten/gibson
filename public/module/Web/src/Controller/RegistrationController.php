<?php

namespace Web\Controller;


use Zend\Mvc\Controller\AbstractActionController;

class RegistrationController extends AbstractActionController
{
    public function indexAction()
    {
//        $vm =  new ViewModel();
//        $vm->setTemplate('web/registration/disabled');
//        return $vm;
        /** @var \Web\Form\Registration $form */
        $form = $this->getServiceLocator()->get('form/registration');

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