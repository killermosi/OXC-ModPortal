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
 * Generate several Bootstrap modals
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class Modal extends AbstractViewHelper
{
    /**
     * Create the login modal
     * 
     * @return string
     */
    public function renderLoginModal($oauthUrl)
    {
        $m = '';
        
        $searchReplace = [
            '{$close}' => $this->translate('global_close'),
            '{$title}' => $this->translate('login_modal_title'),
            '{$description}' => $this->translate('login_modal_description'),
            '{$oAuthUrl}' => $this->oauthUrl,
            '{$oAuthTxt}' => $this->translate('login_modal_oauth_link_txt')
        ];
        
        $m .= '<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">' . PHP_EOL;
        $m .= '    <div class="modal-dialog" role="document">' . PHP_EOL;
        $m .= '        <div class="modal-content">' . PHP_EOL;
        $m .= '            <div class="modal-header">' . PHP_EOL;
        $m .= '                <h5 class="modal-title" id="loginModalLabel">{$title}</h5>' . PHP_EOL;
        $m .= '                <button type="button" class="close" data-dismiss="modal" aria-label="{$close}">' . PHP_EOL;
        $m .= '                    <span aria-hidden="true">&times;</span>' . PHP_EOL;
        $m .= '                </button>' . PHP_EOL;
        $m .= '            </div>' . PHP_EOL;
        $m .= '            <div class="modal-body">' . PHP_EOL;
        $m .= '                <p class="text-center">{$description}</p>' . PHP_EOL;
        $m .= '                <p class="text-center">' . PHP_EOL;
        $m .= '                    <a href="{$oAuthUrl}" class="btn btn-primary">{$oAuthTxt}</a>' . PHP_EOL;
        $m .= '                </p>' . PHP_EOL;
        $m .= '            </div>' . PHP_EOL;
        $m .= '            <div class="modal-footer">' . PHP_EOL;
        $m .= '                <button type="button" class="btn btn-secondary" data-dismiss="modal">{$close}</button>' . PHP_EOL;
        $m .= '            </div>' . PHP_EOL;
        $m .= '        </div>' . PHP_EOL;
        $m .= '    </div>' . PHP_EOL;
        $m .= '</div>';
        
        return $this->renderTemplate($m, $searchReplace);
    }
}

/* EOF */