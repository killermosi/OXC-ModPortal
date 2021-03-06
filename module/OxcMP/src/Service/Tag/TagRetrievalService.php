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

namespace OxcMP\Service\Tag;

use Doctrine\ORM\EntityManager;
use OxcMP\Entity\Tag;
use OxcMP\Util\Log;

/**
 * Tag retrieval service
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class TagRetrievalService {
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
        Log::info('Initializing TagRetrievalService');
        
        $this->entityManager = $entityManager;
    }
    
    /**
     * Retrieve all available tags
     * 
     * @return array
     */
    public function getAllTags()
    {
        Log::info('Retrieving all available tags');
        
        $tags = $this->entityManager->getRepository(Tag::class)->findBy([], ['tag' => 'asc']);
        
        Log::debug('Retrieved ', count($tags), ' tag(s)');
        
        return $tags;
    }
    
    /**
     * Retrieve a tag by name
     * 
     * @param string $tagName The tag name
     * @return Tag
     */
    public function getTag($tagName)
    {
        Log::info('Retrieving tag having the name ', $tagName);
        
        $tag = $this->entityManager->getRepository(Tag::class)->find($tagName);
        
        if ($tag instanceof Tag) {
            Log::debug('Tag found');
        } else {
            Log::debug('Tag not found');
        }
        
        return $tag;
    }
}
