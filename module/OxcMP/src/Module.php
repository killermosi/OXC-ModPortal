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

namespace OxcMP;

use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Config\Config;
use OxcMP\Service\Module\ModuleService;
use OxcMP\Util\Config as ConfigUtil;
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
        // Session
        'session.cookie.path' => 'session_config.cookie_path',
        // Static storage
        'static.storage' => 'layout.staticStorageUrl',
        // Logging
        'log.enabled' => 'log.enabled',
        'log.file'    => 'log.stream',
        'log.level'   => 'log.priority',
        'log.sql'     => 'log.sql',
        // oAuth parameters
        'oauth.url' => 'layout.oAuthUrl'
    ];
    
    /**
     * Module configuration
     * 
     * @return string
     */
    public function getConfig()
    {
        // Use a split configuration model - to make it easier to distinguish between
        // configuration values that are intended to be user-modified and those that are not
        return ConfigUtil::buildConfig(
            __DIR__ . '/../config/module.config.php', 
            __DIR__ . '/../config/module.config.ini',
            $this->configMap
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
        
        // Config for the layout
        $event->getViewModel()->config = $config;

        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one.
        $serviceManager->get(SessionManager::class);

        $moduleService = $serviceManager->get(ModuleService::class);
        
        // Attach events
        $event->getApplication()
            ->getEventManager()
            ->getSharedManager()
            ->attach(
                Application::class,
                MvcEvent::EVENT_ROUTE,
                [$moduleService, 'onDispatch']
            );
        
        $event->getApplication()
            ->getEventManager()
            ->getSharedManager()
            ->attach(
                Application::class,
                MvcEvent::EVENT_FINISH,
                [$moduleService, 'onFinish']
            );

        Log::debug('Bootstrapping complete');
    }
}

/* EOF */