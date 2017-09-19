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
use OxcMP\Entity\User;
use OxcMP\Entity\Mod;
use OxcMP\Util\Log;

/**
 * Collection of useful elements
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * @method string translate(string $string The string to translate, mixed $values Additional values to format the translated string with) Translate a string
 * @method FlashMessenger flashMessenger() The flash messenger
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
    public function __construct()
    {
        $this->view = new ViewModel();
    }
    
    /**
     * Set the "flashMessage" layout value with the last message from the
     * flash messenger as a list with two keys:
     * - success (boolean): If this is a success or error message
     * - message (string):  The actual message
     * 
     * @return void
     */
    protected function setLayoutFlashMessage()
    {
        Log::info('Looking for flash messages in the flash messenger');
        
        // Messages go to the layout
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
    
    /**
     * Set the Open Graph values for the layout. By default, it sets the standard
     * OG values for the portal, should be called as needed in various controller
     * actions
     * 
     * @param Mod|User|null $entity      The entity to set the data for
     * @param string        $title       Custom title for portal pages
     * @param string        $description Custom description for portal pages
     * @return void
     */
    protected function setLayoutData($entity = null, $title = null, $description = null)
    {
        Log::info('Setting layout data values');
        
        $viewHelperManager = $this->getEvent()->getApplication()->getServiceManager()->get('ViewHelperManager');
        
        $staticUrl = $viewHelperManager->get('staticUrl');
        
        // Data
        $dUrl = $dTitle = $dDescription = $dImage = null;
        
        if ($entity instanceof User) {
            Log::debug('Setting data values for user');
            
            $dUrl = $entity->getMemberId(); // TODO: build the complete URL once the user page is created
            $dTitle = $entity->getRealName();
            $dDescription = $entity->getPersonalText();
            $dImage = $entity->getAvatarUrl();
        } elseif ($entity instanceof Mod) {
            Log::debug('Setting data values for mod');
            
            $dUrl = $entity->getSlug(); // TODO: build the complete URL once the mod page is created
            $dTitle = $entity->getTitle();
            $dDescription = $entity->getSummary();
            $dImage = null; // TODO: build the url to the mod image
        } else {
            Log::debug('Setting data values for portal');
            
            $dUrl = $this->getRequest()->getUriString();
            $dTitle = isset($title) ? $title : $this->translate('global_application_name');
            $dDescription = isset($description) ? $description : $dTitle;
            $dImage = $staticUrl('android-chrome-512x512.png'); // TODO: use a separate resource for OG?
        }
        
        $openGraph = [
            'url' => $dUrl,
            'title' => $dTitle,
            'description' => $dDescription,
            'image' => $dImage
        ];
        
        Log::debug('OG set to: ', $openGraph);
        $this->getEvent()->getViewModel()->openGraph = $openGraph;
        
        $headerData = [
            'title' => $dTitle,
            'description' => $dDescription
        ];
        
        Log::debug('Header data set to: ', $headerData);
        $this->getEvent()->getViewModel()->headerData = $headerData;
        
        Log::debug('Page title set to: ', $title);
        $this->getEvent()->getViewModel()->pageTitle = $title;
        
        Log::debug('Done setting layout data values');
    }
}
