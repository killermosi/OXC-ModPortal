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

namespace OxcMP\Service\Markdown;

use HTMLPurifier;
use HTMLPurifier_HTML5Config;
use Parsedown;
use OxcMP\Entity\Mod;
use OxcMP\Util\Log;

/**
 * Handle Markdown to HTML conversion and apply processing and formatting to the resulting HTML code
 * in order to make proper use of the Bootstrap templates
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @note TODO: Parsedown has known issues/vulnerabilities, and might not be quite the best option
 * @note for a PHP Markdown parser, but:
 * @note 1. It has a _lot_ of users on Packagist.org
 * @note 2. The author is still around (as of October 1 2017) 
 * @note 3. There are pull requests with fixes available
 * @note so I'll hang to this solution for now, but other options should be investigated
 */
class MarkdownService extends Parsedown
{
    /**
     * The HTML purifier instance
     * @var HTMLPurifier 
     */
    private $htmlPurifierInstance;
    
    /**
     * Class initialization
     */
    function __construct() {
        Log::info('Initializing MarkdownService');
    }
    
    /**
     * Build a Mod description from the raw version
     * 
     * @param Mod $mod The Mod entity
     * @return void
     */
    public function buildModDescription(Mod $mod)
    {
        Log::info('Building description for mod ', $mod->getId()->toString());
        
        if (!$mod->wasDescriptionRawChanged()) {
            Log::debug('The mod raw description was not changed, not building the description');
            return;
        }
        
        $description = $this->text($mod->getDescriptionRaw());
        
        // Purify the description (this should patch any XSS vulnerabilities in the generated HTML)
        // TODO: Check if additional HTMLPurifier configuration is needed
        // (like dumping out CSS tags - though they seem to not be allowed by default, or removing whitespace)
        $purifiedDescription = $this->getHtmlPurifierInstance()->purify($description);
        
        $mod->setDescription($purifiedDescription);
        
        Log::debug('New mod description built');
    }

    /**
     * Decorate a table block with additional CSS
     * 
     * @param string $line  The table line
     * @param array  $block The table block
     * @return string
     */
    protected function blockTable($line, array $block = null)
    {
        $table = parent::blockTable($line, $block);
        
        if (is_array($table) && !empty($table)) {
            $table['element']['attributes']['class'] = 'table table-bordered table-hover';
        }
        
        return $table;
    }
    /**
     * Decorate a quote with additional CSS
     * 
     * @param string $line The quote line
     * @return string
     */
    protected function blockQuote($line) {
        $quote = parent::blockQuote($line);
        
        if (is_array($quote) && !empty($quote)) {
            $quote['element']['attributes']['class'] = 'blockquote';
        }
        
        return $quote;
    }

    
    /**
     * Create (and configure) a new HTMLPurifier instance
     * 
     * @return HTMLPurifier
     */
    private function getHtmlPurifierInstance()
    {
        if ($this->htmlPurifierInstance instanceof HTMLPurifier) {
            Log::info('Using existing HTMLPurifier instance');
            return $this->htmlPurifierInstance;
        }
        
        Log::info('Creating HTMLPurifier instance');
        
        // Setup a custom cache directory, as HTMLPurifier tries to
        // write the cache in its vendor directory by default.
        // Note: The directory path must not contain a trailing slash
        // https://github.com/ezyang/htmlpurifier/issues/71
        $cacheDir = sys_get_temp_dir() . '/' . get_current_user() . '/HTMLPurifier/DefinitionCache';
        $localConfig = ['Cache.SerializerPath' => $cacheDir];
        
        if (
            (is_dir($cacheDir) && !is_writable($cacheDir))
            || (!is_dir($cacheDir) && !@mkdir($cacheDir, 0770, true))
        ) {
            Log::warn('Failed to create HTMLPurifier Definitions cache directory: ', $cacheDir);
            // We'll disable the cache to prevent HTMLPurifier warnings,
            // but this is a (small? medium?) performance hit
            $localConfig = ['Cache.DefinitionImpl' => null];
        }
        
        // Create a new purifier instance, with our custom config
        // TODO: Check if the cache config is really working, nothing seems to be written to the cache directory
        $htmlPurifierConfig = HTMLPurifier_HTML5Config::create($localConfig);
        $htmlPurifier = new HTMLPurifier($htmlPurifierConfig);
        
        // Cache it for later use, if needed
        $this->htmlPurifierInstance = $htmlPurifier;
        
        Log::debug('HTMLPurifier instance created');
        
        return $htmlPurifier;
    }
}
