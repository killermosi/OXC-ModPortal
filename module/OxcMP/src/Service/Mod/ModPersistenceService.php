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
use OxcMP\Entity\Tag;
use OxcMP\Service\Storage\StorageService;
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
     * @param Mod    $mod               The mod entity
     * @param string  $modTags           The associated tags
     * @param string $modBackgroundUuid The mod background UUID
     * @return void
     * @throws \Exception
     */
    public function updateMod(Mod $mod, $modTags, $modBackgroundUuid)
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
            if ($this->updateModBackground($mod, $modBackgroundUuid) == true) {
                $modUpdated = true;
            }
            
            // Manually mark mod updated if needed
            if ($modUpdated) {
                $mod->markUpdated();
            }
            
            // Update the Mod
            $this->entityManager->persist($mod);

            // Persist changes in the database
            $this->entityManager->flush();
            
            // Persist changes on disk
            $this->storageService->applyFileOperations($mod);
            
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
     * Update a mod tags
     * 
     * @param Mod    $mod          The Mod entity
     * @param string $selectedTags The mod tags, in a comma-separated list
     * @return boolean If any changes were made to the mod tags
     * @throws \Exception
     */
    private function updateModTags(Mod $mod, $selectedTags)
    {
        Log::info('Updating the mod tags for mod ', $mod->getId()->toString(), ' to "', $selectedTags, '"');
        
        $selectedTagsList = (strlen($selectedTags) != 0) ? explode(',', $selectedTags): [];
        
        // Look for duplicates
        if (count($selectedTagsList) != count(array_unique($selectedTagsList))) {
            Log::notice('Received duplicated tags: ', $selectedTagsList);
            throw new \Exception('Received duplicated tags');
        }
        
        // Check that all received tags are valid
        foreach ($selectedTagsList as $selectedTag) {
            $selectedModTag = $this->entityManager->getRepository(Tag::class)->find($selectedTag);
            
            if (!$selectedModTag instanceof Tag) {
                Log::notice('Received unknown mod tag: ', $selectedTag);
                throw new \Exception('Received unknown mod tag');
            }
        }
        
        // Retrieve all set tags for the mod
        $currentModTags = $this->entityManager->getRepository(ModTag::class)->findBy(['modId' => $mod->getId()]);
        
        // List, for logging purposes
        $finalTags = [];
        
        // Determine which tags to remove and which to add
        foreach ($currentModTags as $index => $currentModTag) {
            /* @var $currentModTag ModTag */
            if (!in_array($currentModTag->getTag(), $selectedTagsList)) {
                
                continue;
            }
            
            $finalTags[] = $currentModTag->getTag();
            
            // Delete the values from both lists
            unset($selectedTagsList[array_search($currentModTag->getTag(), $selectedTagsList)]);
            unset($currentModTags[$index]);
        }
        
        // What's left in the $currentModTags must be removed and what's in $selectedTagsList must be added
        if (count($currentModTags) == 0 && count($selectedTagsList) == 0) {
            Log::debug('No tags added or removed');
            return false;
        }
        
        // Remove entries
        foreach ($currentModTags as $currentModTag) {
            $this->entityManager->remove($currentModTag);
        }
        
        Log::debug('Removed ', count($currentModTags), ' tag(s)');
        
        // Add entries
        foreach ($selectedTagsList as $selectedTag) {
            $modTag = new ModTag();
            $modTag->setTag($selectedTag);
            $modTag->setModId($mod->getId());
            
            $this->entityManager->persist($modTag);
            
            $finalTags[] = $selectedTag;
        }
        
        Log::debug('Added ', count($selectedTagsList), ' tag(s)');
        
        sort($finalTags);
        
        Log::debug('Done updating mod tags, mod tags list set to ', $finalTags);
        return true;
    }
    
    /**
     * Update the mod background data
     * 
     * @param Mod    $mod            The Mod entity
     * @param string $backgroundUuid The background UUID
     * @return boolean If the background was updated or not
     */
    private function updateModBackground(Mod $mod, $backgroundUuid)
    {
        Log::info('Updating the mod background for mod ', $mod->getId()->toString(), ' to "', $backgroundUuid, '"');
        
        // Retrieve the current background, if any (as it is needed anyway)
        $criteria = [
            'modId' => $mod->getId(),
            'type' => ModFile::TYPE_BACKGROUND
        ];

        $currentBackground = $this->entityManager->getRepository(ModFile::class)->findOneBy($criteria);
        
        if (!$currentBackground instanceof ModFile && strlen($backgroundUuid) === 0) {
            Log::debug('No background changes - mod retains the default background');
            return false;
        }
        
        if ($currentBackground instanceof ModFile && $currentBackground->getId()->toString() === $backgroundUuid) {
            Log::debug('No background changes - mod retains the custom background');
            return false;
        }
        
        // Delete it
        if ($currentBackground instanceof ModFile) {
            Log::debug('Removing current background');
            
            $this->storageService->deleteModFile($mod, $currentBackground);
            $this->entityManager->remove($currentBackground);
            $this->entityManager->flush($currentBackground);
            
            Log::debug('Current background removed');
            
            // If no new background specieid, stop here
            if (strlen($backgroundUuid) === 0) {
                Log::debug('Default background restored');
                return true;
            }
        }
        
        $fileInfo = new \SplFileInfo(ModFile::BACKGROUND_NAME);
        
        // Create a new ModFile
        $modFile = new ModFile();
        $modFile->setModId($mod->getId());
        $modFile->setName($fileInfo->getBasename('.' . ModFile::EXTENSION_IMAGE));
        $modFile->setUserId($mod->getUserId());
        $modFile->setType(ModFile::TYPE_BACKGROUND);

        // Persist it, so that we have an ID
        $this->entityManager->persist($modFile);
        
        // Copy the file to storage
        $fileSize = $this->storageService->createModFile($mod, $modFile, $backgroundUuid);
        
        // Update the file size
        $modFile->setSize($fileSize);
        
        // Persist the file size change
        $this->entityManager->persist($modFile);
        
        Log::debug('Mod background updated');
        return true;
    }
}

/* EOF */