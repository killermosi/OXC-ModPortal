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

namespace OxcMP\Service\Storage;

use Zend\Config\Config;
use Ramsey\Uuid\Uuid;
use OxcMP\Entity\Mod;
use OxcMP\Service\Storage\StorageOptions;
use OxcMP\Util\Log;

/**
 * Handle file storage
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class StorageService
{
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
     * @param StorageOptions $storageOptions The storage options
     * @param Config         $config         The application configuration
     */
    function __construct(StorageOptions $storageOptions, Config $config)
    {
        Log::info('Initializing StorageService');
        
        $this->storageOptions = $storageOptions;
        $this->config         = $config;
    }
    
    /**
     * Create a new upload slot for the specified mod
     * 
     * @param Mod $mod The mod entity
     * @param type $size The file size
     * @param type $type The file type
     * @param type $name The original file name
     * @return string The upload slot UUID
     * @throws Exception\UnexpectedException
     */
    public function createUploadSlot(Mod $mod, $size, $type, $name = null)
    {
        Log::info(
            'Creating upload slot for mod ',
            $mod->getId()->toString(),
            ' for a file size of ',
            $size,
            ', type ',
            $type,
            ' and name ',
            $name
        );
        
        /*
         * The upload slot consists of a temporary directory that contains a single file named "<uuid>.json"
         * containing in a JSON string the following details about the uploaded file:
         * - modId
         * - fileId
         * - size
         * - type
         * - name
         * - uploadedChunks
         * 
         * The name can be null, and the uploadedChunks will start at zero. The UUID is automatically generated.
         */
        
        $uploadSlot = [
            'modId' => $mod->getId()->toString(),
            'size' => $size,
            'type' => $type,
            'name' => $name,
            'uploadedChunks' => 0,
        ];
        
        try {
            $uploadDir = $this->storageOptions->getTemporaryUploadStorageDirectory($mod, true);
        } catch (\Exception $exc) {
            Log::notice('Failed to create upload slot directory: ', $exc->getMessage());
            throw new Exception\UnexpectedException('Failed to create upload slot directory');
        }
        
        Log::debug('Using upload directory: ', $uploadDir);
        
        $uuid = Uuid::uuid4()->toString();
        
        $uploadSlotFile = $uploadDir . $uuid . '.json';
        
        if (false === file_put_contents($uploadSlotFile, json_encode($uploadSlot))) {
            Log::notice('Failed to cread the upload slot file: ', $uploadSlotFile);
            throw new Exception\UnexpectedException('Failed to create upload slot file');
        }
        
        Log::debug('Created upload slot having the UUID ', $uuid);
        
        return $uuid;
    }
}

/* EOF */