<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Description of ServiceManager
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ServiceManager extends AbstractPlugin {
    /**
     * Retrieve the specified service
     * 
     * @param string $service The service name
     * @return mixed
     */
    public function __invoke($service) {
        return $this->getController()->getEvent()->getApplication()->getServiceManager()->get($service);
    }
}

/* EOF */