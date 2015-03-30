<?php

namespace Application\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class RegistrationController extends AbstractActionController
{
    public function indexAction()
    {
        $vm =  new ViewModel();
        $vm->setTemplate('application/registration/disabled');
        return $vm;
    }
}