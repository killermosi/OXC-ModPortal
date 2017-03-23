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
     * @param mixed   $messages The message(s) to log
     * @return void
     */
    private static function log($priority, ...$messages)
    {
        // Stop if there is nothing to log
        if (empty($messages)) {
            return;
        }
        
        // Stop if the logger is not enabled
        if (is_null(self::$logger)) {
            return;
        }
        
        // Do a bit of formatting
        $logData = '';
        
        foreach ($messages as $message) {
            if (is_string($message)) {
                $logData .= $message;
            } elseif (is_null($message)) {
                $logData .= 'NULL';
            } else {
                $logData .= print_r($message, true);
            }
        }
        
        $lines = explode(PHP_EOL, $logData);
        
        foreach ($lines as $line) {
            self::$logger->log($priority, $line);
        }
    }
}

/* EOF */