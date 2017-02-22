<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service;

use Interop\Container\ContainerInterface;
use Zend\Config\Config;

/**
 * Base service class
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class AbstractService
{
    /**
     * Service container
     * @var ContainerInterface 
     */
    private $container;
    
    /**
     * Class initialization
     * 
     * @param ContainerInterface $container the container interface
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    /**
     * Retrieve a service by its name
     * 
     * @param string $name The service name
     * @return mixed
     */
    protected function getService($name)
    {
        return $this->container->get($name);
    }
    
    /**
     * Generate a random id for the specified entity class
     * 
     * @param string $entityClass The entity class
     * @return int|null
     */
    protected function generateRandomId($entityClass)
    {
        $config = $this->getService(Config::class);
        
        // Limits
        $rangeMin    = $config->id_generator->range_min;
        $rangeMax    = $config->id_generator->range_max;
        $attemptsMax = $config->id_generator->attempts_max;
    }
}

/* EOF */