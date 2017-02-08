<?php

namespace OxcMP\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        //$this->getEvent()->getApplication()->getServiceManager()->get($id);
        return new ViewModel();
    }
}
