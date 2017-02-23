<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service\User;

use Zend\Http\Client as HttpClient;
use Zend\Json\Server\Exception\ErrorException;
use OxcMP\Entity\User;
use OxcMP\Service\Config\ConfigService;
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
     * @param ConfigService $config Module configuration
     */
    public function __construct(ConfigService $config)
    {
        $this->jsonRpcClient = $this->buildJsonRpcClient($config);
    }
    
    /**
     * Retrieve the display data for a user
     * 
     * @param User $user The user entity
     * @return array The display data
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
     * @param ConfigService $config Module configuration
     * @return JsonRpcClient
     */
    private function buildJsonRpcClient(ConfigService $config)
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