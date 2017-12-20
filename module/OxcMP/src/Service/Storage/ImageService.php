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

use Imagick;
use ImagickException;
use Zend\Config\Config;
use OxcMP\Entity\ModFile;
use OxcMP\Util\Log;

/**
 * Handle various image processing needs
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ImageService
{
    /**
     * Application configuration
     * @var Config 
     */
    private $config;
    
    /**
     * Class initialization
     * 
     * @param Config $config The configuration
     */
    public function __construct(Config $config)
    {
        Log::info('Initializing ImageService');
        
        $this->config = $config;
    }

    /**
     * Process a background image by converting it to PNG and adding a gradient overlay to it
     * 
     * @param string $backgroundImageData The image data
     * @return string The processed background image, in PNG format
     * @throws Exception\UnexpectedError
     */
    public function processBackgroundImage($backgroundImageData)
    {
        Log::info('Processing background image');
        // TODO: set EXIF data with source (webasite) and mod name
        try {
            $background = new Imagick();
            $background->readImageBlob($backgroundImageData);

            $background->setImageColorspace(Imagick::COLORSPACE_RGB);
            
            $gradient = new Imagick();
            $gradient->readimage($this->config->storage->backgroundGradient);
            $gradient->setImageColorspace(Imagick::COLORSPACE_RGB);
            
            $background->compositeimage($gradient, Imagick::COMPOSITE_ATOP, 0, 0);
            $background->setImageFormat(ModFile::IMAGE_FORMAT);
            $background->stripImage();
            
            $processedBackground = $background->getimageblob();
            
            $background->clear();
            $gradient->clear();
            
            return $processedBackground;
            
        } catch (ImagickException $exc) {
            Log::notice('Unexpected Imagick exception: ', $exc->getMessage());
            throw new Exception\UnexpectedError('Unexpected error while processing background image');
        }
    }
    
    public function processImage($imageData, $width, $height)
    {
        
    }
}

/* EOF */