<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service\User\Support;

use Zend\Json\Server\Client;
use Zend\Json\Server\Request;
use OxcMP\Util\Log;

/**
 * Customized JSON-RPC client
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class JsonRpcClient extends Client
{
    /**
     * The JSON-RPC protocol version to use
     * @var type 
     */
    protected $protocolVersion;
    
    /**
     * Set the JSON-RPC protocol version to use
     * 
     * @param string $version Version string: "1.0" or "2.0"
     * @return void;
     */
    public function setProtocolVersion($version)
    {
        $this->protocolVersion = $version;
    }
    
    /**
     * Create request object.
     *
     * @param  string $method Method to call.
     * @param  array  $params List of arguments.
     * @return Request Created request.
     */
    protected function createRequest($method, array $params)
    {
        $request = parent::createRequest($method, $params);
        $request->setVersion($this->protocolVersion);
        
        return $request;
    }
    
    /**
     * Send a JSON-RPC request to the service (for a specific method).
     *
     * @param  string $method Name of the method we want to call.
     * @param  array  $params  Array of parameters for the method.
     * @return mixed Method call results.
     * @throws \Zend\Json\Server\Exception\ErrorException When remote call fails.
     */
    public function call($method, $params = [])
    {
        Log::info('Calling JSON-RPC method "', $method, '" with parameters: ', $params);
        
        return parent::call($method, $params);
    }
}

/* EOF */