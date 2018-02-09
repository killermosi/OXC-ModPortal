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

namespace OxcMP\View\Helper;

use OxcMP\Entity\Mod;
use OxcMP\Entity\ModFile;

/**
 * Handle generation of a mod image URL
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModImageUrl  extends AbstractUrlHelper
{
    /**
     * Build the URL for a mod background image
     * 
     * @param Mod     $mod      The Mod entity
     * @param ModFile $modImage The mod image
     * @param int     $width    The mod image width
     * @param int     $height   The mod image height
     * @return string URL to the mod image
     */
    public function __invoke(Mod $mod, ModFile $modImage = null, $width = 0, $height = 0)
    {
        // Don't generate anything if the image is not provided or is a placeholder
        if (
            !$modImage instanceof ModFile
            || is_null($modImage->getId())
            || $modImage->getType() !== ModFile::TYPE_IMAGE
        ) {
            return null;
        }
        
        // Gather data
        $fileInfo = new \SplFileInfo($modImage->getName());
        
        $parameters = [
            'modSlug' => $mod->getSlug(),
            'imageName' => $fileInfo->getBasename('.' . $fileInfo->getExtension()),
            'imageWidth' => $width,
            'imageHeight' => $height,
        ];
        
        return $this->buildStaticUrl(
            $this->view->url('mod-image', $parameters, ['force_canonical' => true])
        );
    }
}

/* EOF */