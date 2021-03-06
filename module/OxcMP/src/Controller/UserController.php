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

namespace OxcMP\Controller;

use Zend\Session\SessionManager;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Result;
use Zend\Config\Config;
use OxcMP\Service\User\UserRetrievalService;
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
     * The user retrieval service
     * @var UserRetrievalService 
     */
    private $userRetrievalService;
    
    /**
     * The session manager
     * @var SessionManager 
     */
    private $sessionManager;
    
    /**
     * Configuration
     * @var Config
     */
    private $config;
    
    /**
     * Class initialization
     * 
     * @param AuthenticationService $authenticationService Authentication service
     * @param UserRetrievalService  $userRetrievalService  User retrieval service
     * @param SessionManager        $sessionManager        Session manager
     * @param Config                $config                Configuration
     */
    public function __construct(
        AuthenticationService $authenticationService,
        UserRetrievalService $userRetrievalService,
        SessionManager $sessionManager,
        Config $config
    ) {
        parent::__construct();
        
        $this->authenticationService = $authenticationService;
        $this->userRetrievalService = $userRetrievalService;
        $this->sessionManager = $sessionManager;
        $this->config = $config;
    }
    
    /**
     * Handle login action
     * 
     * @return void
     */
    public function loginAction()
    {
        Log::info('Processing login/login action');
        
        // Check the URL for login credentials
        $memberId = $this->params()->fromRoute('memberId', null);
        $authenticationToken = $this->params()->fromRoute('authenticationToken', null);

        // Redirect to the homepage after login or if the user visits
        // this page without the proper credentials in the URL
        if (is_null($memberId) || is_null($authenticationToken)) {
            Log::debug('Login credentials not found in the URL, redirecting to the home');
            $this->redirect()->toRoute('home');
            return;
        }
        
        // Perform login if the credentials are in the URL
        Log::debug('Attempting to login');

        // Try to login the user
        $adapter = $this->authenticationService->getAdapter();
        $adapter->setMemberId((int) $memberId);
        $adapter->setAuthenticationToken($authenticationToken);

        $result = $this->authenticationService->authenticate();

        // Considering the login scenario, the login should succeed at all times,
        // but we'll save ourseves a headache and keep an eye on failures too
        if ($result->getCode() == Result::SUCCESS) {
            Log::debug('Login successful');

            // Set session cookie lifetime
            $this->sessionManager->rememberMe($this->config->userRemote->rememberMe);

            // Success message
            $userRealName = $this->escapeHtml($result->getIdentity()->getRealName());
            $this->flashMessenger()->addSuccessMessage($this->translate('login_success_message', $userRealName));

        } else {
            Log::notice('Login failed');
            $this->flashMessenger()->addErrorMessage($this->translate('login_fail_message'));
        }
        
        // Redirect to home regardless of outcome
        $this->redirect()->toRoute('home');
    }
    
    /**
     * Handle logout action
     * 
     * @return void
     */
    public function logoutAction()
    {
        Log::info('Processing login/logout action');
        
        if ($this->authenticationService->hasIdentity()) {
            Log::debug('Logging out');
            $this->authenticationService->clearIdentity();
            $this->flashMessenger()->addSuccessMessage($this->translate('login_logout_message'));
        } else {
            Log::debug('The user is not logged in, nothing to do');
        }
        
        // Send the user to home
        $this->redirect()->toRoute('home');
    }
}

/* EOF */