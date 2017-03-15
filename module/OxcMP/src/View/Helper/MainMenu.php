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

namespace OxcMP\View\Helper;

/**
 * Create the portal main menu/header, containing:
 * - Logo
 * - "Home" link
 * - "My Mods" link (for logged in users only)
 * - Login/logout link (depending on if the user is logged in or not)
 * - Search box
 * 
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class MainMenu extends AbstractViewHelper
{
    /**
     * Render the main menu
     * @return string
     */
    public function render($isLoggedIn)
    {
        $m = '';
        
        $homeUrl = $this->view->url('home',[], ['force_canonical' => true]);
        
        $searchReplace = [
            '{$logoSrc}' => $homeUrl . 'img/logo.svg',
            '{$navToggle}' => $this->translate('main_menu_nav_toggle'),
            '{$homeUrl}' => $homeUrl,
            '{$homeTxt}' => $this->translate('main_menu_link_home_txt'),
            '{$myModsUrl}' => $this->view->url('my-mods',[], ['force_canonical' => true]),
            '{$myModsTxt}' => $this->translate('main_menu_link_mymods_txt'),
            '{$logoutUrl}' => $this->view->url('logout',[], ['force_canonical' => true]),
            '{$logoutTxt}' => $this->translate('main_menu_link_logout_txt'),
            '{$loginUrl}' => $this->view->url('login',[], ['force_canonical' => true]),
            '{$loginTxt}' => $this->translate('main_menu_link_login_txt')
        ];
        
        $m .= '<nav class="navbar navbar-toggleable-md navbar-light bg-faded">' . PHP_EOL;
        $m .= '    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="{$navToggle}">' . PHP_EOL;
        $m .= '        <span class="navbar-toggler-icon"></span>' . PHP_EOL;
        $m .= '    </button>' . PHP_EOL;
        $m .= '    <a class="navbar-brand" href="{$homeUrl}">' . PHP_EOL;
        $m .= '        <img src="{$logoSrc}" width="48" height="48" alt="">' . PHP_EOL;
        $m .= '    </a>' . PHP_EOL;
        $m .= '    <div class="collapse navbar-collapse" id="navbarNav">' . PHP_EOL;
        $m .= '        <ul class="navbar-nav mr-auto">' . PHP_EOL;
        $m .= '            <li class="nav-item">' . PHP_EOL;
        $m .= '                <a class="nav-link" href="{$homeUrl}">{$homeTxt}</a>' . PHP_EOL;
        $m .= '            </li>' . PHP_EOL;
        
        if ($isLoggedIn) {
            $m .= '            <li class="nav-item">' . PHP_EOL;
            $m .= '                <a class="nav-link" href="{$myModsUrl}">{$myModsTxt}</a>' . PHP_EOL;
            $m .= '            </li>' . PHP_EOL;
            $m .= '            <li class="nav-item">' . PHP_EOL;
            $m .= '                <a class="nav-link" href="{$logoutUrl}">{$logoutTxt}</a>' . PHP_EOL;
            $m .= '            </li>' . PHP_EOL;
        } else {
            $m .= '            <li class="nav-item">' . PHP_EOL;
            $m .= '                <a class="nav-link" data-toggle="modal" data-target="#loginModal" href="{$loginUrl}">{$loginTxt}</a>' . PHP_EOL;
            $m .= '            </li>'. PHP_EOL;
        }
        $m .= '        </ul>' . PHP_EOL;
        //$m .= '        <form class="form-inline my-2 my-lg-0">' . PHP_EOL;
        //$m .= '            <input class="form-control mr-sm-2" type="text" placeholder="Search">' . PHP_EOL;
        //$m .= '            <button class="btn btn-outline-primary my-2 my-sm-0" type="submit">Search</button>' . PHP_EOL;
        //$m .= '        </form>' . PHP_EOL;
        $m .= '    </div>' . PHP_EOL;
        $m .= '</nav>';
        
        return $this->renderTemplate($m, $searchReplace);
    }
}

/* EOF */