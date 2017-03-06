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
 * Create an alert message
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class AlertMessage extends AbstractViewHelper
{
    /**
     * Render an alert message
     * 
     * @param array $message The message to render, having two keys
     *                       - success: Boolean TRUE for success, FALSE for error
     *                       - message: The actual message
     */
    public function render(array $message)
    {
        $m = '';
        
        $searchReplace = [
            '{$alertClass}' => $message['success'] ? 'alert-success' : 'alert-danger',
            '{$close}' => $this->translate('global_close'),
        ];
        
        $m .= '<div class="alert {$alertClass} alert-dismissible fade show" role="alert">' . PHP_EOL;
        $m .= '    <button type="button" class="close" data-dismiss="alert" aria-label="{$close}">' . PHP_EOL;
        $m .= '        <span aria-hidden="true">&times;</span>' . PHP_EOL;
        $m .= '    </button>' . PHP_EOL;
        $m .= '    ' . $message['message'] . PHP_EOL;
        $m .= '</div>';
        
        return $this->renderTemplate($m, $searchReplace);
    }
}

/* EOF */