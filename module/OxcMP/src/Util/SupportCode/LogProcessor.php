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

namespace OxcMP\Util\SupportCode;

use Zend\Log\Processor\ProcessorInterface;

/**
 * Make several adjustments to the logged data
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class LogProcessor implements ProcessorInterface
{
    /**
     * The request identifier
     * @var integer
     */
    private $requestId;
    
    /**
     * The client IP address
     * @var string 
     */
    private $remoteIp;
    
    /**
     * Set the request ID
     * 
     * @param integer $requestId The request identifier
     * @return void
     */
    function setRequestId($requestId)
    {
        $this->requestId = $requestId;
    }

    /**
     * Set the remote client IP address
     * 
     * @param string $remoteIp The client IP address
     * @return void
     */
    function setRemoteIp($remoteIp)
    {
        $this->remoteIp = $remoteIp;
    }

    /**
     * Processes a log message before it is given to the writers
     * 
     * @param array $event The event
     * @return array
     */
    public function process(array $event)
    {
        // Pad priority name, to line the info nicely in the log
        $event['priorityName'] = str_pad($event['priorityName'], 6);
        
        // Create a new "timestamp" value, that includes microseconds
        $event['timestamp'] = \DateTime::createFromFormat('U.u', microtime(true));
        
        // Add request ID and remote IP to the event
        $event['requestId'] = $this->requestId;
        $event['remoteIp'] = $this->remoteIp;
        
        return $event;
    }
}

/* EOF */