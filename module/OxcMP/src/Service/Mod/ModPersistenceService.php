<?php

/*
 * Copyright © 2016-2017 OpenXcom Mod Portal Contributors
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
use OxcMP\Util\Log;
use Behat\Transliterator\Transliterator;

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
     * Class initialization
     * 
     * @param EntityManager $entityManager The entity manager
     */
    public function __construct(EntityManager $entityManager)
    {
        Log::info('Initializing ModPersistenceService');
        
        $this->entityManager = $entityManager;
    }
    
    /**
     * Create a new mod
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
            
            $this->buildModSlug($mod);
            
            $this->entityManager->persist($mod);
            $this->entityManager->flush();
            
            $this->entityManager->getConnection()
                                ->commit();
            
        } catch (\Exception $exc) {
            Log::notice('Failed to create mod');
            $this->entityManager->getConnection()
                                ->rollBack();
            
            throw $exc;
        }
        
        Log::debug('Mod successfully created');
    }
    
    /**
     * Create a slug for the specified mod
     * 
     * @param Mod $mod The mod entity
     * @return void
     */
    private function buildModSlug(Mod $mod)
    {
        Log::info(
            'Building slug for mod: ',
            !is_null($mod->getId()) ? $mod->getId()->toString() : 'new mod'
        );
        
        // The initial slug, based on the title
        $titleSlug = Transliterator::transliterate($mod->getTitle());
        Log::debug('Title slug: ', $titleSlug);
        
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
        
        $mod->setSlug($slug);
        
        Log::debug('Unique slug built: ', $slug);
    }
}
