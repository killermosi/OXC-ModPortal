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

use Zend\Config\Config;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationService;
use Ramsey\Uuid\DegradedUuid as Uuid;
use OxcMP\Entity\Mod;
use OxcMP\Entity\ModFile;
use OxcMP\Service\Mod\ModRetrievalService;
use OxcMP\Service\Quota\QuotaService;
use OxcMP\Service\Quota\Exception as QuotaException;
use OxcMP\Service\Storage\StorageService;
use OxcMP\Service\Storage\Exception as StorageException;
use OxcMP\Util\Log;

/**
 * Description of ModFileManagementController
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModFileManagementController extends AbstractController
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
     * The quota service
     * @var QuotaService
     */
    private $quotaService;
    
    /**
     * The storage service
     * @var StorageService
     */
    private $storageService;
    
    /**
     * Application configuration
     * @var Config
     */
    private $config;
    
    /**
     * Class initialization
     * 
     * @param AuthenticationService $authenticationService The authentication service
     * @param ModRetrievalService   $modRetrievalService   The mod retrieval service
     * @param QuotaService          $quotaService          The quota service
     * @param StorageService        $storageService        The storage service
     * @param Config                $config                The application configuration
     */
    public function __construct(
        AuthenticationService $authenticationService,
        ModRetrievalService $modRetrievalService,
        QuotaService $quotaService,
        StorageService $storageService,
        Config $config
    ) {
        parent::__construct();
        
        // Services
        $this->authenticationService = $authenticationService;
        $this->modRetrievalService   = $modRetrievalService;
        $this->quotaService          = $quotaService;
        $this->storageService        = $storageService;
        $this->config                = $config;
    }
    
    /**
     * Create a new upload slot
     * 
     * @return JsonModel
     */
    public function createUploadSlotAction()
    {
        Log::info('Processing mod-file-management/my-mods action');
        
        // Go to MyMods if the request is not AJAX
        if (!$this->getRequest()->isXmlHttpRequest()) {
            Log::notice('Request is not AJAX, ignoring');
            return $this->redirect()->toRoute('my-mods');
        }
        
        $result = new JsonModel();
        $result->success = false;
        $result->message = $this->translate('global_bad_request');
        
        // Collect file upload data
        $request = $this->getRequest();
        
        $parameters = [
            'uuid' => $this->params()->fromRoute('modUuid', ''),
            'type' => $request->getPost('type', ''),
            'size' => $request->getPost('size', '0'),
            'name' => $request->getPost('name', '')
        ];
        
        // Backgrounds names have a specific value
        if (
            in_array($parameters['type'], array_keys(StorageService::TYPE_MAP))
            && ModFile::TYPE_BACKGROUND == StorageService::TYPE_MAP[$parameters['type']]
        ) {
            $parameters['name'] = ModFile::BACKGROUND_NAME;
        }
        
        // Validate the received data
        $validators = (new SupportCode\ModValidator())->buildUploadFileSlotValidator();
        
        foreach ($parameters as $parameterName => $parameterValue) {
            $validator = $validators[$parameterName];
            
            if (!$validator->isValid($parameterValue)) {
                Log::notice('Unexpected validation failure: ', $validator->getMessages());
                return $result;
            }
        }
        
        // Check that the file is not too large
        switch (StorageService::TYPE_MAP[$parameters['type']]) {
            case ModFile::TYPE_IMAGE:
            case ModFile::TYPE_BACKGROUND:
                $maxFileSize = $this->config->storage->maxFileSize->image;
                break;
            case ModFile::TYPE_RESOURCE:
                $maxFileSize = $this->config->storage->maxFileSize->resource;
                break;
            default:
                Log::error('Unsupported file type for size check: ', StorageService::TYPE_MAP[$parameters['type']]);
                $result->message = $this->translate('global_unexpected_error');
                return $result;                
        }
        
        if ($parameters['size'] > ($maxFileSize * 1024 * 1024)) {
            $result->message = $this->translate('page_editmod_error_upload_too_big');
            return $result;
        }
        
        // Retrieve and validate the mod
        $mod = $this->modRetrievalService->getModById(Uuid::fromString($parameters['uuid']));

        if (!$mod instanceof Mod) {
            Log::notice('Could not find the mod having the UUID ', $parameters['uuid']);
            return $result;
        }
        
        if ($mod->getUserId() != $this->authenticationService->getIdentity()->getId()) {
            Log::notice(
                'The mod having the UUID ',
                $mod->getId()->toString(),
                ' does not belong to the user having the UUID ',
                $this->authenticationService->getIdentity()->getId()->toString()
            );
            
            return $result;
        }
        
        // Check quota
        try {
            $this->quotaService->checkQuota($this->authenticationService->getIdentity(), $mod, $parameters['size']);
        } catch (QuotaException\InsufficientStorageSpace $exc) {
            $result->message = $this->translate('page_editmod_error_storage_insufficient');
            return $result;
        } catch (QuotaException\UserQuotaReached $exc) {
            $result->message = $this->translate('page_editmod_error_storage_user_quota');
            return $result;
        } catch (QuotaException\ModQuotaReached $exc) {
            $result->message = $this->translate('page_editmod_error_storage_mod_quota');
            return $result;
        } catch (\Exception $exc) {
            Log::notice('Unexpected error: ', $exc->getMessage());
            $result->message = $this->translate('global_unexpected_error');
            return $result;
        }
        
        // Create the upload slot
        try {
            $slotUuid = $this->storageService->createUploadSlot(
                $mod,
                $parameters['size'],
                $parameters['type'],
                $parameters['name']
            );
        } catch (\Exception $exc) {
            $result->message = $this->translate('global_unexpected_error');
            return $result;
        }
        
        $result->success = true;
        $result->message = $slotUuid;
        
        return $result;
    }
    
    /**
     * Upload a file chunk
     * 
     * @return JsonModel
     */
    public function uploadfileChunkAction()
    {
        Log::info('Processing mod-file-management/upload-file-chunk action');
        
        // Go to MyMods if the request is not AJAX
        if (!$this->getRequest()->isXmlHttpRequest()) {
            Log::notice('Request is not AJAX, ignoring');
            return $this->redirect()->toRoute('my-mods');
        }
        
        // The "message" key will contain error message on failure, null on success and the URL to the 
        // temporary resource URL when the last chunk was successfully uploaded and the
        // file was successfully combined from the chunks and validated.
        
        $result = new JsonModel();
        $result->success = false;
        $result->message = $this->translate('global_bad_request');
        
        // Collect file upload data
        $request = $this->getRequest();
        
        $parameters = [
            'modUuid' => $this->params()->fromRoute('modUuid', ''),
            'slotUuid' => $request->getPost('slotUuid', ''),
        ];
        
        // Validate the received data
        $validators = (new SupportCode\ModValidator())->buildUploadFileChunkValidator();
        
        foreach ($parameters as $parameterName => $parameterValue) {
            $validator = $validators[$parameterName];
            
            if (!$validator->isValid($parameterValue)) {
                Log::notice('Unexpected validation failure: ', $validator->getMessages());
                return $result;
            }
        }
        
        // Chunk data is a separate value
        $chunkData = $this->params()->fromFiles('chunkData', []);
        
        if (empty($chunkData)) {
            Log::notice('Chunk data missing from request');
            return $result;
        }
        
        // Retrieve and check the mod
        $mod = $this->modRetrievalService->getModById(Uuid::fromString($parameters['modUuid']));

        if (!$mod instanceof Mod) {
            Log::notice('Could not find the mod having the UUID ', $parameters['modUuid']);
            return $result;
        }
        
        if ($mod->getUserId() != $this->authenticationService->getIdentity()->getId()) {
            Log::notice(
                'The mod having the UUID ',
                $mod->getId()->toString(),
                ' does not belong to the user having the UUID ',
                $this->authenticationService->getIdentity()->getId()->toString()
            );
            
            return $result;
        }
        
        try {
            $uploadStatus = $this->storageService->uploadChunk($mod, $parameters['slotUuid'], $chunkData);
        } catch (StorageException\InvalidResource $exc) {
            Log::notice('Not a valid zip file');
            $result->message = $this->translate('page_editmod_error_file_not_resource');
            return $result;
        } catch (StorageException\InvalidImage $exc) {
            Log::notice('Not a valid image file');
            $result->message = $this->translate('page_editmod_error_file_not_image');
            return $result;
        } catch (StorageException\InvalidBackground $exc) {
            Log::notice('Not a valid background file');
            $result->message = $this->translate('page_editmod_error_file_not_background');
            return $result;
        } catch (\Exception $exc) {
            Log::notice('Unexpected exception uploading a file chunk: ', $exc->getMessage());
            $result->message = $this->translate('global_unexpected_error');
            return $result;
        }
        
        sleep(1);
        
        // Null the message
        $result->message = null;
        
        if ($uploadStatus) {
            // Create temporary URL
        }
        
        $result->success = true;
        return $result;
    }
}

/* EOF */