<?php

namespace OxcMP\Controller;

use Zend\View\Model\ViewModel;

/**
 * Primary application controller
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class IndexController extends AbstractController
{
    public function indexAction()
    {
        //$this->getEvent()->getApplication()->getServiceManager()->get('ViewHelperManager')->get('headTitle')->append('Main Page');
        return new ViewModel();
    }
}

/* EOF */