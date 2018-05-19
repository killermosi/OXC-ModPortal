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
use Zend\Authentication\AuthenticationService;
use Zend\Config\Config;
use OxcMP\Entity\Mod;
use OxcMP\Entity\ModFile;
use OxcMP\Entity\ModTag;
use OxcMP\Service\Markdown\MarkdownService;
use OxcMP\Service\Mod\ModRetrievalService;
use OxcMP\Service\Mod\ModPersistenceService;
use OxcMP\Service\ModFile\ModFileRetrievalService;
use OxcMP\Service\ModTag\ModTagRetrievalService;
use OxcMP\Service\Tag\TagRetrievalService;
use OxcMP\Util\Log;

/**
 * Handle mod management actions
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModManagementController extends AbstractController
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
     * The mod file retrieval service
     * @var ModFileRetrievalService
     */
    private $modFileRetrievalService;
    
    /**
     * The mod tag retrieval service
     * @var ModTagRetrievalService
     */
    private $modTagRetrievalService;
    
    /**
     * The tag retrieval service
     * @var TagRetrievalService 
     */
    private $tagRetrievalService;
    
    /**
     * The Markdown service
     * @var MarkdownService 
     */
    private $markdownService;
    
    /**
     * The configuration
     * @var Config 
     */
    private $config;
    
    /**
     * Class initialization
     * 
     * @param AuthenticationService   $authenticationService   The authentication service
     * @param ModRetrievalService     $modRetrievalService     The mod retrieval service
     * @param ModPersistenceService   $modPersistenceService   The mod persistence service
     * @param ModFileRetrievalService $modFileRetrievalService The mod tag retrieval service
     * @param ModTagRetrievalService  $modTagRetrievalService  The mod tag retrieval service
     * @param MarkdownService         $markdownService         The markdown service
     * @param Config                  $config                  The configuration
     */
    function __construct(
        AuthenticationService $authenticationService,
        ModRetrievalService $modRetrievalService,
        ModPersistenceService $modPersistenceService,
        ModFileRetrievalService $modFileRetrievalService,
        ModTagRetrievalService $modTagRetrievalService,
        TagRetrievalService $tagRetrievalService,
        MarkdownService $markdownService,
        Config $config
    ) {
        parent::__construct();
        
        $this->authenticationService   = $authenticationService;
        $this->modRetrievalService     = $modRetrievalService;
        $this->modPersistenceService   = $modPersistenceService;
        $this->modFileRetrievalService = $modFileRetrievalService;
        $this->modTagRetrievalService  = $modTagRetrievalService;
        $this->tagRetrievalService     = $tagRetrievalService;
        $this->markdownService         = $markdownService;
        $this->config                  = $config;
    }

    /**
     * List all mods belonging to the user
     * 
     * @return ViewModel
     */
    public function myModsAction()
    {
        Log::info('Processing mod-management/my-mods action');
        
        $mods = $this->modRetrievalService->getModsByUser($this->authenticationService->getIdentity(), false);
        
        // We can expect flash messages here
        $this->setLayoutFlashMessage();
        $this->setLayoutData(null, $this->translate('page_mymods_title'), $this->buildMyModsDescriptionText($mods));
        
        $this->view->mods = $mods;
        
        return $this->view;
    }
    
    /**
     * Create a new mod
     * 
     * @return JsonModel
     */
    public function addModAction()
    {
        Log::info('Processing mod-management/add-mod action');
        
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
        
        $mod = new Mod();
        $mod->setTitle($modTitle);
        $mod->setUserId = $this->authenticationService->getIdentity()->getId();
        
        try {
            $this->modPersistenceService->createMod($mod);
        } catch (\Exception $exc) {
            Log::notice('Unexpected error while creating the mod entity: ', $exc->getMessage());
            
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
        Log::info('Processing mod-management/edit-mod action');
        
        // Validate the mod UUID
        $modUuid = $this->params()->fromRoute('modUuid', null);
        
        Log::debug('Received mod UUID: "', $modUuid, '"');
        
        // Retrieve the mod from the database
        $mod = $this->modRetrievalService->getModById(Uuid::fromString($modUuid));
        
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
                'Non-administrator user attempted to edit the unowned mod having the UUID "',
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
        
        $modBackground = $this->modFileRetrievalService->getModBackground($mod);
        
        // Assign data to view
        $this->view->mod = $mod;
        $this->view->modBackground = $modBackground;
        $this->view->modImages = $this->modFileRetrievalService->getModImages($mod);
        $this->view->modResources = $this->modFileRetrievalService->getModResources($mod);
        $this->view->tags = $this->tagRetrievalService->getAllTags();
        $this->view->modTags = $this->modTagRetrievalService->getModTags($mod);
        $this->view->config = $this->config;
        
        // Some data needs to go to the layout too
        $this->getEvent()->getViewModel()->mod = $mod;
        $this->getEvent()->getViewModel()->modBackground = $modBackground;
        
        return $this->view;
    }
    
    /**
     * Save a mod
     * 
     * @return JsonModel
     */
    public function saveModAction()
    {
        Log::info('Processing mod-management/save-mod action');
        
        // Go to MyMods if the request is not AJAX
        if (!$this->getRequest()->isXmlHttpRequest()) {
            Log::notice('Request is not AJAX, ignoring');
            return $this->redirect()->toRoute('my-mods');
        }
        
        $result = new JsonModel();
        $result->success = false;
        $result->content = null; // Error message or MyMods page URL
        
        // Collect and validate update data
        $updateData = $this->collectModUpdateData();
        $updateValidator = (new SupportCode\ModValidator())->buildModUpdateValidator();
        
        // These fields are not directly editable by the user or are already validated,
        // thus should never fail validation
        $hardFail = [
            'id',
            'isPublished',
            'tags',
            'backgroundUuid',
            'images'
        ];
        
        foreach ($updateData as $fieldName => $data) {
            /* @var $validator \Zend\Validator\ValidatorChain */
            $validator = $updateValidator[$fieldName];
            
            if ($validator->isValid($data)) {
                continue;
            }
            
            if (in_array($fieldName, $hardFail)) {
                $errorMessageKey = 'global_bad_request';
            } else {
                $errorMessages = $validator->getMessages();
                $errorMessageKey = reset($errorMessages);
            }
            
            $result->content = $this->translate($errorMessageKey);
            Log::notice('Validation failed: ', $result->content);
            return $result;
        }
        
        // Retrieve the mod
        $modId = $updateData['id'];
        $mod = $this->modRetrievalService->getModById(Uuid::fromString($modId));
        
        if (!$mod instanceof Mod) {
            Log::notice('Could not find a mod having the UUID ', $modId);
            $result->content = $this->translate('global_bad_request');
            return $result;
        }
        
        // Check that the user owns the mod, for completion sake
        if (false == $this->authenticationService->getIdentity()->getIsAdministrator()
            && $mod->getId() != $this->authenticationService->getIdentity()->getId()
        ) {
            Log::notice('Non-administrator user attempted to update the mod having the UUID "', $modId, '"');
            $result->content = $this->translate('global_bad_request');
            return $result;
        }
        
        // Update the user mod data
        $mod->setTitle($updateData['title']);
        $mod->setSummary($updateData['summary']);
        $mod->setIsPublished((bool) $updateData['isPublished']);
        $mod->setDescriptionRaw($updateData['descriptionRaw']);
        
        // Update the mod description if needed
        $this->markdownService->buildModDescription($mod);
        
        // Validate the number of visible characters in the processed description,
        // as it may get stripped below the minimum limit
        if ($mod->wasDescriptionRawChanged()) {
            /* @var $descriptionValidator \Zend\Validator\ValidatorChain */
            $descriptionValidator = $updateValidator['descriptionRaw'];
            $strippedDescription = preg_replace('/\s\s+/', '', strip_tags($mod->getDescription()));
            
            if (!$descriptionValidator->isValid($strippedDescription)) {
                $errorMessages = $descriptionValidator->getMessages();
                $result->content = $this->translate(reset($errorMessages));
                Log::notice('Validation failed: ', $result->content);
                return $result;
            }
        }
        
        try {
            $this->modPersistenceService->updateMod($mod,
                $this->buildModTags($mod, $updateData['tags']),
                $this->buildModBackground($mod, $updateData['backgroundUuid']),
                $this->buildModFiles($mod, $updateData['images'], ModFile::TYPE_IMAGE),
                $this->buildModFiles($mod, $updateData['resources'], ModFile::TYPE_RESOURCE)
            );
        } catch (\Exception $exc) {
            Log::notice('Unexpected error while updating the mod entity: ', $exc->getMessage());
            $result->content = $this->translate('page_editmod_error_unknown');
            return $result;
        }
        
        // Everything went OK, build MyMods page URL
        $result->success = true;
        $result->content = $this->url()->fromRoute('my-mods', [], ['force_canonical' => true]);
        
        // Place the success message in the FlashMessenger
         $this->flashMessenger()->addSuccessMessage($this->translate('page_editmod_success'));
         
        return $result;
    }
    
    /**
     * Preview the mod description
     * 
     * @return JsonModel
     */
    public function previewModDescriptionAction()
    {
        Log::info('Processing mod-management/preview-mod-description action');
        
        // Go to MyMods if the request is not AJAX
        if (!$this->getRequest()->isXmlHttpRequest()) {
            Log::notice('Request is not AJAX, ignoring');
            return $this->redirect()->toRoute('my-mods');
        }
        
        $result = new JsonModel();
        $result->success = false;
        $result->content = null; // Error message or description preview
        
        // Validators and filters
        $validator = new SupportCode\ModValidator();
        
        // Collect data
        $modId = $this->params()->fromRoute('modUuid', null);
        $modDescriptionRaw = (new SupportCode\ModFilter())->buildModDescriptionRawFilter()->filter(
            $this->getRequest()->getPost('descriptionRaw', '')
        );
        
        // Validate mod description
        $modDescriptionValidator = $validator->buildModDescriptionRawValidator();
        if (!$modDescriptionValidator->isValid($modDescriptionRaw)) {
            Log::notice('Received invalid mod description: ', $modDescriptionValidator->getMessages());
            
            $errorMessages = $modDescriptionValidator->getMessages();
            $result->content = $this->translate(reset($errorMessages));
            return $result;
        }
        
        // Retrieve the mod
        $mod = $this->modRetrievalService->getModById(Uuid::fromString($modId));
        
        if (!$mod instanceof Mod) {
            Log::notice('Could not find a mod having the UUID ', $modId);
            return $result;
        }
        
        // Check that the user owns the mod, for completion sake
        if (false == $this->authenticationService->getIdentity()->getIsAdministrator()
            && $mod->getId() != $this->authenticationService->getIdentity()->getId()
        ) {
            Log::notice(
                'Non-administrator user attempted to preview the mod description for the mod having the UUID "',
                $modId,
                '"'
            );
            return $result;
        }
        
        // Preview the description
        $mod->setDescriptionRaw($modDescriptionRaw);
        $this->markdownService->buildModDescription($mod);
        
        $result->success = true;
        $result->content = $mod->getDescription();
        
        return $result;
    }
    
    /**
     * Preview the mod slug
     * 
     * @return JsonModel
     */
    public function previewModSlugAction()
    {
        Log::info('Processing mod-management/preview-mod-slug action');
        
        // Since this is a utility method, we don't really care if the received data is invalid
        // (we do validate it, to avoid unnecessary operations), so we return an empty response
        // in that case
        
        $result = new JsonModel();
        $result->slug = null;
        
        // Validators and filters
        $filter = new SupportCode\ModFilter();
        $validator = new SupportCode\ModValidator();
        
        // Collect data
        $modId = $this->params()->fromRoute('modUuid', null);;
        $modTitle = $filter->buildModTitleFilter()->filter(
            $this->getRequest()->getPost('title', '')
        );
        
        if (!$validator->buildModTitleValidator()->isValid($modTitle)) {
            Log::notice('Received invalid mod title: "', $modTitle, '"');
            return $result;
        }
        
        // Retrieve the mod
        $mod = $this->modRetrievalService->getModById(Uuid::fromString($modId));
        
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
        
        // Create slug preview
        $mod->setTitle($modTitle);
        $this->modPersistenceService->buildModSlug($mod);
        $result->slug = $mod->getSlug();
        
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
                $published + $unpublished,
                $unpublished
            );
        }
        
        Log::debug('MyMods page description text is: ', $translation);
        
        return $translation;
    }
    
    /**
     * Build a list of ModTag entities for a mod
     * 
     * @param Mod    $mod          The Mod entity
     * @param string $selectedTags The selected tags, comma-separated
     * @return array
     */
    private function buildModTags(Mod $mod, $selectedTags)
    {
        Log::debug('Building mod tags list');
        
        $selectedTagsList = (strlen($selectedTags) != 0) ? array_unique(explode(',', $selectedTags)): [];
        
        $tagsList = [];
        
        foreach ($selectedTagsList as $selectedTag) {
            $tag = new ModTag();
            $tag->setModId($mod->getId());
            $tag->setTag($selectedTag);
            
            $tagsList[] = $tag;
            unset($tag);
        }
        
        Log::debug('Done building mod tags list, containing', count($tagsList), ' unique tag(s)');
        
        return $tagsList;
    }
    
    /**
     * Build a mod background
     * 
     * @param Mod    $mod            The Mod entity
     * @param string $backgroundUuid The temporary UUID
     * @return ModFile
     */
    private function buildModBackground(Mod $mod, $backgroundUuid)
    {
        Log::info('Building mod background entity');
        
        $background = new ModFile();
        $background->setType(ModFile::TYPE_BACKGROUND);
        $background->setModId($mod->getId());
        $background->setUserId($this->authenticationService->getIdentity()->getId());
        $background->setName(
            (new \SplFileInfo(ModFile::BACKGROUND_NAME))->getBasename('.' . ModFile::EXTENSION_IMAGE)
        );
        
        if (!empty($backgroundUuid)) {
            Log::debug('Using temporary uploaded file: ', $backgroundUuid);
            $background->setTemporaryUuid(Uuid::fromString($backgroundUuid));
        }
        
        Log::debug('Mod background built');
        
        return $background;
    }
    
    /**
     * Build a list of mod file entities for
     * 
     * 
     * @param Mod   $mod   The mod entity
     * @param array $files The file list
     * @param int   $type  The file type: ModFile::TYPE_RESOURCE or ModFile::TYPE_IMAGE
     */
    private function buildModFiles(Mod $mod, array $files, $type)
    {
        Log::info('Building mod file list of type: ', $type == ModFile::TYPE_IMAGE ? 'image' : 'resource');
        $filesList = [];
        
        foreach ($files as  $fileData) {
            $file = new ModFile();
            $file->setType($type);
            $file->setModId($mod->getId());
            $file->setUserId($this->authenticationService->getIdentity()->getId());
            
            $file->setTemporaryUuid(Uuid::fromString($fileData['uuid']));
            $file->setDescription($fileData['description']);
            $file->setName($fileData['filename']);
            
            $filesList[] = $file;
            unset($file);
        }
        
        Log::debug('Done building mod files list, containing', count($filesList), ' files');
        return $filesList;
    }
    
    /**
     * Collect mod update data from the post
     * 
     * @return array
     */
    private function collectModUpdateData()
    {
        $filters = (new SupportCode\ModFilter())->buildModUpdateFilter();
        $request = $this->getRequest();
        
        return [
            'id' => $this->params()->fromRoute('modUuid', null),
            'title' => $filters['title']->filter($request->getPost('title', '')),
            'summary' => $filters['summary']->filter($request->getPost('summary', '')),
            'isPublished' => (int) $request->getPost('isPublished', 0),
            'descriptionRaw' => $filters['descriptionRaw']->filter($request->getPost('descriptionRaw', '')),
            'tags' => $request->getPost('tags', ''),
            'backgroundUuid' => $request->getPost('backgroundUuid',''),
            'images' => json_decode($request->getPost('images', '[]'), true), // Image data is JSON-encoded
            'resources' => json_decode($request->getPost('resources', '[]'), true), // Resource data is JSON-encoded
        ];
    }
}

/* EOF */