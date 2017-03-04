<?php

namespace OxcMP;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Log\Logger;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\RemoteAddr;
use Zend\Session\Validator\HttpUserAgent;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\DBAL\Driver\PDOMySql\Driver as PDOMySqlDriver;
use OxcMP\Controller\AbstractController;
use OxcMP\Service;
use OxcMP\View;

/** !!! PRIVATE CONFIGURATION - DO NOT MODIFY !!! **/
return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'login' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/login/[:memberId/:authenticationToken]',
                    'constraints' => [
                        // All numeric: 123
                        'memberId' => '[0-9]*',
                        // MD5 hash, with a bit of formating: 2c797f70-d4c3b6b3-dbe4d500-71a94b04
                        'authenticationToken' => '[a-z0-9]{8}-[a-z0-9]{8}-[a-z0-9]{8}-[a-z0-9]{8}'
                    ],
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'login',
                    ],
                ],
            ],
            'logout' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/logout[/]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'logout',
                    ],
                ],
            ],
            'my-mods' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/my-mods',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            Controller\Plugin\GetService::class => InvokableFactory::class,
            Controller\Plugin\Translate::class => InvokableFactory::class,
            Controller\Plugin\Session::class => InvokableFactory::class,
        ],
        'aliases' => [
            'getService' => Controller\Plugin\GetService::class,
            'translate' => Controller\Plugin\Translate::class,
            'session' => Controller\Plugin\Session::class
        ]
    ],
    'service_manager' => [
        'factories' => [
            // Authentication
            Service\Authentication\AuthenticationAdapter::class => Service\ServiceFactory::class,
            Service\Authentication\AuthenticationService::class => Service\ServiceFactory::class,
            // Config
            Service\Config\ConfigService::class => Service\ServiceFactory::class,
            // User
            Service\User\UserPersistenceService::class => Service\ServiceFactory::class,
            Service\User\UserRetrievalService::class   => Service\ServiceFactory::class,
            Service\User\UserRemoteService::class      => Service\ServiceFactory::class,
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
            View\Helper\MainMenu::class => InvokableFactory::class,                    
        ],
       'aliases' => [
            'mainMenu' => View\Helper\MainMenu::class
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
                'href' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css',
                'integrity' => 'sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ',
                'crossorigin' => 'anonymous',
            ],
        ],
        'js' => [
            'jquerySlimMin' => [
                'src' => 'https://code.jquery.com/jquery-3.1.1.slim.min.js',
                'integrity' => 'sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n',
                'crossorigin' => 'anonymous'
            ],
            'tetherMin' => [
                'src' => 'https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js',
                'integrity' => 'sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb',
                'crossorigin' => 'anonymous'
            ],
            'bootstrapMin' => [
                'src' => 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js',
                'integrity' => 'sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn',
                'crossorigin' => 'anonymous'
            ],
        ],
        'defaultBackground' => 'img/bg-default.png',
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
        // Session cookie will expire in 1 hour.
        'cookie_lifetime' => 60*60*1,     
        // Session data will be stored on server maximum for 30 days.
        'gc_maxlifetime'     => 60*60*24*30, 
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
    'userRemote' => [
        'tokenCheckDelay' => 60 * 15,
        'displayRefreshDelay' => 60 * 60 * 2
    ]
];
