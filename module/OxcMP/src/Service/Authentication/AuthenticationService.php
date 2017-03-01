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

namespace OxcMP\Service\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use OxcMP\Service\User\UserPersistenceService;
use OxcMP\Service\User\UserRetrievalService;
use OxcMP\Service\User\UserRemoteService;
use OxcMP\Service\Config\ConfigService;

/**
 * Handle user authentication
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class AuthenticationService implements AdapterInterface
{
    /**
     * User persistence service
     * @var UserPersistenceService
     */
    private $userPersistenceService;
    
    /**
     * User retrieval service
     * @var UserRetrievalService
     */
    private $userRetrievalService;
    
    /**
     * User remote service
     * @var UserRemoteService
     */
    private $userRemoteService;
    
    /**
     * Configuration
     * @var ConfigService
     */
    private $config;
    
    /**
     * The OpenXcom forum member ID
     * @var integer
     */
    private $memberId;
    
    /**
     * The OpenXcom forum authentication token
     * @var string
     */
    private $authenticationToken;
    
    /**
     * Class initialization
     * 
     * @param UserPersistenceService $userPersistenceService User persistence service
     * @param UserRetrievalService   $userRetrievalService   User retrieval service
     * @param UserRemoteService      $userRemoteService      User remote service
     * @param ConfigService          $config                 Configuration
     */
    public function __construct(
        UserPersistenceService $userPersistenceService,
        UserRetrievalService $userRetrievalService,
        UserRemoteService $userRemoteService,
        ConfigService $config
    ) {
        $this->userPersistenceService = $userPersistenceService;
        $this->userRetrievalService   = $userRetrievalService;
        $this->userRemoteService      = $userRemoteService;
        $this->config                 = $config;
    }

    /**
     * Set the member ID
     * 
     * @param integer $memberId The member ID
     * @return void
     */
    public function setMemberId($memberId)
    {
        
    }
    
    /**
     * Set the authentication token
     * 
     * @param string $authenticationToken The authentication token
     * @return void
     */
    public function setAuthenticationToken($authenticationToken)
    {
        $this->authenticationToken = $authenticationToken;
    }
    
    /**
     * Perform an authentication attempt
     * 
     * @return void
     */
    public function authenticate()
    {
        
    }

}

/* EOF */