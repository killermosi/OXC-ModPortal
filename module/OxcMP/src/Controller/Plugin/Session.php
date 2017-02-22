<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Session\Container;
use OxcMP\Controller\AbstractController;

/**
 * Description of Session
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class Session extends AbstractPlugin
{
    /**
     * Retrieve the specified session
     * 
     * @return Container
     */
    public function __invoke() {
        return $this->getController()->getEvent()->getApplication()->getServiceManager()->get(
            AbstractController::SESSION_NAMESPACE
        );
    }
}

/* EOF */