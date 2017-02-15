<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Util;

use Zend\Config\Config;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream;
use Zend\Log\Filter\Priority;

/**
 * Module Logger
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class Log {
    /**
     * Zend logger instance
     * @var Logger 
     */
    private static $logger;
    
    /**
     * Initialize the logger
     * 
     * @param Config $config Application configuration
     * @return void
     */
    public static function init(Config $config)
    {
        self::$logger = new Logger();
        
        $writer = new Stream($config->log->stream);
        $writer->addFilter(new Priority($config->log->priority));
        
        self::$logger->addWriter($writer);
    }
    
    /**
     * Write a debug message
     * 
     * @param mixed $message The message to write
     * @return void
     */
    public static function debug($message)
    {
        self::$logger->log(Logger::DEBUG, $message);
    }
    
    /**
     * Write an info message
     * 
     * @param mixed $message The message to write
     * @return void
     */
    public static function info($message)
    {
        self::$logger->log(Logger::INFO, $message);
    }
    
    /**
     * Write a notice message
     * 
     * @param mixed $message The message to write
     * @return void
     */
    public static function notice($message)
    {
        self::$logger->log(Logger::NOTICE, $message);
    }
    
    /**
     * Write a warning message
     * 
     * @param mixed $message The message to write
     * @return void
     */
    public static function warn($message)
    {
        self::$logger->log(Logger::WARN, $message);
    }
    
    /**
     * Write an error message
     * 
     * @param mixed $message The message to write
     * @return void
     */
    public static function error($message)
    {
        self::$logger->log(Logger::ERR, $message);
    }
    
    /**
     * Write a critical message
     * 
     * @param mixed $message The message to write
     * @return void
     */
    public static function critical($message)
    {
        self::$logger->log(Logger::CRIT, $message);
    }
    
    /**
     * Write an alert message
     * 
     * @param mixed $message The message to write
     * @return void
     */
    public static function alert($message)
    {
        self::$logger->log(Logger::ALERT, $message);
    }
    
    /**
     * Write an alert message
     * 
     * @param mixed $message The message to write
     * @return void
     */
    public static function emergency($message)
    {
        self::$logger->log(Logger::EMERG, $message);
    }
}

/* EOF */