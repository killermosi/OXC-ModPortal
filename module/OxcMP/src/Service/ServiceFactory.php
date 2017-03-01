<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * Handles local service creation
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ServiceFactory implements FactoryInterface
{
    /**
     * Create user persistence and retrieval services
     * 
     * @param ContainerInterface $container     The service container
     * @param string             $requestedName The service name
     * @param array              $options       Additional options
     * @return ConfigService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        switch ($requestedName) {
            case Config\ConfigService::class:
                return new $requestedName($container->get('Config'));
            case User\UserPersistenceService::class:
            case User\UserRetrievalService::class:
                return new $requestedName($container->get('doctrine.entitymanager.orm_default'));
            case User\UserRemoteService::class:
                return new $requestedName($container->get(ConfigService::class));
            default:
                throw new \Exception('Unsupported service class : ' . $requestedName);
        }
    }
}

/* EOF */