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
use Ramsey\Uuid\Uuid;
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
     * Delete a mod and all its associated data
     * 
     * @param Mod $mod The mod to delete
     * @return void
     * @throws \Exception
     */
    public function deleteMod(Mod $mod)
    {
        Log::info('Deleting mod ', $mod->getId());
        
        try {
            // Everything goes into a transaction
            $this->entityManager->getConnection()
                                ->beginTransaction();
            
            // Delete files
            $modFiles = $this->entityManager->getRepository(ModFile::class)
                                            ->findBy(['modId' => $mod->getId()]);
            
            foreach ($modFiles as $modFile) {
                $this->storageService->deleteModFile($mod, $modFile);
                $this->entityManager->remove($modFile);
            }
            
            // Delete tags
            $this->entityManager->getRepository(ModTag::class)
                                ->deleteTagsForMod($mod);
            
            // Keep a clone
            $modClone = clone $mod;
            
            // Delete the actual mod
            $this->entityManager->remove($mod);
            
            // Persist changes in the database
            $this->entityManager->flush();
            
            // Persist changes on disk
            $this->storageService->applyFileOperations();
            
            // Remove the cache for this mod
            $this->storageService->removeModCacheDirectory($modClone);
            
            // Remove the mod storage directory
            $this->storageService->deleteModStorageDirectory($modClone);
            
            // Commit the transaction
            $this->entityManager->getConnection()
                                ->commit();
        } catch (\Exception $exc) {
            Log::error('Failed to delete the mod: ', $exc->getMessage());
            $this->entityManager->getConnection()
                                ->rollBack();
        }
    }
    
    /**
     * Update an existing mod entity
     * 
     * @param Mod     $mod          The mod entity
     * @param array   $modTags      The mod tags
     * @param ModFile $background   The mod background
     * @param array   $modImages    The mod images
     * @param array   $modResources The mod resources
     * @return void
     * @throws \Exception
     */
    public function updateMod(Mod $mod, array $modTags, ModFile $background, array $modImages, array $modResources)
    {
        Log::info('Updating the mod having the ID ', $mod->getId()->toString());
        
        try {
            // Everything goes into a transaction
            $this->entityManager->getConnection()
                                ->beginTransaction();
            
            // Update the slug if needed
            $this->buildModSlug($mod);
            
            $modUpdated = false;
            
            // Update tags
            if ($this->updateModTags($mod, $modTags) == true) {
                $modUpdated = true;
            }
            
            // Update background
            if ($this->updateModBackground($mod, $background) == true) {
                $modUpdated = true;
            }
            
            // Update images
            if ($this->updateModFiles($mod, $modImages, ModFile::TYPE_IMAGE)) {
                $modUpdated = true;
            }
            
            // Update resources
            if ($this->updateModFiles($mod, $modResources, ModFile::TYPE_RESOURCE)) {
                
            }
            
            // Manually mark mod updated if needed
            if ($modUpdated) {
                $mod->markUpdated();
            }
            
            // Update the Mod entity
            $this->entityManager->persist($mod);

            // Persist changes in the database
            $this->entityManager->flush();
            
            // Persist changes on disk
            $this->storageService->applyFileOperations();

            // Clear the cache for this mod
            $this->storageService->removeModCacheDirectory($mod);
            
            // Commit the transaction
            $this->entityManager->getConnection()
                                ->commit();
        } catch (\Exception $exc) {
            Log::error('Failed to update the mod: ', $exc->getMessage());
            $this->entityManager->getConnection()
                                ->rollBack();
            
            throw $exc;
        }
        
        // Delete mod temporary uploaded files and clear the mod cache directory
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
     * TODO: Pass from controller a list of objects, not a string
     * 
     * @param Mod    $mod    The Mod entity
     * @param array $modTags The mod tags
     * @return boolean If any changes were made to the mod tags
     * @throws \Exception
     */
    private function updateModTags(Mod $mod, $modTags)
    {
        Log::info('Updating the mod tags for mod ', $mod->getId());
        
        $supportedTags = $this->entityManager->getRepository(Tag::class)->findAll();
        
        $supportedTagsList = [];
        /* @var $supportedTag Tag */
        foreach ($supportedTags as $supportedTag) {
            $supportedTagsList[] = $supportedTag->getTag();
        }
        
        // Check for unsupported tags
        /* @var $modTag ModTag */
        foreach ($modTags as $index => $modTag) {
            // Re-index the tags
            unset($modTags[$index]);
            $modTags[$modTag->getTag()] = $modTag;
            
            if (!in_array($modTag->getTag(), $supportedTagsList)) {
                Log::notice('Unsupported mod tag received: ', $modTag->getTag());
                throw new \Exception('Unsupported mod tag received');
            }
        }
        
        // Check that the new mod tags are not the same as the old ones
        $existingModTags = $this->entityManager->getRepository(ModTag::class)->findBy(['modId' => $mod->getId()]);
        
        // Final tags list, for logging purposes
        $finalTags = [];
        
        // Filter out unchanged tags
        /* @var $existingModTag ModTag */
        foreach ($existingModTags as $index => $existingModTag) {
            if (isset($modTags[$existingModTag->getTag()])) {
                $finalTags[] = $existingModTag->getTag();
                unset($existingModTags[$index], $modTags[$existingModTag->getTag()]);
            }
        }
        
        // What's left in $modTags must be added and what's left in $existingModTags must be deleted
        
        if (empty($modTags) && empty($existingModTags)) {
            Log::debug('No change to the mod tags, nothing to update');
            return false;
        }
        
        // Create the new entries
        foreach ($modTags as $modTag) {
            $this->entityManager->persist($modTag);
            $finalTags[] = $modTag->getTag();
        }
        
        // Remove entries
        foreach ($existingModTags as $existingModTag) {
            $this->entityManager->remove($existingModTag);
        }
        
        Log::debug('Done updating mod tags, mod tags list set to ', $finalTags);
        return true;
    }
    
    /**
     * Update the mod background data
     * 
     * @param Mod     $mod           The Mod entity
     * @param ModFile $newBackground The new mod background
     * @return boolean If the background was updated or not
     */
    private function updateModBackground(Mod $mod, ModFile $newBackground)
    {
        Log::info(
            'Updating the mod background for mod ',
            $mod->getId()->toString(),
            ' to "',
            $newBackground->getTemporaryUuid(),
            '"'
        );
        
        // Retrieve the current background, if any (as it is needed anyway)
        $criteria = [
            'modId' => $mod->getId(),
            'type' => ModFile::TYPE_BACKGROUND
        ];

        $currentBackground = $this->entityManager->getRepository(ModFile::class)->findOneBy($criteria);
        
        if (
            !$currentBackground instanceof ModFile
            && empty($newBackground->getTemporaryUuid())
        ) {
            Log::debug('No background changes - mod retains the default background');
            return false;
        }
        
        if (
            $currentBackground instanceof ModFile
            && $currentBackground->getId()->equals($newBackground->getTemporaryUuid())
        ) {
            Log::debug('No background changes - mod retains the current background');
            return false;
        }
        
        // Background updated, so delete the current one
        if ($currentBackground instanceof ModFile) {
            Log::debug('Removing current background');
            
            $this->storageService->deleteModFile($mod, $currentBackground);
            $this->entityManager->remove($currentBackground);
            $this->entityManager->flush($currentBackground);
            
            Log::debug('Current background removed');
            
            // If no new background specified, stop here
            if (empty($newBackground->getTemporaryUuid())) {
                Log::debug('Default background restored');
                return true;
            }
        }

        // Persist it, so that we have an ID
        $this->entityManager->persist($newBackground);
        
        // Copy the file to storage
        $this->storageService->createModFile($mod, $newBackground);
        
        // Persist the file size change
        $this->entityManager->persist($newBackground);
        
        Log::debug('Mod background updated');
        return true;
    }
    
    /**
     * Update the mod files with new ones
     * 
     * @param Mod   $mod   The Mod entity
     * @param array $files The mod files
     * @param int   $type  The file type: ModFile::TYPE_RESOURCE or ModFile::TYPE_IMAGE
     * @return boolean If the images were updated
     */
    private function updateModFiles(Mod $mod, array $files, $type)
    {
        Log::info(
            'Updating the mod files of type ',
            $type == ModFile::TYPE_RESOURCE ? '"resource"' : '"image"',
            ' for mod ',
            $mod->getId(),
            ' with ',
            count($files),
            ' item(s)'
        );
        
        // Retrieve existing files
        $oldFiles = $this->entityManager->getRepository(ModFile::class)->findBy([
            'modId' => $mod->getId(),
            'type' => $type
        ]);
        
        Log::debug('Found ', count($oldFiles), ' existing file(s)');
        
        /* @var $file ModFile */
        foreach ($files as $fileIndex => $file) {
            // Look for an old file matching the new one's temporary UUID
            /* @var $oldFile ModFile */
            foreach ($oldFiles as $oldFileIndex => $oldFile) {
                // Update the old file data if match is found
                if ($oldFile->getId()->equals($file->getTemporaryUuid())) {
                    Log::debug('Updating mod file ', $oldFile->getId());
                    
                    // Update data
                    $oldFile->setDescription($file->getDescription());
                    $oldFile->setName($file->getName());
                    
                    $files[$fileIndex] = $oldFile;
                    
                    // Remove the old file from the list
                    unset($oldFiles[$oldFileIndex]);
                    
                    continue(2);
                }
            }
        }
        
        
        // Delete the remaining mod files to free up the file names
        if (!empty($oldFiles)) {
            Log::debug('Removing mod file(s)');
            
            foreach ($oldFiles as $oldFile) {
                Log::debug('Removing mod file ', $oldFile->getId());
                $this->storageService->deleteModFile($mod, $oldFile);
                $this->entityManager->remove($oldFile);
            }

            // Flush the changes to free up filenames
            $this->entityManager->flush();

            Log::debug('Done removing mod file(s)');
        } else {
            Log::debug('No mod files were removed');
        }
        
        // Some counters
        $removedFilesCount = count($oldFiles);
        $updatedFilesCount = $createdFilesCount = 0;
        
        // Create/update the mod files
        foreach ($files as $fileIndex => $file) {
            
            // Update order and make sure the filename is unique for all files of this type
            $file->setFileOrder($fileIndex);
            $this->buildUniqueFilename($mod, $file);
            
            // Update existing files
            if (!empty($file->getId())) {
                Log::debug('Updating existing mod file ', $file->getId());
                // No actual operations to do, just persist the file in the database
            } else {
                Log::debug('Creating new mod file from temporary UUID ', $file->getTemporaryUuid());
                
                // Need an ID
                $this->entityManager->persist($file);
                
                $fileSize = $this->storageService->createModFile($mod, $file);
                $file->setSize($fileSize);
            }
            
            // Persist any file update
            $this->entityManager->persist($file);
            $this->entityManager->flush($file);
            
            // Increment the proper counter
            if (!empty($file->getId()) && $file->WasUpdated()) {
                $updatedFilesCount++;
            } elseif (empty($file->getId())) {
                $createdFilesCount++;
            }
        
        }
        
        Log::debug(
            'Updated ',
            $updatedFilesCount,
            ' mod file(s), created ',
            $createdFilesCount,
            ' mod file(s), removed ',
            $removedFilesCount,
            ' mod file(s)'
        );
        
        if ($updatedFilesCount > 0 || $createdFilesCount > 0 || $removedFilesCount) {
            Log::debug('The mod files were modified');
            return true;
        } else {
            Log::debug('The mod files were not modified');
            return false;
        }
    }
    
    /**
     * Build a unique filename for a mod file
     * 
     * @param Mod     $mod     The Mod entity
     * @param ModFile $modFile The ModFile entity
     * @return void
     */
    private function buildUniqueFilename(Mod $mod, ModFile $modFile)
    {
        Log::info('Building unique filename for supplied filename: ', $modFile->getName());
        
        if (empty(trim($modFile->getName()))) {
            Log::debug('Filename is empty, using mod slug as base: ', $mod->getSlug());
            $modFile->setName($mod->getSlug());
        }
        
        $fileSlug = Transliterator::transliterate($modFile->getName());
        
        $fileName = $fileSlug;
        $counter = 0;
        
        while (true) {
            
            $otherFile = $this->entityManager->getRepository(ModFile::class)->findOneBy([
                'modId' => $mod->getId(),
                'type' => $modFile->getType(),
                'name' => $fileName
            ]);
            
            if (
                !$otherFile instanceof ModFile
                || $otherFile->getId()->equals($modFile->getId())
            ) {
                break;
            }
            
            $counter++;
            $fileName = $fileSlug . '-' . $counter;
        }
        
        Log::debug('Unique filename built: ', $fileName);
        
        $modFile->setName($fileName);
    }
}

/* EOF */