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

use Zend\Config\Config;
use Zend\View\Helper\AbstractHelper;

/**
 * Base class for URL helpers
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
abstract class AbstractUrlHelper extends AbstractHelper
{
    /**
     * Module configuration
     * @var Config
     */
    protected $config;
    
    /**
     * Static storage URL, as defined in the configuration
     * @var string
     */
    private $staticStorageUrl;
    
    /**
     * Home URL
     * @var string
     */
    private $homeUrl;
    
    /**
     * Class initialization
     * 
     * @param Config $config The module configuration
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        
        $this->staticStorageUrl = $config->layout->staticStorageUrl;
        
        if (!empty($this->staticStorageUrl)) {
            $this->staticStorageUrl = rtrim($this->staticStorageUrl, '/') . '/';
        }
    }
    
    /**
     * Build the home URL
     * 
     * @return string
     */
    protected function buildHomeUrl()
    {
        if (empty($this->homeUrl)) {
            $this->homeUrl = $this->view->url('home',[], ['force_canonical' => true]);
        }
        
        return $this->homeUrl;
    }
    
    /**
     * Build a static storage version of a URL
     * 
     * @param string $url The original URL
     * @return string The static URL, or the original URL if static storage is disabled
     */
    protected function buildStaticUrl($url)
    {
        // Nothing to do if static storage is disabled
        if (empty($this->staticStorageUrl)) {
            return $url;
        }
        
        // TODO: There has to be a better way than this...
        return str_replace($this->buildHomeUrl(), $this->staticStorageUrl, $url);
    }
}

/* EOF */