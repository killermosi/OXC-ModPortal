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
use OxcMP\Controller\AbstractController;
use OxcMP\Factory\ModuleFactory;

/** !!! PRIVATE CONFIGURATION - DO NOT MODIFY !!! **/
return [
    'router' => require('module.config.router.php'),
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => ModuleFactory::class,
            Controller\UserController::class  => ModuleFactory::class,
            Controller\ModController::class   => ModuleFactory::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\Translate::class => InvokableFactory::class,
            Controller\Plugin\AddPageTitle::class => InvokableFactory::class,
            Controller\Plugin\EscapeHtml::class => InvokableFactory::class,
        ],
        'aliases' => [
            'translate' => Controller\Plugin\Translate::class,
            'escapeHtml' => Controller\Plugin\EscapeHtml::class,
        ]
    ],
    'service_manager' => [
        'factories' => [
            
            /* Local-defined services */
            // ACL
            Service\Acl\AclService::class => ModuleFactory::class,
            // Authentication
            Service\Authentication\AuthenticationAdapter::class => ModuleFactory::class,
            // Mod
            Service\Mod\ModRetrievalService::class => ModuleFactory::class,
            // Module
            Service\Module\ModuleService::class => ModuleFactory::class,
            // User
            Service\User\UserPersistenceService::class => ModuleFactory::class,
            Service\User\UserRetrievalService::class   => ModuleFactory::class,
            Service\User\UserRemoteService::class      => ModuleFactory::class,
            
            /* Framework-defined services */
            // Config
            Config::class => ModuleFactory::class,
            // Authentication
            AuthenticationService::class => ModuleFactory::class,
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
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\StaticUrl::class => ModuleFactory::class,                    
        ],
       'aliases' => [
            'staticUrl' => View\Helper\StaticUrl::class
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
    // TODO: Add local fallback for CSS and JS
    'layout' => [
        'css' => [
            'bootstrapMin' => [
                'href' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css',
                'integrity' => 'sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M'
            ],
        ],
        'js' => [
            'jquerySlimMin' => [
                'src' => 'https://code.jquery.com/jquery-3.2.1.slim.min.js',
                'integrity' => 'sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN'
            ],
            'popperMin' => [
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js',
                'integrity' => 'sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4'
            ],
            'bootstrapMin' => [
                'src' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js',
                'integrity' => 'sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1'
            ],
        ],
        'defaultBackground' => 'img/bg-default.png',
        'oAuthUrl' => 'https://openxcom.org/forum/index.php?action=oxcmpoauth;board,',
        'githubProjectUrl' => 'https://github.com/killermosi/OXC-ModPortal',
        'staticStorageUrl' => null
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
                    UuidType::NAME => UuidType::class
                ]
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
        'priority' => Logger::WARN
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
];