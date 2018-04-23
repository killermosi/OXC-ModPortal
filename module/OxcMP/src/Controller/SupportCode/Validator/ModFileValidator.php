<?php

/*
 * Copyright © 2016-2018 OpenXcom Mod Portal Developers
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

namespace OxcMP\Controller\SupportCode\Validator;

use Zend\Validator\AbstractValidator;
use Zend\Validator\Uuid;
use Zend\Validator\Regex;
use OxcMP\Util\Regex as RegexUtil;

/**
 * Validate a ModFile structure
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModFileValidator extends AbstractValidator
{
    /*
     * Validation constants
     */
    const BAD_REQUEST         = 'bad_request';
    const INVALID_DESCRIPTION = 'invalid_description';
    const INVALID_FILENAME    = 'invalid_filename';
    const INVALID_POSITION    = 'invalid_position';
    
    /**
     * Validation message templates
     * @var array
     */
    protected $messageTemplates = [
        self::BAD_REQUEST         => 'global_bad_request',
        self::INVALID_DESCRIPTION => 'page_editmod_error_invalid_description',
        self::INVALID_FILENAME    => 'page_editmod_error_invalid_filename',
        self::INVALID_POSITION    => 'page_editmod_error_invalid_position'
    ];
    
    /**
     * Validate the ModFile data
     * 
     * @param mixed $value The value to validate
     * @return boolean
     */
    public function isValid($value)
    {
        $this->setValue($value);
        
        // Value should be array
        if (!is_array($value)) {
            $this->error(self::BAD_REQUEST);
            return false;
        }
        
        // Valiate each sub-item
        foreach ($value as $item) {
            // Each sub-item should be an array and must contain exactly 4 keys
            // - uuid
            // - description
            // - filename
            // - position
            if (!is_array($item) || count($item) != 4) {
                $this->error(self::BAD_REQUEST);
                return false;
            }
            
            if (!$this->validateUuid($item)) {
                return false;
            }
            
            if (!$this->validateDescription($item)) {
                return false;
            }
            
            if (!$this->validateFilename($item)) {
                return false;
            }
            
            if (!$this->validatePosition($item)) {
                return false;
            }
        }
        
        // Validation OK
        return true;
    }

    /**
     * Validate the UUID for a ModFile
     * 
     * @param array $item The item data
     * @return boolean
     */
    private function validateUuid(array $item)
    {
        // "uuid" must be present and be a string
        if (!isset($item['uuid']) || !is_string($item['uuid'])) {
            $this->error(self::BAD_REQUEST);
            return false;
        }
        
        // UUID can be empty
        if (empty($item['uuid'])) {
            return true;
        }
        
        $validator = new Uuid();
        
        if (!$validator->isValid($item['uuid'])) {
            $this->error(self::BAD_REQUEST);
            return false;
        }
        
        // UUID is valid
        return true;
    }
    
    /**
     * Validate the description for a ModFile
     * 
     * @param array $item The item data
     * @return boolean
     */
    private function validateDescription(array $item)
    {
        // "description" must be present and be a string
        if (!isset($item['description']) || !is_string($item['description'])) {
            $this->error(self::BAD_REQUEST);
            return false;
        }
        
        // Description can be empty
        if (empty($item['description'])) {
            return true;
        }
        
        $validator = new Regex(RegexUtil::BASIC_LATIN_AND_PUNCTUATION);
        
        if (!$validator->isValid($item['description'])) {
            $this->error(self::INVALID_DESCRIPTION);
            return false;
        }
        
        // Description is valid
        return true;
    }
    
    /**
     * Validate the filename for a ModFile
     * 
     * @param array $item The item data
     * @return boolean
     */
    private function validateFilename(array $item)
    {
        // "filename" must be present and be a string
        if (!isset($item['filename']) || !is_string($item['filename'])) {
            $this->error(self::BAD_REQUEST);
            return false;
        }
        
        // Filename can be empty
        if (empty($item['filename'])) {
            return true;
        }
        
        $validator = new Regex(RegexUtil::SLUG);
        
        if (!$validator->isValid($item['filename'])) {
            $this->error(self::INVALID_FILENAME);
            return false;
        }
        
        // Filename is valid
        return true;
    }
    
    /**
     * Validate the position for a ModFile
     * 
     * @param array $item The item data
     * @return boolean
     */
    private function validatePosition(array $item)
    {
        // "position" must be present and be a string
        if (!isset($item['position']) || !is_string($item['position'])) {
            $this->error(self::BAD_REQUEST);
            return false;
        }
        
        // Position must be a strict positive integer string representation of a number
        $validator = new Regex(RegexUtil::PINTS);
        
        if (!$validator->isValid($item['position'])) {
            $this->error(self::INVALID_POSITION);
            return false;
        }
        
        // Position is valid
        return true;
    }
}

/* EOF */
