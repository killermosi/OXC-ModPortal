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

namespace OxcMP\Service\Authentication;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Config\Config;
use OxcMP\Service\User\UserPersistenceService;
use OxcMP\Service\User\UserRetrievalService;
use OxcMP\Service\User\UserRemoteService;
use OxcMP\Service\User\Exception\JsonRpc as JsonRpcException;
use OxcMP\Entity\User;
use OxcMP\Util\Log;

/**
 * Handle user authentication using the Member ID (not the User ID) and the Authentication Token
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class AuthenticationAdapter implements AdapterInterface
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
     * @var Config
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
     * @param Config                 $config                 Configuration
     */
    public function __construct(
        UserPersistenceService $userPersistenceService,
        UserRetrievalService $userRetrievalService,
        UserRemoteService $userRemoteService,
        Config $config
    ) {
        Log::info('Initialiaing AuthenticationAdapter');
            
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
        $this->memberId = $memberId;
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
     * @return Result
     */
    public function authenticate()
    {
        Log::info('Attempting to authenticate Member ID ', $this->memberId);
        
        
        Log::debug('Checking if the Member ID ', $this->memberId, ' is already present in the database');
        $user = $this->userRetrievalService->findByMemberId($this->memberId);
        
        // Authenticate accordingly
        if ($user instanceof User) {
            Log::debug(
                'Member ID ',
                $this->memberId,
                ' was authenticated before, and has the local ID ',
                $user->getId()->toString()
            );
           return  $this->authenticateExistingUser($user);
        } else {
            Log::debug('Member ID ', $this->memberId, ' was not authenticated before');
            return $this->authenticateNewUser();
        }
    }

    /**
     * Perform an authentication attempt for the first time for an user
     * 
     * @return Result
     */
    private function authenticateNewUser()
    {
        Log::info('Attempting to authenticate for the first time');
        
        // Create a new User entity for authentication -  it will be
        // needed anyway further down the line if authentication succeeds
        $user = new User();
        $user->setMemberId($this->memberId);
        $user->setAuthenticationToken($this->authenticationToken);
        
        // Validate the credentials
        $validationResult = $this->validateUserAuthenticationToken($user);
        
        // If validation failed, stop
        if ($validationResult->getCode() != Result::SUCCESS) {
            return $validationResult;
        }

        // Authentication succeeded, update the last token check date
        $user->updateLastTokenCheckDate();
        
        // Retrieve the user details
        try {
            $userDetails = $this->userRemoteService->getDisplayData($user);
        } catch (\Exception $exc) {
            // Since this call is done right after a successful authentication,
            // all errors are unexpected
            Log::notice('Unexpected error encountered while retrieving the remote user data');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        }
        
        // Update the user entity with them
        $user->updateDetails($userDetails);
        
        // Persist the user in the database
        try {
            $this->userPersistenceService->create($user);
        } catch (\Exception $exc) {
            Log::notice('Unexpected error encountered while creating the user in the database');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        }
        
        // Return the previously received success result
        return $validationResult;
    }
    
    /**
     * Perform an authentication attempt for an user
     * 
     * @return Result
     */
    private function authenticateExistingUser(User $user)
    {
        Log::info('Attempting to re-authenticate the User ID ', $user->getId()->toString());
        
        // Check if it's orphan
        if ($user->getIsOrphan()) {
            Log::notice('The user is orphan!');
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null);
        }
        
        // If the token has not changed and was recently checked, do not refresh the token remotely
        if (
            $this->authenticationToken == $user->getAuthenticationToken()
            && !$user->isDueTokenCheck($this->config)
        ) {
            Log::debug('The authentication token was recently checked, accepting login');
            return new Result(Result::SUCCESS, $user);
        }
        
        // Update and validate the user credentials
        $user->setAuthenticationToken($this->authenticationToken);
        $validationResult = $this->validateUserAuthenticationToken($user);
        
        // If the user was not found on the remote system, mark it as orphan and return failure
        if ($validationResult->getCode() == Result::FAILURE_IDENTITY_NOT_FOUND) {
            Log::notice('User is orphaned');
            try {
                $user->setIsOrphan(true);
                $this->userPersistenceService->update($user);
            } catch (\Exception $exc) {
                Log::notice('Unexpected error while updating the user in the database');
            }
            
            return $validationResult;
        }
        
        // If validation failed for any other reason, stop
        if ($validationResult->getCode() != Result::SUCCESS) {
            return $validationResult;
        }
        
        // Retrieve the user details
        try {
            $userDetails = $this->userRemoteService->getDisplayData($user);
        } catch (\Exception $exc) {
            // Since this call is done right after a successful authentication,
            // all errors are unexpected
            Log::notice('Unexpected error encountered while retrieving the remote user data');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        }
        
        // Update the user entity with them
        $user->updateDetails($userDetails);
        
        // Login succeeded, update the user entity
        try {
            $user->updateLastTokenCheckDate();
            $this->userPersistenceService->update($user);
        } catch (\Exception $exc) {
            return new Result(Result::FAILURE_UNCATEGORIZED, $user);
        }
        
        // Return the success result
        return $validationResult;
    }
    
    /**
     * Validate a user authentication token
     * 
     * @param User $user The user entity
     * @return Result
     */
    private function validateUserAuthenticationToken(User $user)
    {
        Log::info('Validating credentials with the remote server');
        
        try {
            $this->userRemoteService->checkAuthenticationToken($user);
        } catch (JsonRpcException\UserJsonRpcGenericErrorException $exc) {
            Log::notice('Unexpected error encountered while checking the authentication token');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        } catch (JsonRpcException\UserJsonRpcIncorrectApiKeyException $exc) {
            Log::critical('API key is incorrect!');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        } catch (JsonRpcException\UserJsonRpcMemberIdNotFoundException $exc) {
            Log::notice('MemberId not found');
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null);
        } catch (JsonRpcException\UserJsonRpcIncorrectAuthenticationTokenException $exc) {
            Log::notice('Incorrect authentication token');
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null);
        } catch (JsonRpcException\UserJsonRpcMaintenanceModeActiveException $exc) {
            Log::notice('The board is in maintenance mode');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        } catch (JsonRpcException\UserJsonRpcMemberBannedException $exc) {
            Log::notice('The member is banned');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        }
        
        Log::debug('Authentication token is valid!');
        return new Result(Result::SUCCESS, $user);
    }
}

/* EOF */