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
use OxcMP\Util\Log;

/**
 * Storage options
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class StorageOptions
{
    /**
     * Mod to use when creating new directories in storage
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
     * @param boolean $checkDirectory Create it if needed, perform additional checks
     * @return string
     * @throws \Exception
     */
    public function getModRootStorageDirectory($checkDirectory = false)
    {
        $modRootDir = '/' . trim($this->config->storage->mod->data, '/') . '/';
        
        if (is_dir($modRootDir) == false && $checkDirectory == false) {
            Log::critical('The mod root storage directory does not exists: ', $modRootDir);
            throw new \Exception('Mod root storage directory does not exists');
        }
        
        if (
            is_dir($modRootDir) == false
            && $checkDirectory == true
            && @mkdir($modRootDir, $this->mode, true) == false
        ) {
            Log::critical('The mod root storage directory does not exists and could not be created: ', $modRootDir);
            throw new \Exception('Mod root storage directory does not exists and could not be created');
        }
        
        if ($checkDirectory == true && is_writable($modRootDir) == false) {
            Log::critical('The mod root storage directory is not writable: ', $modRootDir);
            throw new \Exception('The mod root storage directory is not writable');
        }
        
        return $modRootDir;
    }
}

/* EOF */