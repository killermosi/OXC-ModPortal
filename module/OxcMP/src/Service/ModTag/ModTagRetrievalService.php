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

namespace OxcMP\Service\ModTag;

use Doctrine\ORM\EntityManager;
use OxcMP\Entity\Mod;
use OxcMP\Entity\ModTag;
use OxcMP\Util\Log;

/**
 * Handle ModTag retrieval
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModTagRetrievalService {
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
     * Retrieve all tags belonging to the specified mod
     * 
     * @param Mod $mod The mod entity
     * @return array
     */
    public function getModTags(Mod $mod)
    {
        Log::info('Retrieving all tags for mod ', $mod->getId()->toString());
        
        $modTags = $this->entityManager->getRepository(ModTag::class)
                                       ->findBy(['modId' => $mod->getId()], ['tag' => 'asc']);
        
        Log::debug('Retrieved ', count($modTags), ' mod tag(s)');
        
        return $modTags;
    }
}
