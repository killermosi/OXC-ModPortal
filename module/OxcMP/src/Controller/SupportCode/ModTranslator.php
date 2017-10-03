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

use OxcMP\Entity\Mod;
use OxcMP\Entity\User;

/**
 * Update mod entity data
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModTranslator {
    /**
     * Create a new Mod entity containing basic data
     * 
     * @param string $modTitle The mod title
     * @param User $user The associated user
     * @return Mod
     */
    public function createMod($modTitle, User $user)
    {
        $mod = new Mod();
        
        $mod->setTitle($modTitle);
        $mod->setUserId($user->getId());
        
        return $mod;
    }
}
