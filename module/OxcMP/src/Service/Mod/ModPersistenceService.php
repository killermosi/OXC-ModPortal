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

use Behat\Transliterator\Transliterator;
use Doctrine\ORM\EntityManager;
use OxcMP\Entity\Mod;
use OxcMP\Service\Markdown\MarkdownService;
use OxcMP\Util\Log;

/**
 * Mod persistence service
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModPersistenceService {
    /**
     * The Entity Manager
     * @var EntityManager 
     */
    private $entityManager;
    
    /**
     * The Markdown Service;
     * @var MarkdownService 
     */
    private $markdownService;
    
    /**
     * Class initialization
     * 
     * @param EntityManager   $entityManager   The entity manager
     * @param MarkdownService $markdownService The markdown service
     */
    public function __construct(EntityManager $entityManager, MarkdownService $markdownService)
    {
        Log::info('Initializing ModPersistenceService');
        
        $this->entityManager   = $entityManager;
        $this->markdownService = $markdownService;
    }
    
    /**
     * Create a new mod entity
     * 
     * @param Mod $mod The mod entity
     * @return void
     * @throws \Exception
     */
    public function createMod(Mod $mod)
    {
        Log::info('Creating new mod');
        
        
        try {
            // Everything goes into a transaction
            $this->entityManager->getConnection()
                                ->beginTransaction();
            
            // Create mod slug
            $this->buildModSlug($mod);
            
            $this->entityManager->persist($mod);
            $this->entityManager->flush();
            
            $this->entityManager->getConnection()
                                ->commit();
            
        } catch (\Exception $exc) {
            Log::error('Failed to create the mod');
            $this->entityManager->getConnection()
                                ->rollBack();
            
            throw $exc;
        }
        
        Log::debug('Mod successfully created');
    }
    
    /**
     * Update an existing mod entity
     * 
     * @param Mod $mod The mod entity
     * @return void
     * @throws \Exception
     */
    public function updateMod(Mod $mod)
    {
        Log::info('Updating the mod having the ID ', $mod->getId()->toString());
        
        try {
            // Everything goes into a transaction
            $this->entityManager->getConnection()
                                ->beginTransaction();
            
            $this->entityManager->persist($mod);
            $this->entityManager->flush();
            
            $this->entityManager->getConnection()
                                ->commit();
        } catch (\Exception $exc) {
            Log::error('Failed to update the mod');
            $this->entityManager->getConnection()
                                ->rollBack();
            
            throw $exc;
        }
    }
    
    /**
     * Create a slug for the specified mod
     * 
     * @param Mod $mod The mod entity
     * @return void
     */
    public function buildModSlug(Mod $mod)
    {
        Log::info(
            'Building slug for ',
            !is_null($mod->getId())
                ? 'mod ' . $mod->getId()->toString()
                : 'new mod'
        );
        
        // Don't build it if the mod title is the same
        if (!$mod->wasTitleChanged()) {
            Log::debug('The mod title was not changed, not building the mod slug');
            return;
        }
        
        $title = $mod->getTitle();

        
        // The initial slug, based on the title
        $titleSlug = Transliterator::transliterate($title);
        Log::debug('Slug derived from title: ', $titleSlug);
        
        // The working slug
        $slug = $titleSlug;
        $counter = 1;
        // Search query
        $queryBuilder = $this->entityManager->createQueryBuilder();
        
        $queryBuilder->select('m')
                     ->from(Mod::class, 'm')
                     ->where('m.slug = :slug');
        
        // If the slug is already used, keep adding digits to the end
        while (true) {
            $queryBuilder->setParameter('slug', $slug);
            
            $dbMod = $queryBuilder->getQuery()->getOneOrNullResult();
            
            if (
                !$dbMod instanceof Mod
                || $dbMod->getId() == $mod->getId()
            ) {
                // Slug not used by another mod
                break;
            }
            
            // Add a digit to the end of the slug or increment it
            $slug = $titleSlug . '-' . $counter++;
        }
        
        Log::debug('Unique slug built: ', $slug);
        $mod->setSlug($slug);
    }
}
