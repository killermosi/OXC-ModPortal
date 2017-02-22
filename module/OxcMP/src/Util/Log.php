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
use Zend\Log\Formatter\Simple;

/**
 * Module Logger
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class Log
{
    /**
     * Zend logger instance
     * @var Logger 
     */
    private static $logger;
    
    /**
     * The format to use when writing a line
     * @var string 
     */
    private static $lineFormat = '%timestamp%|%priorityName%: %message% %extra%';
    
    /**
     * The format to use when writing the date
     * @var string 
     */
    private static $dateTimeFormat = 'Ymd H:i:s'; // TODO:: add microseconds (u)
    
    /**
     * Initialize the logger
     * 
     * @param Config $config Application configuration
     * @return void
     */
    public static function init(Config $config)
    {
        // Don't init if disabled in the config
        if (!($config->log->enabled)) {
            return;
        }

        $writer = new Stream($config->log->stream);
        
        $filter = new Priority($config->log->priority);
        $writer->addFilter($filter);
        
        $formatter = new Simple(self::$lineFormat, self::$dateTimeFormat);
        $writer->setFormatter($formatter);

        self::$logger = new Logger();        
        self::$logger->addWriter($writer);
        
        // Register the logger as error and exception handler
        Logger::registerErrorHandler(self::$logger);
        Logger::registerExceptionHandler(self::$logger);
    }
    
    /**
     * Log a debug message
     * 
     * @param mixed $message The message to log
     * @return void
     */
    public static function debug(...$message)
    {
        self::log(Logger::DEBUG, ...$message);
    }
    
    /**
     * Log an info message
     * 
     * @param mixed $message The message to log
     * @return void
     */
    public static function info(...$message)
    {
        self::log(Logger::INFO, ...$message);
    }
    
    /**
     * Log a notice message
     * 
     * @param mixed $message The message to log
     * @return void
     */
    public static function notice(...$message)
    {
        self::log(Logger::NOTICE, ...$message);
    }
    
    /**
     * Log a warning message
     * 
     * @param mixed $message The message to log
     * @return void
     */
    public static function warn(...$message)
    {
        self::log(Logger::WARN, ...$message);
    }
    
    /**
     * Log an error message
     * 
     * @param mixed $message The message to log
     * @return void
     */
    public static function error(...$message)
    {
        self::log(Logger::ERR, ...$message);
    }
    
    /**
     * Log a critical message
     * 
     * @param mixed $message The message to log
     * @return void
     */
    public static function critical(...$message)
    {
        self::log(Logger::CRIT, ...$message);
    }
    
    /**
     * Log an alert message
     * 
     * @param mixed $message The message to log
     * @return void
     */
    public static function alert(...$message)
    {
        self::log(Logger::ALERT, ...$message);
    }
    
    /**
     * Log an alert message
     * 
     * @param mixed $message The message to log
     * @return void
     */
    public static function emergency(...$message)
    {
        self::log(Logger::EMERG, ...$message);
    }
    
    /**
     * Log a message
     * TODO: Check for redundant operations
     * 
     * @param integer $priority The message priority
     * @param mixed   $message The message(s) to log
     * @return void
     */
    private static function log($priority, ...$message)
    {
        // Stop if there is nothing to log
        if (empty($message)) {
            return;
        }
        
        // Stop if the logger is not enabled
        if (is_null(self::$logger)) {
            return;
        }
        
        // Do a bit of formatting
        $logData = '';
        
        foreach ($message as $msg) {
            if (is_string($msg)) {
                $logData .= $msg;
            } elseif (is_null($msg)) {
                $logData .= 'NULL';
            } else {
                $logData .= print_r($msg, true);
            }
        }
        
        $lines = explode(PHP_EOL, $logData);
        
        foreach ($lines as $line) {
            self::$logger->log($priority, $line);
        }
    }
}

/* EOF */