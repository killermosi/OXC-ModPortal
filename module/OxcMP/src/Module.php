<?php

namespace OxcMP;

use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use OxcMP\Service\Config\ConfigService;
use OxcMP\Util\Log;

class Module
{
    const VERSION = '3.0.1';
    
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
        $config = $serviceManager->get(ConfigService::class);
        
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
     * Transform any value that that contains the dot character into a multi-dimensional array
     * (each dot denotes another level)
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