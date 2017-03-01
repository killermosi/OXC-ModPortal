<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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