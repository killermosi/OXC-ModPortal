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
use OxcMP\Entity\Mod;
use OxcMP\Util\Log;

/**
 * Storage options
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class StorageOptions
{
    /**
     * Mode to use when creating new directories in storage
     * @var type 
     */
    private $mode;
    
    /**
     * Application configuration
     * @var Config 
     */
    private $config;
    
    /**
     * Class initialization
     * 
     * @param Config $config Application configuration
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->mode = $config->storage->mode;
    }

    /**
     * Retrieve the mod storage directory
     * 
     * @param boolean $createIfNotExists Create it if needed
     * @return string
     * @throws \Exception
     */
    public function getModRootStorageDirectory($createIfNotExists = false)
    {
        $modRootDir = '/' . trim($this->config->storage->mod, '/') . '/';
       
        if (
            is_dir($modRootDir) == false
            && $createIfNotExists == true
            && @mkdir($modRootDir, $this->mode, true) == false
        ) {
            Log::critical('The mod root storage directory does not exists and could not be created: ', $modRootDir);
            throw new \Exception('Mod root storage directory does not exists and could not be created');
        }
        
        return $modRootDir;
    }
    
    /**
     * Retrieve the temporary storage directory for a Mod file upload
     *
     * @param Mod     $mod              The Mod entity
     * @param boolean $createIfNotExists Create if needed
     * @return string
     * @throws \Exception
     */
    public function getTemporaryUploadStorageDirectory(Mod $mod, $createIfNotExists = false)
    {
        $tempDir = trim($this->config->storage->temp, '/');
        $userDir = $mod->getUserId()->toString();
        $modDir  = $mod->getId()->toString();
        
        $dir = '/' . $tempDir . '/' . $userDir . '/' . $modDir . '/';
        
        if (
            is_dir($dir) == false
            && $createIfNotExists == true
            && @mkdir($dir, $this->mode, true) == false
        ) {
            Log::critical('The temporary user storage directory does not exists and could not be created: ', $dir);
            throw new \Exception('Mod temporary user storage directory does not exists and could not be created');
        }
        
        return $dir;
    }
}

/* EOF */