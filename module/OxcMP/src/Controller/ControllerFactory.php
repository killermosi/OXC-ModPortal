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

namespace OxcMP\Controller;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use OxcMP\Service;
use OxcMP\Util\Log;

/**
 * Controller factory
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ControllerFactory implements FactoryInterface
{
    /**
     * Create an object
     *
     * @param  ContainerInterface $container     The service container
     * @param  string             $requestedName The service name
     * @param  null|array         $options       Additional options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        try {
            switch ($requestedName) {
                case IndexController::class:
                    return new $requestedName();
                case UserController::class:
                    return new $requestedName(
                        $container->get(Service\Authentication\AuthenticationService::class)
                    );
            }
        } catch (\Exception $exc) {
            Log::notice('Failed to create controller ', $requestedName, ': ', $exc->getMessage());
            throw new ServiceNotCreatedException();
        }
        
        // If no service was created thus far, it means that it is not supported
        throw new ServiceNotFoundException('Unsupported controller class : ' . $requestedName);
    }

}

/* EOF */