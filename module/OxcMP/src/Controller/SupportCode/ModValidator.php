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

namespace OxcMP\Controller\SupportCode;

use Zend\Validator\ValidatorChain;
use Zend\Validator\Digits;
use Zend\Validator\Callback;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\Regex;
use Zend\Validator\StringLength;
use Zend\Validator\Uuid;
use Ramsey\Uuid\DegradedUuid; // Use this as we will need an alias for UUID anyway
use OxcMP\Service\Storage\StorageService;
use OxcMP\Controller\SupportCode\Validator\ModFileValidator;

/**
 * Validator for mod data
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModValidator {
    /**
     * Regexp for "Latin letters, numbers and basic punctuation" validation
     * @TODO: use the Regex clas
     * @var string
     */
    const BASIC_LATIN_REGEXP = '/^[A-Za-z0-9 _:\-\.\/\*\(\)\&]*$/';
    
    /**
     * Regexp for "Latin letters, numbers, basic punctuation and Markdown syntax" validation
     * @TODO: Is it really needed?
     * @var string
     */
    const MARKDOWN_REGEXP = '/^[A-Za-z0-9 _:\-\.\/\*\s]+$/';
    
    /**
     * Regexp for tags list supported characters (letters, numbers, dashes, commas, must not start or end with
     * a dash or comma, or be an empty string)
     * @var string
     */
    const TAGS_REGEXP_MUST_CONTAIN_ONLY = '/^$|^[a-z0-9]+$|^[a-z0-9][a-z0-9,\-]*[a-z0-9]$/';
    
    /**
     * Regexp for tags list unsupported characters - no double-dashes or double-commas
     * TODO: Can this be merged with TAGS_REGEXP_MUST_CONTAIN_ONLY 
     * @var string
     */
    const TAGS_REGEXP_MUST_NOT_CONTAIN = '/^((?!--|,,).)*$/';
    
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
        $chars = new Regex(self::BASIC_LATIN_REGEXP);
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
        return new Uuid();
    }
    
    /**
     * Build the mod update data validator
     * 
     * @return array A list of validators indexed by the field name in lowerCamelCase
     */
    public function buildModUpdateValidator()
    {
        // Id
        $idValidator = new Uuid();
        
        $isPublishedValidator = new Regex('/^[0|1]$/');
        
        // Title
        $titleValidator = new ValidatorChain();
        
        $titleLength = new StringLength();
        $titleLength->setMin(4);
        $titleLength->setMax(64);
        $titleLength->setMessage('page_editmod_error_title_length_short', StringLength::TOO_SHORT);
        $titleLength->setMessage('page_editmod_error_title_length_long', StringLength::TOO_LONG);
        $titleValidator->attach($titleLength, true);
        
        $titleChars = new Regex(self::BASIC_LATIN_REGEXP);
        $titleChars->setMessage('page_editmod_error_title_characters_forbidden', Regex::NOT_MATCH);
        $titleValidator->attach($titleChars, true);
        
        // Summary
        $summaryValidator = new ValidatorChain();
        
        $summaryLength = new StringLength();
        $summaryLength->setMin(4);
        $summaryLength->setMax(128);
        $summaryLength->setMessage('page_editmod_error_summary_length_short', StringLength::TOO_SHORT);
        $summaryLength->setMessage('page_editmod_error_summary_length_long', StringLength::TOO_LONG);
        $summaryValidator->attach($summaryLength, true);
        
        $summaryChars = new Regex(self::BASIC_LATIN_REGEXP);
        $summaryChars->setMessage('page_editmod_error_summary_characters_forbidden', Regex::NOT_MATCH);
        $summaryValidator->attach($summaryChars, true);
        
        // Description
        $descriptionRawValidator = new ValidatorChain();
        
//        $descriptionRawChars = new Regex(self::MARKDOWN_REGEXP); 
//        $descriptionRawChars->setMessage('page_editmod_error_description_characters_forbidden', Regex::NOT_MATCH);
//        $descriptionRawValidator->attach($descriptionRawChars, true);
        
        $descriptionRawLength = new StringLength();
        $descriptionRawLength->setMin(4);
        $descriptionRawLength->setMax(65535);
        $descriptionRawLength->setMessage('page_editmod_error_description_length_short', StringLength::TOO_SHORT);
        $descriptionRawLength->setMessage('page_editmod_error_description_length_long', StringLength::TOO_LONG);
        $descriptionRawValidator->attach($descriptionRawLength, true);
        
        // Tags
        $tagsValidator = new ValidatorChain();
        
        $tagsMustContain = new Regex(self::TAGS_REGEXP_MUST_CONTAIN_ONLY);
        $tagsValidator->attach($tagsMustContain);
        
        $tagsMustNotContain = new Regex(self::TAGS_REGEXP_MUST_NOT_CONTAIN);
        $tagsValidator->attach($tagsMustNotContain);
        
        $backgroundUuidValidator = new Regex(
            // /^$|^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$/
            sprintf('/^$|%s/', DegradedUuid::VALID_PATTERN)
        );
        
        $imagesValidator = new ValidatorChain();
        
        $imagesUuid = new Callback(
            function ($imageUuids) {
                if (!is_array($imageUuids)) {
                    return false;
                }
                
                $uuidValidator = new Uuid();
                foreach ($imageUuids as $imageUuid) {
                    if (!$uuidValidator->isValid($imageUuid)) {
                        return false;
                    }
                }
                
                return true;
            }
        );
        //$imagesValidator->attach($imagesUuid);
        
        return [
            'id' => $idValidator,
            'isPublished' => $isPublishedValidator,
            'title' => $titleValidator,
            'summary' => $summaryValidator,
            'descriptionRaw' => $descriptionRawValidator,
            'tags' => $tagsValidator,
            'backgroundUuid' => $backgroundUuidValidator,
            'images' => $imagesValidator
        ];
    }
    
    /**
     * Build the raw description validator
     * 
     * @return ValidatorChain
     */
    public function buildModDescriptionRawValidator()
    {
        $validator = new ValidatorChain();
        
        $descriptionLength = new StringLength();
        $descriptionLength->setMax(65535);
        $descriptionLength->setMessage('page_editmod_error_description_length_long', StringLength::TOO_LONG);
        $validator->attach($descriptionLength, true);
        
//        $descriptionRawChars = new Regex(self::MARKDOWN_REGEXP); 
//        $descriptionRawChars->setMessage('page_editmod_error_description_characters_forbidden', Regex::NOT_MATCH);
//        $validator->attach($descriptionRawChars, true);
        
        return $validator;
    }
    
    /**
     * Build the upload file slot validator
     * 
     * @return array
     */
    public function buildUploadFileSlotValidator()
    {
        $uuidValidator = new Uuid();
        
        $typeValidator = new InArray();
        $typeValidator->setHaystack(array_keys(StorageService::TYPE_MAP));
        
        $sizeValidator = new Digits();
  
        $nameValidator = new NotEmpty();
        
        return [
            'uuid' => $uuidValidator,
            'type' => $typeValidator,
            'size' => $sizeValidator,
            'name' => $nameValidator,
        ];
    }
    
    /**
     * Build the upload file chunk validator
     * 
     * @return array
     */
    public function buildUploadFileChunkValidator()
    {
        $modUuidValidator = new Uuid();
        
        $slotUuidValidator = new Uuid();
        
        return [
            'modUuid' => $modUuidValidator,
            'slotUuid' => $slotUuidValidator,
        ];
    }
    
    /**
     * Build the mod file validator
     * 
     * @return ModFileValidator
     */
    public function buildModFileValidator()
    {
        return new ModFileValidator();
    }
}
