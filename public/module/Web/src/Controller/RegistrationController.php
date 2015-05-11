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


        $prg = $this->postRedirectGet('login');
        if ($prg instanceof Response) {
            return $prg;
        } else {

        }
        return [
            'form' => $form
        ];
    }
}