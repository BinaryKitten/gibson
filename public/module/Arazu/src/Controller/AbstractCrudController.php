<?php

namespace Arazu\Controller;

use Arazu\Mapper\AbstractDbMapper;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Helper\ViewModel;

class AbstractCrudController  extends AbstractActionController
{

    /**
     * @var  AbstractDbMapper $mapper
     * @access protected
     */
    protected $mapper;

    /**
     * Set the class scope db mapper object
     * @param AbstractDbMapper $mapper
     * @access public
     * @return $this
     */
    public function setMapper(AbstractDbMapper $mapper)
    {
        $this->mapper = $mapper;
        return $this;
    }

    /**
     * Get the class scope db mapper object
     * @access public
     * @return AbstractDbMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }

    /**
     * List all elements from the mapper object
     * @access public
     * @return array
     */
    public function listAction()
    {

        return array(
            'items' => $this->getMapper()->findAll()
        );
    }

    /**
     * View a specific item from the mapper object
     * @access public
     * @return array
     */
    public function viewAction()
    {
//        return array();
        $item = $this->getItem();
        if($item === false) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return array(
            'item' => $item
        );
    }

    /**
     * Edit an item within the mapper object
     * @access public
     * @return array
     */
    public function editAction()
    {
        $item = $this->getItem();
        if($item === false) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        /** @var \Zend\Form\Form $form */
        $form = $this->getServiceLocator()->get('thingyform'); // name to be dealt with here
        $form->setObject($item);

        return array(
            'item' => $item,
            'form' => $form
        );
    }

    /**
     * Get the id of the requested item via the route, else via the query string
     * @access protected
     * @return mixed|\Zend\Mvc\Controller\Plugin\Params
     */
    protected function getId()
    {
        $id = (integer) $this->params('id');

        if($id === null) {
            $id = $this->params()->fromQuery('id');
        }

        return $id;
    }

    /**
     * Get a specific item via referencing a passed item id
     * @access protected
     * @return bool|object
     */
    protected function getItem()
    {
        $id = $this->getId();
        if($id === null || $id < 1) {
            return false;
        }

        $item = $this->getMapper()->findById($id);
        if ($item === null) {
            return false;
        }

        return $item;
    }

}
