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

namespace OxcMP\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * Base view helper class
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
abstract class AbstractViewHelper extends AbstractHelper
{
    /**
     * Translate a string
     * 
     * @param string $string The string to translate
     * @param array  $values Additional values to format the string with
     * @return string
     */
    public function translate($string, ...$values)
    {
        return sprintf($this->view->translate($string), ...$values);
    }
    
    /**
     * Prepare a template by replacing various placeholders in the template
     * 
     * @param string $template The template
     * @param array $searchReplace Placeholders and their values
     * @return string
     */
    public function renderTemplate($template, array $searchReplace = [])
    {
        if (empty($searchReplace)) {
            return $template;
        }
        
        return str_replace(
            array_keys($searchReplace),
            array_values($searchReplace),
            $template
        );
    }
}

/* EOF */