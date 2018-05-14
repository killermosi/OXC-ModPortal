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

use SplFileInfo;
use Behat\Transliterator\Transliterator;

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

        $bytes /= (1 << (10 * $pow));

        return sprintf($units[$pow], round($bytes, $precision));
    }
    
    /**
     * Sanitize a file name
     * 
     * @param string $filename The filename to sanitize
     * @param string $ext The extension to use (optional)
     * @return string|null The sanitized file name or NULL if the filename could not be sanitized
     */
    public static function sanitizeFilename($filename, $ext = null)
    {
        $fileInfo = new SplFileInfo($filename);
        
        // Use the file extension if not explicitly specified
        if (is_null($ext)) {
            $ext = $fileInfo->getExtension();
        }
        
        $basename = $fileInfo->getBasename('.' . $fileInfo->getExtension());
        
        $returnName = Transliterator::transliterate($basename);
        
        // If a name could not be extracted, return null
        if (strlen($returnName) == 0) {
            return null;
        }
        
        $returnExt = (strlen($ext) == 0) ? '' : strtolower('.' . $ext);
        
        return $returnName . $returnExt;
    }
    
    /**
     * Convert a php.ini shorthand value (like 8M) to their byte values
     * 
     * @param string $value The value to convert
     * @return float
     */
    public static function convertPhpIniShorthandValue($value)
    {
        $number = (float) preg_replace('/[^0-9\.]/', '', $value);

        $unit = preg_replace('/[^kKmMgG]/', '', $value);
        
        switch (strtolower($unit)) {
            case 'g':
                return $number * 1024 * 1024 * 1024;
            case 'm':
                return $number * 1024 * 1024;
            case 'k':
                return $number * 1024;
            default:
                return $number;
        }
    }
    
    /**
     * Delete a directory and all files within (not directories)
     * 
     * @param string $path Directory path
     * @return array A list of paths that could not be deleted
     */
    public static function deleteDirectoryAndContents($path)
    {
        $errors = [];
        
        if (!is_dir($path)) {
            return $errors;
        }
        
        /* @var $fileInfo \DirectoryIterator */
        foreach ((new \DirectoryIterator($path)) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            
            if ($fileInfo->isDir()) {
                $errors[] = $fileInfo->getPathname();
                continue;
            }
            
            if (@unlink($fileInfo->getPathname()) === false) {
                $errors[] = $fileInfo->getPathname();
            }
        }
        
        if (@rmdir($path) === false) {
            $errors[] = $path;
        }
        
        return $errors;
    }
}

/* EOF */
