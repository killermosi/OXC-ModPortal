<?php

namespace OxcMP;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Config\Config;

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
        $serviceManager = $event->getApplication()->getServiceManager();
        $viewModel = $event->getApplication()->getMvcEvent()->getViewModel();
    
        $viewModel->config = new Config($serviceManager->get('Config'));
        
        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one.
        $serviceManager->get(SessionManager::class);
    }
}

/* EOF */