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

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use OxcMP\Entity\User;
use OxcMP\Util\Log;

/**
 * Handle user login and logout, and potential settings
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class UserController extends AbstractController
{
    /**
     * The authentication service
     * @var AuthenticationService
     */
    private $authenticationService;
    
    /**
     * Class initialization
     * 
     * @param AuthenticationService $authenticationService The authentication service
     */
    public function __construct(AuthenticationService $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }
    
    /**
     * Handle login action
     * 
     * @return void
     */
    public function loginAction()
    {
        Log::info('Processing login action');
        
        // Check the URL for login credentials
        $memberId = $this->params()->fromRoute('memberId', null);
        $authenticationToken = $this->params()->fromRoute('authenticationToken', null);
        
        // Use the 'login' namespace for the flash messenger
        // Note: Although the flash messenger was not created for this, it is well suited for
        // the task, as we need to remembered the credentials for the next request and only
        // for the next request
        $this->flashMessenger()->setNamespace('login');
        
        // If login credentials are in the URL, store them in the flash messenger
        // and do a redirect back to the login page but without the parameters in the URL
        if (!is_null($memberId) && !is_null($authenticationToken)) {
            Log::debug('Login parameters found in the URL, redirecting back to the login page');
            $this->flashMessenger()
                ->addMessage($memberId)
                ->addMessage($authenticationToken);
            
            $this->redirect()->toRoute('login');
            return;
        }
        
        // Perform login if the credentials are in the flash messenger
        if ($this->flashMessenger()->hasMessages()) {
            Log::debug('Attempting to login');
            list($memberId, $authenticationToken) = $this->flashMessenger()->getMessages();
            
            // Try to login the user
            $adapter = $this->authenticationService->getAdapter();
            $adapter->setMemberId((int) $memberId);
            $adapter->setAuthenticationToken($authenticationToken);
            
            $result = $this->authenticationService->authenticate();
            
            // Considering the login scenario, the login should succeed at all times,
            // but we'll save ourseves a headache and keep an eye on failures too
            if ($result->getCode() == Result::SUCCESS) {
                Log::debug('Login successful');
                
                $escapeHtml = $this->getService('ViewHelperManager')->get('escapeHtml');
                /* @var $user User */
                $user = $result->getIdentity();
                
                $userRealName = $escapeHtml($user->getRealName());
                $this->flashMessenger()->addSuccessMessage($this->translate('login_success_message', $userRealName));
            } else {
                Log::notice('Login failed');
                $this->flashMessenger()->addErrorMessage($this->translate('login_fail_message'));
            }
        }
        
        // Redirect to the homepage after login or if the user visits
        // this page without the proper credentials in the URL
        
        $this->redirect()->toRoute('home');
        return;
    }
    
    /**
     * Handle logout action
     * 
     * @return void
     */
    public function logoutAction()
    {
        Log::info('Processing logout action');
        
        if ($this->authenticationService->hasIdentity()) {
            Log::debug('Logging out');
            $this->authenticationService->clearIdentity();
        } else {
            Log::debug('The user is not logged in, nothing to do');
        }
        
        // Send the confirmation message anyway and send the user to home
        $this->flashMessenger()->addSuccessMessage($this->translate('login_logout_message'));
        $this->redirect()->toRoute('home');
    }
}

/* EOF */