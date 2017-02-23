<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service\Config;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * Create the module configuration instance
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ConfigFactory implements FactoryInterface {
    /**
     * Create the configuration instance
     * 
     * @param ContainerInterface $container     Container
     * @param string             $requestedName Requested name
     * @param array              $options       Additional options
     * @return ConfigService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        return new ConfigService($container->get('Config'));
    }
}

/* EOF */