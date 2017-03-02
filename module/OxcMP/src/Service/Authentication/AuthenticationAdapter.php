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
use Zend\Authentication\Result;
use OxcMP\Service\User\UserPersistenceService;
use OxcMP\Service\User\UserRetrievalService;
use OxcMP\Service\User\UserRemoteService;
use OxcMP\Service\Config\ConfigService;
use OxcMP\Service\User\Exception as UserException;
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
        
        // Look for the member ID in the database
        $user = $this->userRetrievalService->findByMemberId($this->memberId);
        
        // Authenticate accordingly
        return ($user instanceof User)
            ? $this->authenticateExistingUser($user)
            : $this->authenticateNewUser();
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
        $user->setRealName($userDetails['RealName']);
        $user->setPersonalText($userDetails['PersonalText']);
        $user->setIsAdministrator($userDetails['IsAdministrator']);
        $user->setAvatarUrl($userDetails['Avatar']);
        $user->updateLastTokenCheckDate();
        
        // Persist the user in the database
        try {
            $this->userPersistenceService->create($user);
        } catch (\Exception $exc) {
            Log::notice('Unexpected error encountered while creating the user in the database');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        }
        
        // Return the success result
        return $validationResult;
    }
    
    /**
     * Perform an authentication attempt for an user
     * 
     * @return Result
     */
    private function authenticateExistingUser(User $user)
    {
        Log::info('Attempting to authenticate the User ID ', $user->getId());
        
        // Check if it's orphan
        if ($user->getIsOrphan()) {
            Log::notice('The user is orphan!');
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null);
        }
        
        // If the token has not changed and was recently checked, do not refresh the token remotely
        $timeInterval = 'PT' . $this->config->userRemote->tokenCheckDelay . 'S';
        if (
            $this->authenticationToken == $user->getAuthenticationToken()
            && $user->getLastTokenCheckDate() > (new \DateTime())->sub(new \DateInterval($timeInterval))
        ) {
            Log::debug('The authentication token was recently checked, accepting login ');
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
        
        // Validation succeeded, update the user entity
        try {
            $user->updateLastTokenCheckDate();
            $this->userPersistenceService->update($user, true);
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
        } catch (UserException\UserJsonRpcGenericErrorException $exc) {
            Log::notice('Unexpected error encountered while checking the authentication token');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        } catch (UserException\UserJsonRpcIncorrectApiKeyException $exc) {
            Log::critical('API key is incorrect!');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        } catch (UserException\UserJsonRpcMemberIdNotFoundException $exc) {
            Log::notice('MemberId not found');
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null);
        } catch (UserException\UserJsonRpcIncorrectAuthenticationTokenException $exc) {
            Log::notice('Incorrect authentication token');
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null);
        } catch (UserException\UserJsonRpcMaintenanceModeActiveException $exc) {
            Log::notice('The board is in maintenance mode');
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        }
        
        Log::debug('Validation successful!');
        return new Result(Result::SUCCESS, $user);
    }
}

/* EOF */