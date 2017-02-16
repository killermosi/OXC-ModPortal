<?php

namespace OxcMP;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use OxcMP\Util\Log;

class Module
{
    const VERSION = '3.0.1';

    /**
     * Module config
     * 
     * @return string
     */
    public function getConfig()
    {
        // Use a split configuration - to make it easier to distinguish between
        // configuration values that are intended to be user-modified and those which are not
        return array_merge_recursive(
            require  __DIR__ . '/../config/module.config.private.php',
            require  __DIR__ . '/../config/module.config.php'
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
        $config = $serviceManager->get('cfg');
        
        // Init the log
        Log::init($config);
        
        Log::info('Application starting, bootstrapping...');
        
        // Add the config to the layout
        $event->getViewModel()->config = $config;
        
        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one.
        $serviceManager->get(SessionManager::class);
        
        Log::debug('Bootstrapping complete');
    }
}

/* EOF */