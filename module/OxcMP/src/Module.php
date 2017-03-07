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

namespace OxcMP;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Config\Config;
use OxcMP\Util\Log;

class Module
{
    const VERSION = '1.0.0';
    
    /**
     * Public-to-private configuration mapping
     * @var array 
     */
    private $configMap = [
        // API
        'api.url'            => 'oxcForumApi.url',
        'api.key'            => 'oxcForumApi.key',
        'api.basicAuth.user' => 'oxcForumApi.basicAuth.user',
        'api.basicAuth.pass' => 'oxcForumApi.basicAuth.pass',
        // Database
        'db.host' => 'doctrine.connection.orm_default.params.host',
        'db.port' => 'doctrine.connection.orm_default.params.port',
        'db.user' => 'doctrine.connection.orm_default.params.user',
        'db.pass' => 'doctrine.connection.orm_default.params.password',
        'db.name' => 'doctrine.connection.orm_default.params.dbname',
        // Logging
        'log.enabled' => 'log.enabled',
        'log.file'    => 'log.stream',
        'log.level'   => 'log.priority',
        // oAuth parameters
        'oauth.url' => 'layout.oAuthUrl'
    ];
    
    /**
     * Module config
     * 
     * @return string
     */
    public function getConfig()
    {
        // Use a split configuration model - to make it easier to distinguish between
        // configuration values that are intended to be user-modified and those that are not
        return array_replace_recursive(
            require  __DIR__ . '/../config/module.config.php',
            $this->buildPublicConfig( __DIR__ . '/../config/module.config.ini')
        );
    }
    
    /**
     * Module bootstrap
     * 
     * @param MvcEvent $event The event
     * @return void
     */
    public function onBootstrap(MvcEvent $event)
    {
        // Service manager
        $serviceManager = $event->getApplication()->getServiceManager();
        $config = $serviceManager->get(Config::class);
        
        // Init the log
        Log::init($config);
        
        Log::info('Application starting, bootstrapping...');
        
        // Config and default backgroud image
        $event->getViewModel()->config = $config;
        $event->getViewModel()->defaultBackground = $config->layout->defaultBackground;
        
        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one.
        $serviceManager->get(SessionManager::class);
        
        Log::debug('Bootstrapping complete');
    }
    
    /**
     * Build the public configuration array
     * 
     * @param string $configFile Path to configuration file
     * @return array
     */
    private function buildPublicConfig($configFile)
    {
        // Read the config data
        $config = parse_ini_file($configFile, false, INI_SCANNER_TYPED);
        
        // Adjust the config data
        $adjustedConfig = [];
        
        foreach ($this->configMap as $oldKey => $newKey) {
            if (!isset($config[$oldKey])) {
                throw new \Exception('Missing config key: ' . $oldKey);
            }
            
            $adjustedConfig[$newKey] = $config[$oldKey];
            unset($config[$oldKey]);
        }
        
        return $this->dimensionalSplit($adjustedConfig);
    }
    
    /**
     * Transform any value that that contains the dot character into a multi-dimensional array,
     * each dot denoting another level. Code adapted from: http://stackoverflow.com/a/9636021/1111983
     * 
     * @param array $data An associative array with the data
     * @return array
     */
    private function dimensionalSplit(array $data)
    {
        foreach ($data as $key => $value) {
            // Do not process if there are no dots in the key name
            if (false === strpos($key, '.')) {
                continue;
            }
            
            // Transform the key containing dots to a multi-dimensional array
            $levels = explode('.', $key);
            
            $array = array();
            $ref = &$array;
            
            foreach ($levels as $level) {
                $ref[$level] = array();
                $ref = &$ref[$level];
            }
            
            $ref = $value;
            
            // Merge the array obtained from the key name with the original one
            $data = array_merge_recursive($data, $array);
            
            // And remove the original key
            unset($data[$key]);
        }

        return $data;
    }
}

/* EOF */