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

namespace OxcMP\Service\Quota;

use Doctrine\ORM\EntityManager;
use Zend\Config\Config;
use OxcMP\Entity\Mod;
use OxcMP\Entity\ModFile;
use OxcMP\Entity\User;
use OxcMP\Service\Storage\StorageOptions;
use OxcMP\Util\File;
use OxcMP\Util\Log;

/**
 * Handle quota checks
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class QuotaService
{
    /**
     * The entity manager
     * @var EntityManager 
     */
    private $entityManager;
    
    /**
     * The storage options
     * @var StorageOptions
     */
    private $storageOptions;
    
    /**
     * Application configuration
     * @var Config 
     */
    private $config;
    
    /**
     * Class initialization
     * 
     * @param EntityManager $entityManager The entity manager
     * @param Config        $config        Application configuration
     */
    public function __construct(EntityManager $entityManager, Storageoptions $storageOptions, Config $config)
    {
        $this->entityManager = $entityManager;
        $this->storageOptions = $storageOptions;
        $this->config = $config;
    }

    /**
     * Check that a user can upload a new file for a mod
     * 
     * @param User    $user The user
     * @param Mod     $mod The mod
     * @param integer $fileSize The file size
     * @return void
     */
    public function checkQuota(User $user, Mod $mod, $fileSize)
    {
        Log::info(
            'Checking that user ',
            $user->getId()->toString(),
            ' can upload a new file for mod ',
            $mod->getId()->toString(),
            ' having the size ',
            File::formatByteSize($fileSize)
        );
        
        // Check free storage space
        $modStorageDir = $this->storageOptions->getModRootStorageDirectory();
        $minFreeSpace = $this->config->storage->quota->freeSpace * 1024 * 1024;
        $diskFreeSpace = disk_free_space($modStorageDir);
        
        if ($diskFreeSpace <= $minFreeSpace) {
            Log::notice(
                'There is not enough free space in storage: ',
                File::formatByteSize($diskFreeSpace),
                ' currently free, ',
                File::formatByteSize($minFreeSpace),
                ' must be free at all times'
            );
            
            throw new Exception\InsufficientStorageSpace();
        }
        
        // Check that the file fits the free storage space
        if ($diskFreeSpace - $fileSize <= $minFreeSpace) {
            Log::notice(
                'There is not enough free space in storage to store a file having ',
                File::formatByteSize($fileSize),
                ', the storage currently having ',
                File::formatByteSize($diskFreeSpace),
                ' currently free, ',
                File::formatByteSize($minFreeSpace),
                ' must be free at all times'
            );
            
            throw new Exception\InsufficientStorageSpace();
        }
        
        // Retrieve all files for user, and count the used space
        $modUsedSpace = $totalUsedSpace = 0;
        $files = $this->entityManager->getRepository(ModFile::class)->findBy(['userId' => $user->getId()]);
        
        /* @var $modFile ModFile */
        foreach ($files as $modFile) {
            $totalUsedSpace += $modFile->getSize();
            
            if ($modFile->getModId() == $mod->getId()) {
                $modUsedSpace += $modFile->getSize();
            }
        }
        
        // Check user quota
        if ($totalUsedSpace + $fileSize > $this->config->storage->quota->user) {
            Log::notice(
                'File size over user quota: ',
                File::formatByteSize($fileSize)
            );
            
            throw new Exception\UserQuotaReached();
        }
        
        // Check mod quota
        if ($modUsedSpace + $fileSize > $this->config->storage->quota->mod) {
            Log::notice(
                'File size over user quota: ',
                File::formatByteSize($fileSize)
            );
            
            throw new Exception\UserQuotaReached();
        }
        
        Log::debug('The file can be uploaded');
    }
}

/* EOF */