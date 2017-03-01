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
            case Authentication\AuthenticationService::class:
                return new $requestedName(
                    $container->get(User\UserPersistenceService::class),
                    $container->get(User\UserRetrievalService::class),
                    $container->get(User\UserRemoteService::class),
                    $container->get('Config')
                );
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