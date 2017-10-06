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
use Zend\Config\Config;

/**
 * Handle generation of static resource URLs
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class StaticUrl extends AbstractHelper
{
    /**
     * Module configuration
     * @var Config
     */
    private $config;
    
    /**
     * Class initialization
     * 
     * @param Config $config The module configuration
     */
    function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Build a URL for a resource
     * 
     * @param string $resourcePath Path to the resource
     * @return void
     */
    public function __invoke($resourcePath = null)
    {
        $staticStorage = $this->config->layout->staticStorageUrl;
        
        return empty($staticStorage)
            ? $this->view->url('home',[], ['force_canonical' => true]) . trim($resourcePath, '/')
            : rtrim($staticStorage, '/') . '/' . trim($resourcePath, '/');
    }
}

/* EOF */