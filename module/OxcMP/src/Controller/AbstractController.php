<?php

/*
 * Copyright © 2016-2017 OpenXcom Mod Portal Developers
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

namespace OxcMP\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\View\Model\ViewModel;
use OxcMP\Util\Log;

/**
 * Collection of useful elements
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * @method string translate(string $string The string to translate, mixed $values Additional values to format the translated string with) Translate a string
 * @method FlashMessenger flashMessenger() The flash messenger
 * @method void addPageTitle(string $title The title to add) Add a title to the page
 * @method string escapeHtml(string $string The string to escape) Escape the HTML characters from the string
 */
class AbstractController extends AbstractActionController {
    
    /**
     * Namespace for sessions
     */
    const SESSION_NAMESPACE = 'OxcMpSession';

    /**
     * The view
     * @var ViewModel
     */
    protected $view;
    
    /**
     * Class initialization
     */
    function __construct()
    {
        $this->view = new ViewModel();
    }

    /**
     * Set the "flashMessage" view value with the last message from the
     * flash messenger as a list with two keys:
     * - success: If this is a success or error message
     * - message: The actual message
     * 
     * @return void
     */
    protected function setViewFlashMessage()
    {
        Log::info('Looking for flash messages in the flash messenger');
        
        // Messages go to the view model
        $viewModel = $this->getEvent()->getViewModel();

        if ($this->flashMessenger()->hasErrorMessages()) {
            // Errors first
            $messages = $this->flashMessenger()->getErrorMessages();
            $flashMessage = [
                'success' => false,
                'message' => end($messages)
            ];
            
            $viewModel->flashMessage = $flashMessage;
            
            Log::debug('Error message found in the flash messenger: ', $flashMessage['message']);
        } elseif ($this->flashMessenger()->hasSuccessMessages()) {
            // Success afterwards
            $messages = $this->flashMessenger()->getSuccessMessages();
            $flashMessage = [
                'success' => true,
                'message' => end($messages)
            ];
            
            $viewModel->flashMessage = $flashMessage;
            
            Log::debug('Success message found in the flash messenger: ', $flashMessage['message']);
        } else {
            Log::debug('No flash messages in the flash messenger');
        }
    }
}
