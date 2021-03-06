<?php

namespace OxcMP;

use Zend\Log\Logger;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Config\Config;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\DBAL\Driver\PDOMySql\Driver as PDOMySqlDriver;
use Ramsey\Uuid\Doctrine\UuidType;
use Predis\Client as RedisClient;
use OxcMP\Controller\AbstractController;
use OxcMP\Factory\ModuleFactory;
use OxcMP\Util\Resource\DoctrineUtcDateTimeType;

/** !!! PRIVATE CONFIGURATION - DO NOT MODIFY !!! **/
return [
    'router' => require('module.config.router.php'),
    'controllers' => [
        'factories' => [
            Controller\IndexController::class             => ModuleFactory::class,
            Controller\UserController::class              => ModuleFactory::class,
            Controller\ModFileManagementController::class => ModuleFactory::class,
            Controller\ModFileController::class           => ModuleFactory::class,
            Controller\ModManagementController::class     => ModuleFactory::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\Translate::class    => InvokableFactory::class,
            Controller\Plugin\AddPageTitle::class => InvokableFactory::class,
            Controller\Plugin\EscapeHtml::class   => InvokableFactory::class,
        ],
        'aliases' => [
            'translate' => Controller\Plugin\Translate::class,
            'escapeHtml' => Controller\Plugin\EscapeHtml::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            
            /* Local services */
            // ACL
            Service\Acl\AclService::class                       => ModuleFactory::class,
            // Authentication
            Service\Authentication\AuthenticationAdapter::class => ModuleFactory::class,
            // Markdown
            Service\Markdown\MarkdownService::class             => ModuleFactory::class,
            // Mod
            Service\Mod\ModRetrievalService::class              => ModuleFactory::class,
            Service\Mod\ModPersistenceService::class            => ModuleFactory::class,
            // ModFile
            Service\ModFile\ModFileRetrievalService::class      => ModuleFactory::class,
            // ModTag
            Service\ModTag\ModTagRetrievalService::class        => ModuleFactory::class,
            // Module
            Service\Module\ModuleService::class                 => ModuleFactory::class,
            // Quota
            Service\Quota\QuotaService::class                   => ModuleFactory::class,
            // Storage
            Service\Storage\ImageService::class                 => ModuleFactory::class,
            Service\Storage\StorageOptions::class               => ModuleFactory::class,
            Service\Storage\StorageService::class               => ModuleFactory::class,
            // Tag
            Service\Tag\TagRetrievalService::class              => ModuleFactory::class,
            // User
            Service\User\UserPersistenceService::class          => ModuleFactory::class,
            Service\User\UserRetrievalService::class            => ModuleFactory::class,
            Service\User\UserRemoteService::class               => ModuleFactory::class,
            
            /* External services */
            // Config
            Config::class                                       => ModuleFactory::class,
            // Authentication
            AuthenticationService::class                        => ModuleFactory::class,
            // Redis client
            RedisClient::class                                  => ModuleFactory::class,
            /* Utility resources */
            // Doctrine log
            Util\Resource\DoctrineLog::class                    => ModuleFactory::class
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'oxcmp/index/index'       => __DIR__ . '/../view/oxcmp/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy'
        ]
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\DefaultBackgroundUrl::class => ModuleFactory::class,
            View\Helper\ModBackgroundUrl::class     => ModuleFactory::class,
            View\Helper\ModImageUrl::class          => ModuleFactory::class,
            View\Helper\StaticUrl::class            => ModuleFactory::class,
        ],
       'aliases' => [
            'defaultBackgroundUrl' => View\Helper\DefaultBackgroundUrl::class,
            'modBackgroundUrl'     => View\Helper\ModBackgroundUrl::class,
            'modImageUrl'          => View\Helper\ModImageUrl::class,
            'staticUrl'            => View\Helper\StaticUrl::class,
       ]
    ], 
    'translator' => [
        'locale' => 'en_US',
        'translation_file_patterns' => [
            [
                'type'     => 'phparray',
                'base_dir' => __DIR__ . '/../locales',
                'pattern'  => '%s.php',
            ],
        ],
    ],
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                // Driver type
                'driverClass' => PDOMySqlDriver::class,
                // Connection parameters
                'params' => [
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => null,
                    'password' => null,
                    'dbname'   => null,
                ]
            ]
        ],
        'configuration' => [
            'orm_default' => [
                'types' => [
                    UuidType::NAME => UuidType::class,
                    'datetime'     => DoctrineUtcDateTimeType::class,
                    'datetimez'    => DoctrineUtcDateTimeType::class
                ],
                'sql_logger' => Util\Resource\DoctrineLog::class
            ]
        ],
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],
    // TODO: Add local fallback for CSS and JS
    'layout' => [
        'css' => [
            'bootstrapMin' => [
                'href' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css',
                'integrity' => 'sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4'
            ],
        ],
        'js' => [
            'bootstrapMin' => [
                'src' => 'https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js',
                'integrity' => 'sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm'
            ],
            'popperMin' => [
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js',
                'integrity' => 'sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ'
            ],
            'jqueryMin' => [
                'src' => 'https://code.jquery.com/jquery-3.3.1.min.js',
                'integrity' => 'sha384-tsQFqpEReu7ZLhBV2VZlAu7zcOV+rXbYlF2cqB8txI/8aZajjp4Bqd+V6D5IgvKT'
            ],
        ],
        'defaultBackground' => 'img/bg-default.png',
        'oAuthUrl' => 'https://openxcom.org/forum/index.php?action=oxcmpoauth;board,',
        'githubProjectUrl' => 'https://github.com/killermosi/OXC-ModPortal',
        'staticStorageUrl' => null,
        'gitHubFlavoredMarkdownGuideUrl' => 'https://guides.github.com/features/mastering-markdown/'
    ],
    // Session configuration.
    'session_config' => [
        'cookie_lifetime' => 60*60*24,     
        'gc_maxlifetime'  => 60*60*24*7,
        'cookie_path'     => '/',
        'cookie_httponly' => true,
        'name'            => 'OXCMPSession'
    ],
    // Session manager configuration.
    'session_manager' => [
        // Session validators (used for security).
        'validators' => [
            RemoteAddr::class,
            HttpUserAgent::class,
        ]
    ],
    // Session storage configuration.
    'session_storage' => [
        'type' => SessionArrayStorage::class
    ],
    'session_containers' => [
        AbstractController::SESSION_NAMESPACE
    ],
    // Application logging
    'log' => [
        'enabled' => false,
        'stream' => '/tmp/ocxmp.log',
        'priority' => Logger::WARN,
        'sql' => false,
    ],
    'oxcForumApi' => [
        'url' => 'https://www.openxcom.org/OxcMpOauth.php',
        'key' => null,
        'header' => 'ApiKey',
        'basicAuth' => [
            'user' => null,
            'pass' => null
        ]
    ],
    'userRemote' => [ // All values are in seconds
        'tokenCheckDelay' => 60 * 15, // 15 minutes
        'displayRefreshDelay' => 60  * 60 * 2, // 2 hours
        'rememberMe' => 60 * 60 * 24 * 14 // 14 days
    ],
    // Storage configuration (all limits are in MB)
    'storage' => [
        // Mode to use for newly created directories
        'mode' => 0775,
        // Where to store the mod files
        'mod' => '/tmp/oxcmp/data',
        // Where to cache the mod images, can be null to disable the cache (NOT recommended)
        'cache' => null,
        // Where to store temporary files
        'temp' => '/tmp/oxcmp/',
        // Max total storage allowed per user/mod
        'quota' => [
            'freeSpace' => 1024 * 25, // 25 GB
            'user' => 1024 * 5, // 5GB
            'mod' => 1024 // 1 GB
        ],
        // Max upload file size
        'maxFileSize' => [
            'image' => 10,
            'resource' => 512
        ],
        // Background image size, in pixels
        'background' => [
            'width' => 1700,
            'height' => 700
        ],
        // Gallery image sizes, in pixels, for each bootstrap breakpoint
        'imageSize' =>[
            'b575' => '527x296', // One card per row
            'b767' => '222x125',
            'b991' => '192x108',
            'b1199' => '237x133' // Four cards per row
        ],
        'backgroundGradient' => dirname(__DIR__) . '/resource/background/gradient.png',
        'fileLock' => [
            // Lock timeout for a file, in case the lock is not properly removed, in seconds
            'timeout' => 30,
            // How fast to retry if the attempt fails, in seconds
            'retryDelay'=> 1
        ]
    ],
    // Upload settings
    'upload' => [
        'safetyMargin' => 4,
        'chunkSize' => 5,
        'throttling' => 0
    ],
    // Redis settings
    'redis' => [
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => '6379',
        'database' => 13
    ]
];

/* EOF */
