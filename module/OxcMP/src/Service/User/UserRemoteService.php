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

namespace OxcMP\Service\User;

use Zend\Http\Client as HttpClient;
use Zend\Json\Server\Exception\ErrorException;
use Zend\Config\Config;
use OxcMP\Entity\User;
use OxcMP\Service\User\Support\JsonRpcClient;
use OxcMP\Util\Log;

/**
 * Handle remote user data retrieval
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class UserRemoteService
{
    /**
     * The JSON-RPC protocol version to use
     */
    const PROTOCOL_VERSION = '2.0';
    
    /**
     * The JSON-RPC client
     * @var JsonRpcClient
     */
    private $jsonRpcClient;
    
    /**
     * Class initialization
     * 
     * @param Config $config Module configuration
     */
    public function __construct(Config $config)
    {
        Log::info('Initializing UserRemoteService');
        $this->jsonRpcClient = $this->buildJsonRpcClient($config);
    }
    
    /**
     * Retrieve the display data for a user
     * 
     * @param User $user The user entity
     * @return array The display data
     * @throws Exception\UserJsonRpcGenericErrorException
     * @throws Exception\UserJsonRpcIncorrectApiKeyException
     * @throws Exception\UserJsonRpcMemberIdNotFoundException
     * @throws Exception\UserJsonRpcIncorrectAuthenticationTokenException
     * @throws Exception\UserJsonRpcMaintenanceModeActiveException
     */
    public function getDisplayData(User $user)
    {
        Log::info('Retrieving member display data for User ID: ', $user->getId());
        
        try {
            $memberData = $this->jsonRpcClient->call('Member.GetDisplayData', ['MemberId' => $user->getMemberId()]);
            
            Log::debug('Successfully retrieved the member data: ', $memberData);
            return $memberData;
        } catch (ErrorException $exc){
            throw $this->translateErrorException($exc);
        } catch (\Exception $exc) {
            Log::critical('Caught unexpected JSON-RPC exception: ', $exc->getCode(), $exc->getMessage());
            throw new Exception\UserJsonRpcGenericErrorException();
        }
    }
    
    /**
     * Check the authentication token validity for a user
     * 
     * @param User $user The user entity
     * @return boolean
     * @throws Exception\UserJsonRpcGenericErrorException
     * @throws Exception\UserJsonRpcIncorrectApiKeyException
     * @throws Exception\UserJsonRpcMemberIdNotFoundException
     * @throws Exception\UserJsonRpcIncorrectAuthenticationTokenException
     * @throws Exception\UserJsonRpcMaintenanceModeActiveException
     */
    public function checkAuthenticationToken(User $user)
    {
        Log::info('Checking authentication token validity for User ID', $user->getId());
        
        try {
            $params = [
                'MemberId'            => $user->getMemberId(),
                'AuthenticationToken' => $user->getAuthenticationToken()
            ];
            
            $isAuthTokeValid = $this->jsonRpcClient->call('Member.TokenCheck', $params);

            if ($isAuthTokeValid) {
                Log::debug('The authentication token is valid');
            } else {
                Log::debug('The authentication token is invalid');
            }
            
            return $isAuthTokeValid;
        } catch (ErrorException $exc){
            throw $this->translateErrorException($exc);
        } catch (\Exception $exc) {
            Log::critical('Caught unexpected JSON-RPC exception: ', $exc->getCode(), $exc->getMessage());
            throw new Exception\UserJsonRpcGenericErrorException();
        }
    }
    
    /**
     * Build the JSON-RPC client
     * 
     * @param Config $config Module configuration
     * @return JsonRpcClient
     */
    private function buildJsonRpcClient(Config $config)
    {
        Log::info('Building the JSON-RPC client');
        
        // Build the underlying HTTP client first
        $httpClient = new HttpClient($config->oxcForumApi->url);
        $httpClient->setHeaders([$config->oxcForumApi->header => $config->oxcForumApi->key]);
        
        // Add basic auth parameters if defined in the configuration
        $authUser = $config->oxcForumApi->basicAuth->user;
        $authPass = $config->oxcForumApi->basicAuth->pass;
        
        if (!empty($authPass) && !empty($authUser)) {
            Log::debug('Adding BASIC auth parameters');
            $httpClient->setAuth($authUser, $authPass);
        }
        
        $jsonRpcClient = new JsonRpcClient($config->oxcForumApi->url, $httpClient);
        $jsonRpcClient->setProtocolVersion(self::PROTOCOL_VERSION);
        
        Log::debug('Successfuly built the JSON-RPC client');
        
        return $jsonRpcClient;
    }
    
    /**
     * Translate a JSON-RPC error to a local exception object
     * 
     * @param ErrorException $exception The JSON-RPC error
     * @return Exception\ExceptionInterface
     */
    private function translateErrorException(ErrorException $exception)
    {
        Log::info('Translating JSON-RPC error ', $exception->getCode(), ': ', $exception->getMessage());
        
        switch ($exception->getCode()) {
            case -32600: // Invalid request
            case -32601: // Method not found
            case -32602: // Invalid params
            case -32603: // Internal error
            case -32700: // Parse error
                return new Exception\UserJsonRpcGenericErrorException();
            case -32000: // Incorrect API key
                // TODO: Notify administrator
                return new Exception\UserJsonRpcIncorrectApiKeyException();
            case -32001: // The specified member ID could not be found
                return new Exception\UserJsonRpcMemberIdNotFoundException();
            case -32002: // The authentication token is incorrect
                return new Exception\UserJsonRpcIncorrectAuthenticationTokenException();
            case -32003: // Maintenance mode active
                return new Exception\UserJsonRpcMaintenanceModeActiveException();
            default:
                Log::critical('Unexpected JSON-RPC error code received: ', $exception->getCode());
                return new Exception\UserJsonRpcGenericErrorException();
        }
    }
}

/* EOF */