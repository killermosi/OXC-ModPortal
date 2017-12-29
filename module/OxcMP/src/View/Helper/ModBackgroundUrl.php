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

namespace OxcMP\View\Helper;

use OxcMP\Entity\Mod;
use OxcMP\Entity\ModFile;

/**
 * Handle generation of a mod background image URL
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModBackgroundUrl extends AbstractUrlHelper
{
    /**
     * Build the URL for a mod background image
     * 
     * @param Mod     $mod           The Mod entity
     * @param ModFile $modBackground The mod background image
     * @return string URL to the mod image, or to the default background if no valid background image was specified
     */
    public function __invoke(Mod $mod, ModFile $modBackground = null)
    {
        // No custom URL
        if (!$modBackground instanceof ModFile) {
            return $this->view->defaultBackgroundUrl();
        }
        
        return $this->buildStaticUrl(
            $this->view->url('mod-background', ['modSlug' => $mod->getSlug()], ['force_canonical' => true])
        );
    }
}

/* EOF */