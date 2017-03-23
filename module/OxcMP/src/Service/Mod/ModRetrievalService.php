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

namespace OxcMP\Service\Mod;

use Doctrine\ORM\EntityManager;
use OxcMP\Entity\Mod;
use OxcMP\Entity\User;
use OxcMP\Util\Log;

/**
 * Mod retrieval service
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModRetrievalService
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
        Log::info('Initializing ModRetrievalService');
        
        $this->entityManager = $entityManager;
    }
    
    /**
     * Find a mod by its internal identifier
     * 
     * @param integer $id The internal identifier
     * @return null|Mod
     */
    public function findModById($id)
    {
        Log::info('Trying to retrieve the mod having the ID: ', $id);
        
        $mod = $this->entityManager->getRepository(Mod::class)->find($id);
        
        if ($mod instanceof Mod) {
            Log::debug('Mod found');
        } else {
            Log::debug('Mod not found');
        }
        
        return $mod;
    }
    
    /**
     * Find a mod by its slug
     * 
     * @param string $slug The mod slug
     * @return Mod
     */
    public function findModBySlug($slug)
    {
        Log::info('Trying to retrieve the mod having the slug: ', $slug);
        
        $mod = $this->entityManager->getRepository(Mod::class)->findOneBy(['slug' => $slug]);
        
        if ($mod instanceof Mod) {
            Log::debug('Mod found');
        } else {
            Log::debug('Mod not found');
        }
        
        return $mod;
    }
    
    /**
     * Retrieve the latest published mods
     * 
     * @param integer $limit How many mods to retrieve
     * @return array
     */
    public function getLatestMods($limit)
    {
        Log::info('Retrieving the latest ', $limit, ' published mod(s)');
        
        $mods = $this->entityManager->getRepository(Mod::class)->getLatestMods($limit);
        
        Log::debug('Retrieved ', count($mods), ' mod(s)');
        
        return $mods;
    }
    
    /**
     * Retrieve all mods belonging to a certain user
     * 
     * @param User $user The user entity
     * @return array
     */
    public function getModsByUser(User $user)
    {
        Log::info('Retrieving all mods belonging to user ID ', $user->getId());
        
        $mods = $this->entityManager->getRepository(Mod::class)->getModsByUserId($user->getId());
        
        Log::debug('Retrieved ', count($mods), ' mod(s)');
        
        return $mods;
    }
}

/* EOF */