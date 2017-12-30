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
use OxcMP\Entity\ModFile;
use OxcMP\Entity\ModTag;
use OxcMP\Service\Storage\StorageService;
use OxcMP\Util\Log;

/**
 * Mod persistence service
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModPersistenceService {
    /**
     * The mod background remains unchanged
     * @var integer
     */
    const BACKGROUND_NO_OP = 0;
    
    /**
     * Use the default mod background
     * @var integer
     */
    const BACKGROUND_DEFAULT = 1;
    
    /**
     * The Entity Manager
     * @var EntityManager 
     */
    private $entityManager;
    
    /**
     * The storage service
     * @var StorageService
     */
    private $storageService;
    
    /**
     * Class initialization
     * 
     * @param EntityManager  $entityManager  The entity manager
     * @param StorageService $storageService The storage service
     */
    public function __construct(EntityManager $entityManager, StorageService $storageService)
    {
        Log::info('Initializing ModPersistenceService');
        
        $this->entityManager  = $entityManager;
        $this->storageService = $storageService;
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
     * @param Mod    $mod           The mod entity
     * @param array  $modTags       The associated tags
     * @param string $modBackground The mod background data
     * @return void
     * @throws \Exception
     */
    public function updateMod(Mod $mod, array $modTags, $modBackground)
    {
        Log::info('Updating the mod having the ID ', $mod->getId()->toString());
        
        try {
            // Everything goes into a transaction
            $this->entityManager->getConnection()
                                ->beginTransaction();
            
            $modUpdated = false;
            
            // Update tags
            if ($this->updateModTags($mod, $modTags) == true) {
                $modUpdated = true;
            }
            
            // Update background
            if ($this->updateModBackground($mod, $modBackground) == true) {
                $modUpdated = true;
            }
            
            // Mark mod updated if needed
            if ($modUpdated) {
                $mod->markUpdated();
            }
            
            // Update the Mod
            $this->entityManager->persist($mod);

            // Persist changes in the database
            $this->entityManager->flush();
            
            // Persist changes on disk
            $this->storageService->applyFileOperations();
            
            // Commit the transaction
            $this->entityManager->getConnection()
                                ->commit();
        } catch (\Exception $exc) {
            Log::error('Failed to update the mod: ', $exc->getMessage());
            $this->entityManager->getConnection()
                                ->rollBack();
            
            throw $exc;
        }
        
        // Delete mod temporar uploaded files
        $this->storageService->deleteModTemporaryUploadDirectory($mod);
        
        Log::debug('Mod successfully updated');
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
    
    /**
     * Update the ModTags for a Mod
     * 
     * @param Mod   $mod            The Mod entity
     * @param array $updatedModTags The updated ModTag list
     * @return boolean If any changes were made to the mod tags
     */
    private function updateModTags(Mod $mod, array $updatedModTags)
    {
        Log::info('Updating Mod tags');
        
        $modTagRepository =  $this->entityManager->getRepository(ModTag::class);
        
        $currentModTags = $modTagRepository->findBy(['modId' => $mod->getId()]);
        
        $updatedModTagNames = $currentModTagNames = [];
        $tagsToAdd = $tagsToRemove = [];
        
        // Build lists of tag names
        
        /* @var $updatedModTag ModTag */
        foreach ($updatedModTags as $updatedModTag) {
            $updatedModTagNames[] = $updatedModTag->getTag();
        }
        
        /* @var $currentModTag ModTag */
        foreach ($currentModTags as $currentModTag) {
            $currentModTagNames[] = $currentModTag->getTag();
        }
        
        // Build lists of added and removed tags
        foreach ($updatedModTags as $updatedModTag) {
            if (!in_array($updatedModTag->getTag(), $currentModTagNames)) {
                $tagsToAdd[$updatedModTag->getTag()] = $updatedModTag;
            }
        }
        
        foreach ($currentModTags as $currentModTag) {
            if (!in_array($currentModTag->getTag(), $updatedModTagNames)) {
                $tagsToRemove[$currentModTag->getTag()] = $currentModTag;
            }
        }
        
        if (count($tagsToAdd) == 0 && count($tagsToRemove) == 0) {
            Log::debug('No Mod tags added or removed');
            return false;
        }
        
        // Log tally
        if (count($tagsToAdd) == 0) {
            Log::debug('No Mod tags added');
        } else {
            Log::debug('Adding ', count($tagsToAdd), ' ModTag(s): ', implode(', ', array_keys($tagsToAdd)));
        }
        
        if (count($tagsToRemove) == 0) {
            Log::debug('No Mod tags removed');
        } else {
            Log::debug('Removing ', count($tagsToRemove), ' ModTag(s): ', implode(', ', array_keys($tagsToRemove)));
        }
        
        // Persist changes
        foreach ($tagsToAdd as $modTag) {
            $this->entityManager->persist($modTag);
        }
        
        foreach ($tagsToRemove as $modTag) {
            $this->entityManager->remove($modTag);
        }
        
        Log::debug('Done updating mod tags');
        return true;
    }
    
    /**
     * Update the mod background data
     * 
     * @param Mod    $mod            The Mod entity
     * @param string $backgroundData The background data
     * @return boolean If the background was updated or not
     */
    private function updateModBackground(Mod $mod, $backgroundData)
    {
        Log::notice('Updating the mod backgroud for mod ', $mod->getId()->toString(), ' to "', $backgroundData, '"');

        if ($backgroundData === (string) self::BACKGROUND_NO_OP) {
            Log::debug('No changes to the mod background background requested');
            return false;
        }
        
        // Retrieve the current background, if any (as it is needed anyway)
        $criteria = [
            'modId' => $mod->getId(),
            'type' => ModFile::TYPE_BACKGROUND
        ];

        $currentBackground = $this->entityManager->getRepository(ModFile::class)->findOneBy($criteria);
        
        // Delete it
        if ($currentBackground instanceof ModFile) {
            Log::debug('Removing current background');
            
            $this->storageService->deleteModFile($mod, $currentBackground);
            $this->entityManager->remove($currentBackground);
            $this->entityManager->flush($currentBackground);
            
            Log::debug('Current background removed');
        }
        
        if ($backgroundData === (string) self::BACKGROUND_DEFAULT) {
            if (!$currentBackground instanceof ModFile) {
                Log::debug('No custom background for this mod found, nothing to restore');
                return false;
            }
            
            Log::debug('Default background restored');
            return true;
        }
        
        // Create a new ModFile
        $modFile = new ModFile();
        $modFile->setModId($mod->getId());
        $modFile->setName(ModFile::BACKGROUND_NAME);
        $modFile->setUserId($mod->getUserId());
        $modFile->setType(ModFile::TYPE_BACKGROUND);

        // Persist it, so that we have an ID
        $this->entityManager->persist($modFile);
        
        // Copy the file to storage
        $fileSize = $this->storageService->createModFile($mod, $modFile, $backgroundData);
        
        // Update the file size
        $modFile->setSize($fileSize);
        
        // Persist the file size change
        $this->entityManager->persist($modFile);
        
        Log::debug('Mod background updated');
        return true;
    }
}

/* EOF */