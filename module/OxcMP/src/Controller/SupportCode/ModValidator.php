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

namespace OxcMP\Controller\SupportCode;

use Zend\Validator\ValidatorChain;
use Zend\Validator\StringLength;
use Zend\Validator\Regex;
use Ramsey\Uuid\DegradedUuid as Uuid;

/**
 * Validator for mod data
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModValidator {
    
    /**
     * Build the mod title validator
     * 
     * @return ValidatorChain
     */
    public function buildModTitleValidator()
    {
        $validators = new ValidatorChain();
        
        $length = new StringLength();
        $length->setMin(4);
        $length->setMax(64);
        $length->setMessage('page_mymods_create_error_title_length_short', StringLength::TOO_SHORT);
        $length->setMessage('page_mymods_create_error_title_length_long', StringLength::TOO_LONG);
        $validators->attach($length, true);
        
        // Numbers, letters, spaces, and some punctuation
        $chars = new Regex('/^[A-Za-z0-9 _:\-\.\/\*]+$/');
        $chars->setMessage('page_mymods_create_error_title_characters_forbidden', Regex::NOT_MATCH);
        $validators->attach($chars, true);
        
        return $validators;
    }
    
    /**
     * Build the mod UUID validator
     * 
     * @return Regex
     */
    public function buildModUuidValidator()
    {
        return new Regex('/' . Uuid::VALID_PATTERN . '/');
    }
}
