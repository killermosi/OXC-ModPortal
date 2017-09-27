<?php

/* 
 * Copyright © 2016-2017 OpenXcom Mod Portal Contributors
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

namespace OxcMP;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use OxcMP\Service\Acl\Role;

/** !!! PRIVATE CONFIGURATION - DO NOT MODIFY !!! **/

return [
    'routes' => [
        'home' => [
            'type' => Literal::class,
            'options' => [
                'route'    => '/',
                'defaults' => [
                    'controller' => Controller\IndexController::class,
                    'action'     => 'index',
                ],
                'acl' => [Role::GUEST, Role::MEMBER, Role::ADMINISTRATOR]
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
                    'controller' => Controller\UserController::class,
                    'action'     => 'login',
                ],
                'acl' => [Role::GUEST, Role::MEMBER, Role::ADMINISTRATOR]
            ],
        ],
        'logout' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/logout[/]',
                'defaults' => [
                    'controller' => Controller\UserController::class,
                    'action'     => 'logout',
                ],
                'acl' => [Role::GUEST, Role::MEMBER, Role::ADMINISTRATOR]
            ],
        ],
        'my-mods' => [
            'type'    => Segment::class,
            'options' => [
                'route'    => '/my-mods',
                'defaults' => [
                    'controller' => Controller\ModController::class,
                    'action'     => 'my-mods',
                ],
                'acl' => [Role::MEMBER, Role::ADMINISTRATOR]
            ],
        ],
        'about' => [
            'type' => Literal::class,
            'options' => [
                'route'    => '/about',
                'defaults' => [
                    'controller' => Controller\IndexController::class,
                    'action'     => 'about',
                ],
                'acl' => [Role::GUEST, Role::MEMBER, Role::ADMINISTRATOR]
            ],
        ],
        'disclaimer' => [
            'type' => Literal::class,
            'options' => [
                'route'    => '/disclaimer',
                'defaults' => [
                    'controller' => Controller\IndexController::class,
                    'action'     => 'disclaimer',
                ],
                'acl' => [Role::GUEST, Role::MEMBER, Role::ADMINISTRATOR]
            ],
        ],
        'contact' => [
            'type' => Literal::class,
            'options' => [
                'route'    => '/contact',
                'defaults' => [
                    'controller' => Controller\IndexController::class,
                    'action'     => 'contact',
                ],
                'acl' => [Role::GUEST, Role::MEMBER, Role::ADMINISTRATOR]
            ],
        ],
    ],
];