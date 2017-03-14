<?php

/*
 * Copyright © 2016-2017 OpenXcom Mod Portal Contributors
 *
 * This file is part of OpenXcom Mod Portal.
 *
 * OpenXcom Mod Portal is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OpenXcom Mod Portal is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OpenXcom Mod Portal. If not, see <http://www.gnu.org/licenses/>.
 */

namespace OxcMP\Service;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Authentication\AuthenticationService;
use Zend\Session\SessionManager;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\Config\Config;
use OxcMP\Util\Log;

/**
 * Handles local service creation
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ServiceFactory implements FactoryInterface
{
    /**
     * Create the requested service
     * 
     * @param ContainerInterface $container     The service container
     * @param string             $requestedName The service name
     * @param array              $options       Additional options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        try {
            switch ($requestedName) {
                
                // Local services
                case Acl\AclService::class:
                    return new $requestedName(
                        $container->get(Config::class)
                    );
                case Authentication\AuthenticationAdapter::class:
                    return new $requestedName(
                        $container->get(User\UserPersistenceService::class),
                        $container->get(User\UserRetrievalService::class),
                        $container->get(User\UserRemoteService::class),
                        $container->get(Config::class)
                    );
                case Mod\ModRetrievalService::class:
                    return new $requestedName(
                        $container->get('doctrine.entitymanager.orm_default')
                    );
                case User\UserPersistenceService::class:
                    return new $requestedName(
                        $container->get('doctrine.entitymanager.orm_default'),
                        $container->get(User\UserRemoteService::class)
                    );
                case User\UserRetrievalService::class:
                    return new $requestedName(
                        $container->get('doctrine.entitymanager.orm_default')
                    );
                case User\UserRemoteService::class:
                    return new $requestedName(
                        $container->get(Config::class)
                    );
                    
                // External services
                case \Zend\Config\Config::class:
                    return new $requestedName(
                        $container->get('Config')
                    );
                case AuthenticationService::class:
                    return new $requestedName(
                        new SessionStorage(
                            'Zend_Auth',
                            'session',
                            $container->get(SessionManager::class)
                        ),
                        $container->get(Authentication\AuthenticationAdapter::class)
                    );
            }
        } catch (\Exception $exc) {
            Log::notice('Failed to create service ', $requestedName, ': ', $exc->getMessage());
            throw new ServiceNotCreatedException();
        }
        
        // If no service was created thus far, it means that it is not supported
        throw new ServiceNotFoundException('Unsupported service class : ' . $requestedName);
    }
}

/* EOF */