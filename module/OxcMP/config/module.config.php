<?php

namespace OxcMP;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;
use Zend\Config\Config;
use OxcMP\Service;

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
            'oxcmp' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/oxcmp[/:action]',
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
        ],
        'aliases' => [
            'getService' => Controller\Plugin\GetService::class,
            'translate' => Controller\Plugin\Translate::class,
        ]
    ],
    // TODO: check if there is an already defined way of retrieving the module config as a Config instance
    'service_manager' => [
        'factories' => [
            Config::class => Service\Factory\ConfigFactory::class
        ],
        // use lowerCamelCase for local service names
        'aliases' => [
            'cfg' => Config::class
        ]
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
    'layout' => [ // TODO: Add local fallback for CSS and JS
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
        ]
    ]
];
