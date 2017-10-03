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

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use OxcMP\Entity\Mod;
use OxcMP\Service\Mod\ModRetrievalService;
use OxcMP\Service\Mod\ModPersistenceService;
use Zend\Authentication\AuthenticationService;
use OxcMP\Util\Log;

/**
 * Handle user mod-related actions
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModController extends AbstractController
{
    /**
     * The authentication service
     * @var AuthenticationService
     */
    private $authenticationService;
    
    /**
     * The mod retrieval service
     * @var ModRetrievalService 
     */
    private $modRetrievalService;
    
    /**
     * The mod persistence service
     * @var ModPersistenceService 
     */
    private $modPersistenceService;
    
    /**
     * Class initialization
     * 
     * @param AuthenticationService $authenticationService The authentication service
     * @param ModRetrievalService   $modRetrievalService   The mod retrieval service
     */
    function __construct(
        AuthenticationService $authenticationService,
        ModRetrievalService $modRetrievalService,
        ModPersistenceService $modPersistenceService
    ) {
        parent::__construct();
        
        $this->authenticationService = $authenticationService;
        $this->modRetrievalService   = $modRetrievalService;
        $this->modPersistenceService = $modPersistenceService;
    }

    /**
     * List all mods belonging to the user
     * 
     * @return ViewModel
     */
    public function myModsAction()
    {
        Log::info('Processing mod/my-mods action');
        
        $mods = $this->modRetrievalService->getModsByUser($this->authenticationService->getIdentity(), false);
        
        $this->setLayoutData(null, $this->translate('page_mymods_title'), $this->buildMyModsDescriptionText($mods));
        
        $this->view->setVariable('mods', $mods);
        
        return $this->view;
    }
    
    /**
     * Create a new mod
     * 
     * @return JsonModel
     */
    public function addModAction()
    {
        Log::info('Processing mod/add-mod action');
        
        // Go to MyMods if the request is not AJAX
        if (!$this->getRequest()->isXmlHttpRequest()) {
            Log::notice('Request is not AJAX, ignoring');
            return $this->redirect()->toRoute('my-mods');
        }
        
        $result = [
            'success' => false,
            'modUrl' => null,
            'errorMessage' => null
        ];
        
        $modTitle = (new SupportCode\ModFilter())->buildModTitleFilter()->filter(
            $this->getRequest()->getPost('modTitle', '')
        );
        
        Log::debug('Received mod title ', var_export($modTitle, true));
        
        // Validate mod name
        $validator = (new SupportCode\ModValidator())->buildModTitleValidator();
        
        if (!$validator->isValid($modTitle)) {
            $errorMessages = $validator->getMessages();
            
            $result['errorMessage'] = $this->translate(reset($errorMessages));
            
            return new JsonModel($result);
        }
        
        $mod = $validator = (new SupportCode\ModTranslator())->createMod(
            $modTitle,
            $this->authenticationService->getIdentity()
        );
        
        try {
            $this->modPersistenceService->createMod($mod);
        } catch (\Exception $exc) {
            Log::error('Unexpected error: ', $exc->getMessage());
            
            $result['errorMessage'] = $this->translate('page_mymods_create_error_unknown');
            return new JsonModel($result);
        }
        
        $result['success'] = false;
        return new JsonModel($result);
    }
    
    /**
     * Build the description text for the MyMods page
     * 
     * @param array $mods The mods
     * #return string
     */
    private function buildMyModsDescriptionText(array $mods)
    {
        Log::info('Building MyMods page description text');
        
        // Count published and unpublished mods
        $published = $unpublished = 0;
        
        /* @var $mod Mod */
        foreach ($mods as $mod) {
            if ($mod->getIsPublished()) {
                $published++;
            } else {
                $unpublished++;
            }
        }
        
        Log::debug('There are ', $published, ' published mod(s) and ', $unpublished, ' mod(s) by this user');
        
        $translationKey = '';
        
        if ($published == 0 && $unpublished == 0) {
            $translationKey = 'page_mymods_description_no_mods';
        } elseif ($published != 0 && $unpublished == 0) {
            $translationKey = 'page_mymods_description_ony_published_mods';
        } elseif ($published == 0 && $unpublished !=0 ) {
            $translationKey = 'page_mymods_description_ony_unpublished_mods';
        } else {
            $translationKey = 'page_mymods_description_published_and_unpublished_mods';
        }
        
        $translation = $this->translate($translationKey);
        
        Log::debug('MyMods page description text is: ', $translation);
        
        return $translation;
    }
}

/* EOF */