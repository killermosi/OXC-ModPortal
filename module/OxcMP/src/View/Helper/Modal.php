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
     * The oAuth URL
     * @var string
     */
    private $oauthUrl;
    
    /**
     * Set the oAuth URL
     * 
     * @param string $url The URL
     * @return $this
     */
    public function setOauthUrl($url)
    {
        $this->oauthUrl = $url;
        return $this;
    }
    
    /**
     * Create the login modal
     * 
     * @return string
     */
    public function renderLoginModal()
    {
        $m = '';
        
        $searchReplace = [
            '{$close}' => $this->translate('global_close'),
            '{$title}' => $this->translate('login_modal_title'),
            '{$description}' => $this->translate('login_modal_description'),
            '{$oAuthUrl}' => $this->oauthUrl,
            '{$oAuthTxt}' => $this->translate('login_modal_oauth_link_txt')
        ];
        
        $m .= '<div class="modal fade" id="loginModal" tabindex="-1" role="dialog" aria-labelledby="loginModalLabel" aria-hidden="true">';
        $m .= '    <div class="modal-dialog" role="document">';
        $m .= '        <div class="modal-content">';
        $m .= '            <div class="modal-header">';
        $m .= '                <h5 class="modal-title" id="loginModalLabel">{$title}</h5>';
        $m .= '                <button type="button" class="close" data-dismiss="modal" aria-label="{$close}">';
        $m .= '                    <span aria-hidden="true">&times;</span>';
        $m .= '                </button>';
        $m .= '            </div>';
        $m .= '            <div class="modal-body">';
        $m .= '                <p class="text-center">{$description}</p>';
        $m .= '                <p class="text-center">';
        $m .= '                    <a href="{$oAuthUrl}" class="btn btn-primary">{$oAuthTxt}</a>';
        $m .= '                </p>';
        $m .= '            </div>';
        $m .= '            <div class="modal-footer">';
        $m .= '                <button type="button" class="btn btn-secondary" data-dismiss="modal">{$close}</button>';
        $m .= '            </div>';
        $m .= '        </div>';
        $m .= '    </div>';
        $m .= '</div>';
        
        return $this->renderTemplate($m, $searchReplace);
    }
}

/* EOF */