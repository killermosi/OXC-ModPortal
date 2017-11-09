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

namespace OxcMP\Util\Resource;

use Zend\Config\Config;
use Zend\Log\Logger;
use Doctrine\DBAL\Logging\DebugStack;
use Ramsey\Uuid\DegradedUuid as Uuid;
use OxcMP\Util\Log;

/**
 * Handle Doctrine logging
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class DoctrineLog extends DebugStack
{
    /**
     * If logging is enabled
     * @var boolean 
     */
    private $logEnabled = false;
    
    /**
     * Class initialization
     * 
     * @param Config $config The configuration
     */
    public function __construct(Config $config) {
        if (
            $config->log->enabled == true
            && $config->log->sql == true
            && $config->log->priority == Logger::DEBUG
        ) {
            $this->logEnabled = true;
        }
    }
    
    /**
     * Logs a SQL statement somewhere
     *
     * @param string     $sql    The SQL to be executed
     * @param array|null $params The SQL parameters
     * @param array|null $types  The SQL parameter types
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null) {
        if ($this->enabled == false) {
            return;
        }
        
        parent::startQuery($sql, $params, $types);

        if ($this->logEnabled == false) {
            return;
        }

        $this->logQuery($sql, $params);
    }

    /**
     * Marks the last started query as stopped - this can be used for timing of queries
     *
     * @return void
     */
    public function stopQuery() {
        if ($this->enabled == false) {
            return;
        }
        
        parent::stopQuery();
        
        if ($this->logEnabled == false) {
            return;
        }
        
        $executionTime = $this->queries[$this->currentQuery]['executionMS'];
        Log::debug('Execution time: ', number_format($executionTime, 5), ' seconds');
    }
    
    /**
     * Log a SQL query before execution
     * 
     * @param string     $sql    The SQL to be executed
     * @param array|null $params The SQL parameters
     * @return void
     */
    private function logQuery($sql, array $params = null) {
        // This is the easy part - if no parameters are provided, just log the query as-is
        if (empty($params)) {
            Log::debug('Executing query: ', $sql, ';');
            return;
        }

        // "Hydrate" the query, so that the  "raw" query can be logged
        $convertedSql = str_replace('?', '%s', $sql);
        $convertedParams = [];
        
        foreach ($params as $param) {
            $convertedParams[] = $this->convertToDatabaseValue($param);
        }
        
        Log::debug('Executing query: ', sprintf($convertedSql, ...$convertedParams), ';');
    }
    
    /**
     * Convert a parameter to its database representation
     * TODO: This manual conversion should not be needed, as it can be handled by doctrine, as explained here:
     * https://stackoverflow.com/questions/2095394/doctrine-how-to-print-out-the-real-sql-not-just-the-prepared-statement/18641582#18641582
     * However, when that solution was implemented this happened:
     * https://stackoverflow.com/questions/46917767/doctrine-2-5-query-logging-unknown-column-type-2-requested
     * Hence the manual conversion
     * 
     * @param mixed $parameter The parameter
     * @return mixed
     */
    private function convertToDatabaseValue($parameter)
    {
        // Array
        if (is_array($parameter)) {
            $result = [];
            
            foreach ($parameter as $value) {
                $result[] = $this->convertToDatabaseValue($value);
            }
            
            return implode(', ', $result);
        }
        
        // UUID
        if ($parameter instanceof Uuid) {
            return var_export($parameter->toString(), true);
        }
        
        if ($parameter instanceof \DateTime) {
            return var_export($parameter->format('Y-m-d H:i:s'));
        }
        
        // Everything else (string, number, UFOs...)
        return var_export($parameter, true);
    }
}
