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

namespace OxcMP\Util;

/**
 * Date and time utility functions
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class DateTime {
    
    /**
     * Create a new DateTime object using the UTC TimeZone
     * 
     * @param string $dateTimeString The date to set
     * @return \DateTime
     */
    public static function newDateTimeUtc($dateTimeString = null)
    {
        return new \DateTime($dateTimeString, new \DateTimeZone('UTC'));
    }
}
