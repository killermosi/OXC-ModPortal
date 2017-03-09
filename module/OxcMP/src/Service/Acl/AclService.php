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

namespace OxcMP\Service\Acl;

use Zend\Permissions\Acl\Acl;
use Zend\Config\Config;
use OxcMP\Util\Log;

/**
 * Handle access control lists
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class AclService
{
    /**
     * The access control list, using format 'route' => [roles]
     * @var array
     */
    private $acl = [];
    
    /**
     * Class initialization
     * 
     * @param Config $config The configuration
     */
    public function __construct(Config $config)
    {
        Log::info('ACL service intializing');
        $this->loadAcl($config);
    }
    
    /**
     * Check if a certain route is accessible to a certain role, using a whitelist access model
     * 
     * @param string $route The route name
     * @param string $role  The role name
     * @return void
     */
    public function isAclAllowed($route, $role = Role::GUEST)
    {
        Log::info('Checking if route "', $route, '" is accessible to "', $role, '" role');
        
        if (!$this->acl->isAllowed($role, $route)) {
            Log::notice('Route "', $route, '" is not accesible to role "', $role, '"');
            return false;
        }
        
        Log::debug('Route "', $route, '" is accesible to role "', $role, '"');
        return true;
    }
    
    /**
     * Load the ACL from the configuration
     * 
     * @param Config $config The configuration
     * @return void
     */
    private function loadAcl(Config $config)
    {
        Log::info('Loading ACL');
        
        $this->acl = new Acl();
        
        // Set roles
        $this->acl->addRole(Role::GUEST);
        $this->acl->addRole(Role::MEMBER);
        $this->acl->addRole(Role::ADMINISTRATOR);
        
        // Set resources and permissions
        foreach ($config->router->routes as $routeName => $route) {
            if (!isset($route->options->acl)) {
                continue;
            }
            
            // Add the resource
            $this->acl->addResource($routeName);
            
            // Per these roles to access the resource
            foreach ($route->options->acl as $role) {
                $this->acl->allow($role, $routeName);
            }
        }
        
        Log::debug('ACL loaded');
    }
}

/* EOF */