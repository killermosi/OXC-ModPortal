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

use Ramsey\Uuid\DegradedUuid as Uuid;
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
        
        // We can expect flash messages here
        $this->setLayoutFlashMessage();
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
        
        $result = new JsonModel();
        $result->success = false;
        $result->content = null; // Error message or new mod URL
        
        $modTitle = (new SupportCode\ModFilter())->buildModTitleFilter()->filter(
            $this->getRequest()->getPost('modTitle', '')
        );
        
        Log::debug('Received mod title ', var_export($modTitle, true));
        
        // Validate mod name
        $validator = (new SupportCode\ModValidator())->buildModTitleValidator();
        
        if (!$validator->isValid($modTitle)) {
            $errorMessages = $validator->getMessages();
            
            $result->content = $this->translate(reset($errorMessages));
            return $result;
        }
        
        $mod = $validator = (new SupportCode\ModTranslator())->createMod(
            $modTitle,
            $this->authenticationService->getIdentity()
        );
        
        try {
            $this->modPersistenceService->createMod($mod);
        } catch (\Exception $exc) {
            Log::error('Unexpected error: ', $exc->getMessage());
            
            $result->content = $this->translate('page_mymods_create_error_unknown');
            return $result;
        }
        
        // Everything went OK, build the mod edit URL
        $result->success = true;
        $result->content = $this->url()->fromRoute(
            'edit-mod',
            ['modUuid' =>  $mod->getId()->toString()],
            ['force_canonical' => true]
        );

        return $result;
    }
    
    /**
     * Display the mod edit form
     * 
     * @return mixed
     */
    public function editModAction()
    {
        Log::info('Processing mod/edit-mod action');
        
        // Validate the mod UUID
        $modUuid = $this->params()->fromRoute('modUuid', null);
        
        Log::debug('Received mod UUID: "', $modUuid, '"');
        
        $validator = (new SupportCode\ModValidator())->buildModUuidValidator();
        
        if (!$validator->isValid($modUuid)) {
            Log::notice('The mod UUID "', $modUuid, '" is invalid, redirecting to "my-mods" page');
            // Simply show the "not found" message, no need for additional details
            $this->flashMessenger()->addErrorMessage($this->translate('page_editmod_mod_not_found'));
            return $this->redirect()->toRoute('my-mods');
        }
        
        // Retrieve the mod from the database
        $mod = $this->modRetrievalService->findModById(Uuid::fromString($modUuid));
        
        if (!$mod instanceof Mod) {
            Log::notice('The mod having the UUID "', $modUuid, '" could not be found, redirecting to "my-mods" page');
            $this->flashMessenger()->addErrorMessage($this->translate('page_editmod_mod_not_found'));
            return $this->redirect()->toRoute('my-mods');
        }
        
        // Only administrators can edit mods that they don't own
        if (false == $this->authenticationService->getIdentity()->getIsAdministrator()
            && $mod->getId() != $this->authenticationService->getIdentity()->getId()
        ) {
            Log::notice(
                'Non-administrator user attempted to edit the mod having the UUID "',
                $modUuid,
                '", redirecting to "my-mods"'
            );
            $this->flashMessenger()->addErrorMessage($this->translate('page_editmod_mod_not_found'));
            return $this->redirect()->toRoute('my-mods');
        }
        
        $this->setLayoutData(
            null,
            $this->translate('page_editmod_title'),
            $this->translate('page_editmod_description')
        );
        
        // Assign data to view
        $this->view->mod = $mod;
        
        return $this->view;
    }
    
    /**
     * Preview the mod slug
     * 
     * @return JsonModel
     */
    public function previewModSlugAction()
    {
        Log::info('Processing mod/preview-mod-slug action');
        
        // Since this is a utility method, we don't really care if the received data is invalid
        // (we do validate it, to avoid unnecessary operations), so we return an empty response
        // in that case
        
        $result = new JsonModel();
        $result->slug = null;
        
        // Validators and filters
        $filter = new SupportCode\ModFilter();
        $validator = new SupportCode\ModValidator();
        
        // Collect data
        $modId = $this->getRequest()->getPost('id', '');
        $modTitle = $filter->buildModTitleFilter()->filter(
            $this->getRequest()->getPost('title', '')
        );
        
        // Validate
        if (!$validator->buildModUuidValidator()->isValid($modId)) {
            Log::notice('Received invalid mod UUID: "', $modId, '"');
            return $result;
        }
        
        if (!$validator->buildModTitleValidator()->isValid($modTitle)) {
            Log::notice('Received invalid mod title: "', $modTitle, '"');
            return $result;
        }
        
        // Retrieve the mod
        $mod = $this->modRetrievalService->findModById(Uuid::fromString($modId));
        
        if (!$mod instanceof Mod) {
            Log::notice('Could not find a mod having the UUID ', $modId);
            return $result;
        }
        
        // Check that the user owns the mod, for completion sake
        if (false == $this->authenticationService->getIdentity()->getIsAdministrator()
            && $mod->getId() != $this->authenticationService->getIdentity()->getId()
        ) {
            Log::notice(
                'Non-administrator user attempted to preview the mod slug for the mod having the UUID "',
                $modId,
                '"'
            );
            return $result;
        }
        
        $result->slug = $this->modPersistenceService->buildModSlug($mod, $modTitle);
        
        return $result;
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
        
        if ($published == 0 && $unpublished == 0) {
            $translation = $this->translate('page_mymods_description_no_mods');
        } elseif ($published != 0 && $unpublished == 0) {
            $translation = $this->translate('page_mymods_description_ony_published_mods', $published);
        } elseif ($published == 0 && $unpublished !=0 ) {
            $translation = $this->translate('page_mymods_description_ony_unpublished_mods', $unpublished);
        } else {
            $translation = $this->translate(
                'page_mymods_description_published_and_unpublished_mods',
                $published,
                $unpublished
            );
        }
        
        Log::debug('MyMods page description text is: ', $translation);
        
        return $translation;
    }
}

/* EOF */