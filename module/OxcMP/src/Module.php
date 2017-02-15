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
        return include __DIR__ . '/../config/module.config.php';
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
        
        // Init the log first
        Log::init($serviceManager->get('cfg'));
        
        Log::info('Application started');
        
        // Add the config to the layout
        $event->getViewModel()->config = $serviceManager->get('cfg');
        
        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one.
        $serviceManager->get(SessionManager::class);
        
        Log::debug('Bootstrapping complete');
    }
}

/* EOF */