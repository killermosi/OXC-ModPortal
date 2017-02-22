<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service\User;

use Zend\Json\Server\Client as JsonRpcClient;
use Zend\Http\Client as HttpClient;
use OxcMP\Service\Config\ConfigService;

/**
 * Description of UserRemoteService
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class UserRemoteService
{
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
        // Build the underlying HTTP client
        $httpClient = new HttpClient();
    }
}

/* EOF */