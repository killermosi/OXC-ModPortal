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

namespace OxcMP\Util;

/**
 * Container for various regular expression patterns
 * TODO: Move other regular expressions here
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class Regex
{
    /**
     * Match a slug (lowercase Latin letters, numbers and dashes)
     * TODO: don't match if it contains two consecutive dashes of if it starts/ends with a dash
     */
    const SLUG = '/^[a-z0-9\-]+$/';
    
    /**
     * Regex for "Latin letters, numbers and basic punctuation" validation
     * @TODO: improve character range
     * @var string
     */
    const BASIC_LATIN_AND_PUNCTUATION = '/^[A-Za-z0-9 _:\-\.\/\*\(\)\&]*$/';
    
    /**
     * Regex for "positive integer strict"
     * @var string
     */
    const PINTS = '/^([1-9][0-9]*)$/';
}

/* EOF */