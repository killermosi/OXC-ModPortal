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

namespace OxcMP\Service\Module;

use Zend\Authentication\AuthenticationService;
use Zend\Mvc\MvcEvent;
use Zend\Config\Config;
use OxcMP\Entity\User;
use OxcMP\Service\Acl\AclService;
use OxcMP\Service\Acl\Role;
use OxcMP\Service\User\UserPersistenceService;
use OxcMP\Service\User\UserRemoteService;
use OxcMP\Service\User\UserRetrievalService;
use OxcMP\Service\User\Exception\JsonRpc as JsonRpcException;
use OxcMP\Util\Log;

/**
 * This service offloads the processing logic from the module initialization script
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModuleService
{
    /**
     * If this request was done to the static domain
     * @var boolean
     */
    private $isStaticDomainRequest = false;
    
    /**
     * The there is a user currently logged in
     * @var boolean
     */
    private $isUserLoggedIn = false;
    
    /**
     * If the current request was handled by the dispatcher
     * @var boolean
     */
    private $wasDispatcherExecuted = false;
    
    /**
     * Actions to execute on dispatch
     * 
     * @param MvcEvent $event The event
     * @return void
     */
    public function onDispatch(MvcEvent $event)
    {
        Log::info('Handling EVENT_DISPATCH actions');
        
        $this->wasDispatcherExecuted = true;
        
        // Services
        $serviceManager = $event->getApplication()->getServiceManager();
        /* @var $authenticationService AuthenticationService */
        $authenticationService = $serviceManager->get(AuthenticationService::class);
        
        // The  requested route
        $route = $event->getRouteMatch()->getMatchedRouteName();
        
        // For static domain requests, allow only GUEST access
        if ($this->isStaticRequest($event)) {
            Log::debug('Handling request to static domain');
            
            $this->isStaticDomainRequest = true;
            
            if (!$serviceManager->get(AclService::class)->isAclAllowed($route, Role::GUEST)) {
                Log::debug('EVENT_DISPATCH result: redirect to "home"');
                return $this->redirectTo($event, 'home');
            }
            
            return;
        }
        
        Log::debug('Handling request to non-static domain');
        
        // Update authenticated user, stop on error
        if (false === $this->checkAndUpdateAuthenticatedUser($event)) {
            Log::debug('EVENT_DISPATCH result: redirect to "home"');
            return $this->redirectTo($event, 'home');
        }
        
        // Retrieve the authenticated user
        if ($authenticationService->hasIdentity()) {
            $authenticatedUser = $authenticationService->getIdentity();
        } else {
            $authenticatedUser = null;
        }

        // Set the authenticated user view value
        $event->getViewModel()->authenticatedUser = $authenticatedUser;
        
        /* Check ACL */
        
        // User role - default value
        $userRole = Role::GUEST;

        // If logged in, 
        if ($authenticatedUser instanceof User) {
            $userRole = $authenticatedUser->getIsAdministrator() ? Role::ADMINISTRATOR : Role::MEMBER;
            $this->isUserLoggedIn = true;
        }
        
        // Redirect the user to the home page if it is not allowed to access the page
        if (!$serviceManager->get(AclService::class)->isAclAllowed($route, $userRole)) {
            $errorKey = (false == $authenticationService->hasIdentity())
                ? 'acl_not_logged_in'
                : 'acl_not_allowed';
            
            $translate = $serviceManager->get('ViewHelperManager')->get('Translate');
            
            $errorMessage = $translate($errorKey);
            $serviceManager->get('ControllerPluginManager')->get('FlashMessenger')
                                                           ->addErrorMessage($errorMessage);
            
            Log::debug('EVENT_DISPATCH result: redirect to "home"');
            return $this->redirectTo($event, 'home');
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
            $authenticationService->getIdentity()->getId()
        );

        // Sanity check
        if (!$user instanceof User) {
            Log::notice('The current authenticated user was not found in the database, revoking authorization');
            $authenticationService->clearIdentity();
            return false;
        }
        
        Log::debug('User ID ', $user->getId()->toString(), ' is authenticated');
        
        $config = $serviceManager->get(Config::class);
        
        // Stop if neither the token needs checking, nor the user details need update
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
        
        $translate = $serviceManager->get('ViewHelperManager')->get('Translate');
        $serviceManager->get('ControllerPluginManager')->get('FlashMessenger')
                                                       ->addErrorMessage($translate($messageKey));
        
        return false;
    }
    
    /**
     * Check if this request was done to the static domain
     * 
     * @param MvcEvent $event The event
     * @return boolean
     */
    private function isStaticRequest(MvcEvent $event)
    {
        Log::info('Checking if the request was done to the static resource domain');
        
        $serviceManager = $event->getApplication()->getServiceManager();
        
        // Get and check the static storage URL
        $staticStorageUrl = strtolower($serviceManager->get(Config::class)->layout->staticStorageUrl);
        
        if (empty($staticStorageUrl)) {
            Log::debug('No static storage defined, no static domain request possible');
            return false;
        }
        
        // TODO: This seems hackish, find better way to determine if the requested domain was the static one or not
        $urlHelper = $event->getApplication()
            ->getServiceManager()
            ->get('ViewHelperManager')
            ->get('Url');
        
        $requestUrl = strtolower($urlHelper('home',[], ['force_canonical' => true]));

        if (rtrim($staticStorageUrl, '/') == rtrim($requestUrl, '/')) {
            Log::debug('The request was done to the static resource domain');
            return true;
        } else {
            Log::debug('The request was done to the standard application domain');
            return false;
        }
    }
    
    public function onFinish(MvcEvent $event)
    {
        Log::info('Handling EVENT_FINISH actions');
        
        if ($this->mustDestroySession($event)) {
            $this->destroySession();
        }
        
        Log::debug('EVENT_FINISH actions handled, application closing...');
    }
    
    /**
     * Check if the current session needs to be destroyed
     * 
     * @param MvcEvent $event The MVC event
     * @return boolean
     */
    private function mustDestroySession(MvcEvent $event)
    {
        Log::info('Checking if the current session needs to be destroyed');
        
        if (!$this->wasDispatcherExecuted) {
            Log::debug(
                'The request was not handled by the dispatcher (probably an error occured),',
                ' the session must not be destroyed'
            );
            return false;
        }
        
        if ($this->isStaticDomainRequest) {
            Log::debug('The current request was done to the static domain, session needs to be destroyed');
            return true;
        }
        
        if ($this->isUserLoggedIn) {
            Log::debug('There is a user logged in, the session must not be destroyed');
            return false;
        }
        
        // Check for flash messages
        /* @var $flashMessenger \Zend\Mvc\Plugin\FlashMessenger\FlashMessenger */
        $flashMessenger = $event->getApplication()->getServiceManager()
            ->get('ControllerPluginManager')
            ->get('FlashMessenger');
        
        // There seems to be a bug with the hasMessages() and hasCurrentMessages() methods
        // (or I missed something during testing), as they always return false,
        // so we check using the specific methods for messages types
        if (
            $flashMessenger->hasCurrentSuccessMessages()
            || $flashMessenger->hasCurrentErrorMessages()
            || $flashMessenger->hasCurrentInfoMessages()
            || $flashMessenger->hasCurrentWarningMessages()
            || $flashMessenger->hasSuccessMessages()
            || $flashMessenger->hasErrorMessages()
            || $flashMessenger->hasInfoMessages()
            || $flashMessenger->hasWarningMessages()
        ) {
            Log::debug('There are flash messages present, the session must not be destroyed');
            return false;
        }
        
        Log::debug(
            'The request is not done to the static domain, ',
            'there is no user logged in and ',
            'there are no flash messages present, ',
            'the session must be destroyed'
        );
        return true;
    }
    
    /**
     * Build a redirect response  to the specified route
     * 
     * @param \OxcMP\Service\Module\MvsEvent $event
     * @param type $routeName
     */
    private function redirectTo(MvcEvent $event, $routeName)
    {
        $url = $event->getRouter()->assemble([], ['name' => $routeName]);
        
        $response = $event->getResponse();
        $response->getHeaders()->addHeaderLine('Location', $url);
        $response->setStatusCode(307);
        $response->sendHeaders();
        
        return $response;
    }
    
    /**
     * Destroy the current session
     * 
     * @return void
     */
    private function destroySession()
    {
        Log::info('Destroying sesion');
        
        // TODO: This seems hackish too, check for a better way
        $_SESSION = array();
        
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                null,
                1,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        session_destroy();
        
        Log::debug('Session destroyed');
    }
}

/* EOF */