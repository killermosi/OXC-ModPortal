<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service\User;

use Zend\Http\Client as HttpClient;
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
     * Call the Member.GetDisplayData on the remote system
     * 
     * @param type $memberId The member ID
     * @return array The member data
     */
    public function memberGetDisplayData($memberId)
    {
        Log::info('Retrieving member display data for Member ID: ', $memberId);
        
        $memberData = $this->jsonRpcClient->call('Member.GetDisplayData', ['MemberId' => (int) $memberId]);
        
        return $memberData;
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
}

/* EOF */