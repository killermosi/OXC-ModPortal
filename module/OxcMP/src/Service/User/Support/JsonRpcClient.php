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