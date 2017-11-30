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

namespace OxcMP\Util;

/**
 * IP utility
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class Ip
{
    /**
     * Find the client IP address
     * Adapted from: https://stackoverflow.com/a/43703510/1111983
     * 
     * @return string
     */
    public static function getRemoteIp()
    {
        // Where to look for the IP address, and in which order
        $envVars = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];
        
        foreach ($envVars as $envVar) {
            $ipAddress = getenv($envVar);
            
            if (!empty($ipAddress)) {
                return $ipAddress;
            }
        }
        
        // IP address not retrieved
        return 'UNKNOWN';
    }
}

/* EOF */