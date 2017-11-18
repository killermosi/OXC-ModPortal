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
 * File utilities
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class File
{
    /**
     * Format a numeric file size in human-readable format
     * Adapted from https://stackoverflow.com/a/2510459/1111983
     * 
     * @param int   $bytes     The file size
     * @param int   $precision Number of decimals
     * @param array $units     Size units, for internationalization purposes
     * @return string
     */
    public static function formatByteSize(
        $bytes,
        $precision = 2,
        $units = ['%s B', '%s KB', '%s MB', '%s GB', '%s TB', '%s PB']
    ) {
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow));

        return sprintf($units[$pow], round($bytes, $precision));
    }
}

/* EOF */
