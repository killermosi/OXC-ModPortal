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

use OxcMP\Entity\Mod;
use OxcMP\Entity\ModFile;
use OxcMP\Service\Storage\StorageService;
use OxcMP\Service\Mod\ModRetrievalService;
use OxcMP\Service\ModFile\ModFileRetrievalService;
use OxcMP\Util\Log;

/**
 * Mod file retrieval
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModFileController extends AbstractController
{
    /**
     * The storage service
     * @var StorageService 
     */
    private $storageService;
    
    /**
     * The mod retrieval service
     * @var ModRetrievalService
     */
    private $modRetrievalService;
    
    /**
     * The mod file retrieval service
     * @var ModFileRetrievalService
     */
    private $modFileRetrievalService;
    
    /**
     * Class initializations
     * 
     * @param StorageService          $storageService          The storage service
     * @param ModRetrievalService     $modRetrievalService     The mod retrieval service
     * @param ModFileRetrievalService $modFileRetrievalService The mod file retrieval service
     */
    function __construct(
        StorageService $storageService,
        ModRetrievalService $modRetrievalService,
        ModFileRetrievalService $modFileRetrievalService
    ) {
        parent::__construct();
        
        $this->storageService          = $storageService;
        $this->modRetrievalService     = $modRetrievalService;
        $this->modFileRetrievalService = $modFileRetrievalService;
    }

        /**
     * Retrieve a mod background
     * 
     * @return 
     */
    public function modBackgroundAction()
    {
        Log::info('Processing action mod-file/mod-background');
        
        $modSlug = $this->params()->fromRoute('modSlug');
        
        // Retrieve and check the mod
        $mod = $this->modRetrievalService->getModBySlug($modSlug);
        
        if (!$mod instanceof Mod) {
            Log::notice('The mod having the slug "', $modSlug, '" could not be found');
            return $this->errorResponse();
        }

        // Retrieve and check the background
        $modBackground = $this->modFileRetrievalService->getModBackground($mod);
        
        if (!$modBackground instanceof ModFile) {
            Log::notice('The background for the mod having the slug "', $modSlug, '" could not be found');
            return $this->errorResponse();
        }
        
        // Get the background contents
        try {
            $backgroundContents = $this->storageService->getModBackground($mod, $modBackground);
        } catch (\Exception $exc) {
            Log::notice('Failed to retrieve the background file contents: ', $exc->getMessage());
            return $this->errorResponse();
        }
        
        $this->getResponse()->getHeaders()->addHeaderLine(sprintf('Content-Type: %s', ModFile::MIME_IMAGE));
        $this->getResponse()->getHeaders()->addHeaderLine(
            sprintf('Content-Disposition: inline; filename="%s"', ModFile::BACKGROUND_NAME)
        );
        
        $this->getResponse()->setContent($backgroundContents);
        
        return $this->getResponse();
    }
}

/* EOF */