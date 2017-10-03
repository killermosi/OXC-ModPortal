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

namespace OxcMP\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Authentication\AuthenticationService;
use Zend\Session\SessionManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\Config\Config;
use OxcMP\Service;
use OxcMP\Controller;
use OxcMP\View;
use OxcMP\Util\Log;

/**
 * Handle creation of various objects for the entire module
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModuleFactory implements FactoryInterface
{
    /**
     * Create the requested object
     * 
     * @param ContainerInterface $container     The service container
     * @param string             $requestedName The service name
     * @param array              $options       Additional options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when creating a service.
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        try {
            return $this->createObject($container, $requestedName, $options);
        } catch (ServiceNotFoundException $exc) {
            // Nothing to handle here
            throw $exc;
        } catch (\Exception $exc) {
            Log::error('Factory error while attempting to create object "',$requestedName, '": ', $exc->getMessage());
            throw new ServiceNotCreatedException('Failed to create object', $exc->getCode(), $exc);
        }
    }
    
    /**
     * Create the requested object
     * 
     * @param ContainerInterface $container     The service container
     * @param string             $requestedName The service name
     * @param array              $options       Additional options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws \Exception if an error occurs
     */
    private function createObject(ContainerInterface $container, $requestedName, array $options = null)
    {
        switch ($requestedName) {
            /*******************/
            /**  CONTROLLERS  **/
            /*******************/
            
            case Controller\IndexController::class:
                return new $requestedName();
            case Controller\ModController::class:
                return new $requestedName(
                    $container->get(AuthenticationService::class),
                    $container->get(Service\Mod\ModRetrievalService::class),
                    $container->get(Service\Mod\ModPersistenceService::class)
                );
            case Controller\UserController::class:
                return new $requestedName(
                    $container->get(AuthenticationService::class),
                    $container->get(Service\User\UserRetrievalService::class),
                    $container->get(SessionManager::class),
                    $container->get(Config::class)
                );
            
            /****************/
            /**  SERVICES  **/
            /****************/
                
            // Local services
            case Service\Acl\AclService::class:
                return new $requestedName(
                    $container->get(Config::class)
                );
            case Service\Authentication\AuthenticationAdapter::class:
                return new $requestedName(
                    $container->get(Service\User\UserPersistenceService::class),
                    $container->get(Service\User\UserRetrievalService::class),
                    $container->get(Service\User\UserRemoteService::class),
                    $container->get(Config::class)
                );
            case Service\Mod\ModRetrievalService::class:
                return new $requestedName(
                    $container->get('doctrine.entitymanager.orm_default')
                );
            case Service\Mod\ModPersistenceService::class:
                return new $requestedName(
                    $container->get('doctrine.entitymanager.orm_default')
                );
            case Service\Module\ModuleService::class:
                return new $requestedName();
            case Service\User\UserPersistenceService::class:
                return new $requestedName(
                    $container->get('doctrine.entitymanager.orm_default'),
                    $container->get(Service\User\UserRemoteService::class)
                );
            case Service\User\UserRetrievalService::class:
                return new $requestedName(
                    $container->get('doctrine.entitymanager.orm_default')
                );
            case Service\User\UserRemoteService::class:
                return new $requestedName(
                    $container->get(Config::class)
                );

            // External services
            case Config::class:
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
                    $container->get(Service\Authentication\AuthenticationAdapter::class)
                );
            /********************/
            /**  VIEW HELPERS  **/
            /********************/
            
            case View\Helper\StaticUrl::class:
                return new $requestedName(
                    $container->get(Config::class)
                );
                
            /***************/
            /**  WHOOPS!  **/
            /***************/
            default:
                Log::notice('No service definition for "', $requestedName, '"');
                throw new ServiceNotFoundException();
        }
    }
}

/* EOF */