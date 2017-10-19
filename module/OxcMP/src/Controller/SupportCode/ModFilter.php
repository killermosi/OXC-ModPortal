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

use Zend\Filter\FilterChain;
use Zend\Filter\StringTrim;
use Zend\Filter\StripNewlines;
use Zend\Filter\StripTags;

/**
 * Filter for mode data
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModFilter {
    /**
     * Build the mod title filter
     * 
     * @return FilterChain
     */
    public function buildModTitleFilter()
    {
        return (new FilterChain())->attach(new StringTrim())
                                  ->attach(new StripNewlines());
    }
    
    /**
     * Build filters for the mod update data
     * 
     * @return array A list of filters indexed by the filed name in lowerCamelCase
     */
    public function buildModUpdateFilter()
    {
        $titleFilter = (new FilterChain())
            ->attach(new StringTrim())
            ->attach(new StripNewlines());
        
        $summaryFilter = (new FilterChain())
            ->attach(new StringTrim())
            ->attach(new StripNewlines());
        
        $descriptionRawFilter = (new FilterChain())
            ->attach(new StringTrim());
        
        return [
            'title' => $titleFilter,
            'summary' => $summaryFilter,
            'descriptionRaw' => $descriptionRawFilter
        ];
    }
    
    /**
     * Build the mod description preview filter
     * 
     * @return FilterChain
     */
    public function buildModDescriptionRawFilter()
    {
        return (new FilterChain())->attach(new StringTrim()); // TODO: StripTags?
    }
}
