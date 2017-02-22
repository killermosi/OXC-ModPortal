<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service\User;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * Create the module configuration instance
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class UserFactory implements FactoryInterface {
    /**
     * Create user persistence and retrieval services
     * 
     * @param ContainerInterface $container     Container
     * @param string             $requestedName Requested name
     * @param array              $options       Additional options
     * @return ConfigService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        return new $requestedName($container->get('doctrine.entitymanager.orm_default'));
    }
}

/* EOF */