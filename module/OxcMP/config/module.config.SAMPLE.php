<?php

namespace OxcMP;

use Doctrine\DBAL\Driver\PDOMySql\Driver as PDOMySqlDriver;
use Zend\Log\Logger;

return [
    // Database connection
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                // Driver type
                'driverClass' => PDOMySqlDriver::class,
                // Connection parameters
                'params' => [
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => '',
                    'password' => '',
                    'dbname'   => '',
                ]
            ]
        ]
    ],
    // Application logging
    'log' => [
        // Location where to write the log, eg: /tmp/oxcmp.log
        // Set to NULL to disable logging, eg: 'stream' => null
        'stream' => null,
        // Log only messages having this priority or lower
        // See Zend\Log\Logger for possible values
        'priority' => Logger::WARN
    ]
];