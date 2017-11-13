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

namespace OxcMP\Service\ModFile;

use Doctrine\ORM\EntityManager;
use OxcMP\Entity\Mod;
use OxcMP\Entity\ModFile;
use OxcMP\Util\Log;

/**
 * Handle ModFile retrieval
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModFileRetrievalService
{
    /**
     * The Entity Manager
     * @var EntityManager 
     */
    private $entityManager;
    
    /**
     * Class initialization
     * 
     * @param EntityManager $entityManager The entity manager
     */
    public function __construct(EntityManager $entityManager)
    {
        Log::info('Initializing ModTagRetrievalService');
        
        $this->entityManager = $entityManager;
    }
    
    /**
     * Retrieve the background for a mod
     * 
     * @param Mod $mod The Mod entity
     * @return ModFile
     */
    public function getModBackground(Mod $mod)
    {
        Log::info('Retrieving the background for the mod having the  ID ', $mod->getId()->toString());
        
        $background = $this->entityManager->getRepository(ModFile::class)
                                          ->findOneBy(['modId' => $mod->getId(), 'type' => ModFile::TYPE_BACKGROUND]);
        
        if ($background instanceof ModFile) {
            Log::debug('Background found');
        } else {
            Log::debug('Background not found');
        }
        
        return $background;
    }
    
    /**
     * Retrieve the images for a mod
     * 
     * @param Mod $mod The mod entity
     * @return array
     */
    public function getModImages(Mod $mod)
    {
        Log::info('Retrieving the images for the mod having the Id ', $mod->getId()->toString());
        
        $images = $this->entityManager->getRepository(ModFile::class)
                                      ->findBy(['modId' => $mod->getId(), 'type' => ModFile::TYPE_IMAGE]);
        
        Log::debug('Retrieved ', count($images), ' mod image(s)');
        
        return $images;
    }
    
    /**
     * Retrieve the resources for a mod
     * 
     * @param Mod $mod The mod entity
     * @return array
     */
    public function getModResources(Mod $mod)
    {
        Log::info('Retrieving the resources for the mod having the Id ', $mod->getId()->toString());
        
        $resources = $this->entityManager->getRepository(ModFile::class)
                                         ->findBy(['modId' => $mod->getId(), 'type' => ModFile::TYPE_RESOURCE]);
        
        Log::debug('Retrieved ', count($resources), ' mod resource(s)');
        
        return $resources;
    }
}

/* EOF */