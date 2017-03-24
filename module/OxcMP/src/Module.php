<?php

/*
 * Copyright © 2016-2017 OpenXcom Mod Portal Developers
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

namespace OxcMP;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\Config\Config;
use OxcMP\Entity\User;
use OxcMP\Service\Acl\AclService;
use OxcMP\Service\Acl\Role;
use OxcMP\Service\User\UserPersistenceService;
use OxcMP\Service\User\UserRemoteService;
use OxcMP\Service\User\UserRetrievalService;
use OxcMP\Service\User\Exception\JsonRpc as JsonRpcException;
use OxcMP\Util\Config as ConfigUtil;
use OxcMP\Util\Log;

class Module
{
    const VERSION = '1.0.0';
    
    /**
     * Public-to-private configuration mapping
     * @var array 
     */
    private $configMap = [
        // API
        'api.url'            => 'oxcForumApi.url',
        'api.key'            => 'oxcForumApi.key',
        'api.basicAuth.user' => 'oxcForumApi.basicAuth.user',
        'api.basicAuth.pass' => 'oxcForumApi.basicAuth.pass',
        // Database
        'db.host' => 'doctrine.connection.orm_default.params.host',
        'db.port' => 'doctrine.connection.orm_default.params.port',
        'db.user' => 'doctrine.connection.orm_default.params.user',
        'db.pass' => 'doctrine.connection.orm_default.params.password',
        'db.name' => 'doctrine.connection.orm_default.params.dbname',
        // Session
        'session.cookie.path' => 'session_config.cookie_path',
        // Static storage
        'static.storage' => 'layout.staticStorageUrl',
        // Logging
        'log.enabled' => 'log.enabled',
        'log.file'    => 'log.stream',
        'log.level'   => 'log.priority',
        // oAuth parameters
        'oauth.url' => 'layout.oAuthUrl'
    ];
    
    /**
     * Module config
     * 
     * @return string
     */
    public function getConfig()
    {
        // Use a split configuration model - to make it easier to distinguish between
        // configuration values that are intended to be user-modified and those that are not
        return ConfigUtil::buildConfig(
            __DIR__ . '/../config/module.config.php', 
            __DIR__ . '/../config/module.config.ini',
            $this->configMap
        );
    }
    
    /**
     * Module bootstrap
     * 
     * @param MvcEvent $event The event
     * @return void
     */
    public function onBootstrap(MvcEvent $event)
    {
        // Service manager
        $serviceManager = $event->getApplication()->getServiceManager();
        $config = $serviceManager->get(Config::class);
        
        // Init the log
        Log::init($config);
        
        Log::info('Application starting, bootstrapping...');
        
        // Config and default backgroud image
        $event->getViewModel()->config = $config;
        $event->getViewModel()->defaultBackground = $config->layout->defaultBackground;
        
        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one.
        $serviceManager->get(SessionManager::class);
        
        // Check ACL
        $event->getApplication()
            ->getEventManager()
            ->getSharedManager()
            ->attach(
                AbstractActionController::class,
                MvcEvent::EVENT_DISPATCH,
                [$this, 'onDispatch'],
                100
            );
        
        Log::debug('Bootstrapping complete');
    }
    
    /**
     * Actions to execute on dispatch
     * 
     * @param MvcEvent $event The event
     * @return void
     */
    public function onDispatch(MvcEvent $event)
    {
        Log::info('Executing EVENT_DISPATCH actions');
        
        // Update authenticated user, stop on error
        if (false === $this->checkAndUpdateAuthenticatedUser($event)) {
            Log::debug('EVENT_DISPATCH result: redirect to "home"');
            return $event->getTarget()->redirect()->toRoute('home');
        }

        // Services
        $serviceManager = $event->getApplication()->getServiceManager();
        /* @var $authenticationService AuthenticationService */
        $authenticationService = $serviceManager->get(AuthenticationService::class);

        // Retrieve the authenticated user
        if ($authenticationService->hasIdentity()) {
            $authenticatedUser = $serviceManager->get(UserRetrievalService::class)->findById(
                $authenticationService->getIdentity()
            );
        } else {
            $authenticatedUser = null;
        }

        // Set the authenticated user view value
        $event->getViewModel()->authenticatedUser = $authenticatedUser;
        
        /* Check ACL */

        // Route
        $route = $event->getRouteMatch()->getMatchedRouteName();

        // User role
        $userRole = Role::GUEST;
        
        if ($authenticatedUser instanceof User) {
            $userRole = $authenticatedUser->getIsAdministrator() ? Role::ADMINISTRATOR : Role::MEMBER;
        }
        
        // Send the user to the home page if it is not allowed to access the page
        if (!$serviceManager->get(AclService::class)->isAclAllowed($route, $userRole)) {
            $errorKey = (false == $authenticationService->hasIdentity())
                ? 'acl_not_logged_in'
                : 'acl_not_allowed';
            
            $errorMessage = $event->getTarget()->translate($errorKey);
            $event->getTarget()->flashMessenger()->addErrorMessage($errorMessage);
            
            Log::debug('EVENT_DISPATCH result: redirect to "home"');
            return $event->getTarget()->redirect()->toRoute('home');
        }
        
        Log::debug('EVENT_DISPATCH actions handled');
    }
    
    /**
     * Check the authentication token and update the authenticated user - if any
     * 
     * @param MvcEvent $event The event
     * @return boolean True if check and/or update went OK, false otherwise
     */
    private function checkAndUpdateAuthenticatedUser(MvcEvent $event)
    {
        Log::info('Checking authenticated user, updating if necessary');
        
        // Services
        $serviceManager = $event->getApplication()->getServiceManager();
        
        /* @var $authenticationService AuthenticationService */
        $authenticationService = $serviceManager->get(AuthenticationService::class);
        
        if (!$authenticationService->hasIdentity()) {
            Log::debug('No authenticated user found, nothing to update');
            return true;
        }
        
        // Retrieve the user from the database - as it may have been updated in another session
        $user = $serviceManager->get(UserRetrievalService::class)->findById(
            $authenticationService->getIdentity()
        );

        // Sanity check
        if (!$user instanceof User) {
            Log::notice('The current authenticated user was not found in the database, revoking authorization');
            $authenticationService->clearIdentity();
            return false;
        }
        
        Log::debug('User ID ', $user->getId(), ' is authenticated');
        
        $config = $serviceManager->get(Config::class);
        
        // Stop if neither the token needs checking, nor the user details needs update
        if (!$user->isDueTokenCheck($config) && !$user->isDueDetailsUpdate($config)) {
            Log::debug('The authenticated user does not require token check or details update');
            return true;
        }
        
        // Pull remote data if needed
        try {
            if ($user->isDueTokenCheck($config)) {
                Log::debug('Re-checking the user authentication token with the OpenXcom forum');
                $serviceManager->get(UserRemoteService::class)->checkAuthenticationToken($user);
                $user->updateLastTokenCheckDate();
                $serviceManager->get(UserPersistenceService::class)->update($user);
                Log::debug('User authentication token is valid');
            }
            
            if ($user->isDueDetailsUpdate($config)) {
                Log::debug('Updating the user data with the OpenXcom forum');
                $userData = $serviceManager->get(UserRemoteService::class)->getDisplayData($user);
                $user->updateDetails($userData);
                $serviceManager->get(UserPersistenceService::class)->update($user);
                Log::debug('User details updated');
            }
            
            return true;
        } catch (JsonRpcException\UserJsonRpcIncorrectApiKeyException $exc) {
            Log::critical('The API key is incorrect');
            $messageKey = 'module_bootstrap_usercheck_invalid_api_key';
        } catch (JsonRpcException\UserJsonRpcIncorrectAuthenticationTokenException $exc) {
            Log::notice('The user authentication token is invalid');
            $messageKey = 'module_bootstrap_usercheck_invalid_auth_token';
        } catch (JsonRpcException\UserJsonRpcMemberIdNotFoundException $exc) {
            Log::notice('The currently authenticated user is no longer present in the OpenXcom forum');
            $messageKey = 'module_bootstrap_usercheck_member_deleted';
            $user->setIsOrphan(true);
            $serviceManager->get(UserPersistenceService::class)->update($user);
        } catch (JsonRpcException\UserJsonRpcMaintenanceModeActiveException $exc) {
            Log::notice('The OpenXcom forum is in maintenance mode, revoking authorization');
            $messageKey = 'module_bootstrap_usercheck_board_in_maintenance';
        } catch (JsonRpcException\UserJsonRpcMemberBannedException $exc) {
            $messageKey = 'module_bootstrap_usercheck_member_banned';
            Log::notice('The user is banned');
        }
        
        // This is reached only if an error occured
        $authenticationService->clearIdentity();
        
        $controller = $event->getTarget();
        $controller->flashMessenger()->addErrorMessage($controller->translate($messageKey));
        
        return false;
    }
}

/* EOF */