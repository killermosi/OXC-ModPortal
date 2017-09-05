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
 * Generate the module configuration based on a public configuration file,
 * a private configuration file, and mapping rules list
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class Config
{
    /**
     * Build the configuration by merging the private configuration with the public one
     * 
     * @param string $publicConfigPath  Path to the public configuration file (a .ini file)
     * @param string $privateConfigPath Path to the private configuration file (a .php file)
     * @param array  $configMapping     The public to private configuration mapping
     * @return array The merged configuration structure
     */
    public static function buildConfig($publicConfigPath, $privateConfigPath, array $configMapping)
    {
        return array_replace_recursive(
            require  $publicConfigPath,
            self::buildPublicConfig($privateConfigPath, $configMapping)
        );
    }
    
    /**
     * Build the public configuration
     * 
     * @param string $publicConfigPath Path to the public configuration file (a .ini file)
     * @param array  $configMapping    The public to private configuration mapping
     * @return array
     */
    private static function buildPublicConfig($publicConfigPath, array $configMapping)
    {
        // Read the config data
        $config = parse_ini_file($publicConfigPath, false, INI_SCANNER_TYPED);
        
        // Adjust the config data
        $adjustedConfig = [];
        
        foreach ($configMapping as $publicKey => $privateKey) {
            if (!isset($config[$publicKey])) {
                Log::notice('Missing config key: ', $publicKey);
                continue;
            }
            
            $adjustedConfig[$privateKey] = $config[$publicKey];
        }
        
        return self::dimensionalSplit($adjustedConfig);
    }
    
    /**
     * Transform a two dimensional array with keys containing the dot character "." 
     * to a multi-dimensional array by splitting the keys at each dot. Example:
     * <pre>
     * $input = [
     *     'aValue'                  => 'a',
     *     'section.someValue'       => 'b',
     *     'section.otherValue'      => 'c',
     *     'otherSection.value'      => 'd',
     *     'some.deep.nested.section => 'e'
     * ];
     * 
     * $output = [
     *     'aValue' => 'a',
     *     'section' => [
     *         'someValue'  => 'b',
     *         'otherValue' => 'c'
     *     ],
     *     'otherSection' => [
     *         'value' => 'd'
     *     ],
     *     'some' => [
     *         'deep' => [
     *             'nested' => [
     *                 'section' => 'e'
     *             ]
     *         ]
     *     ]
     * ];
     * </pre>
     * Code adapted from: http://stackoverflow.com/a/9636021/1111983
     * 
     * @param array $data An associative array with the data
     * @return array
     */
    private static function dimensionalSplit(array $data)
    {
        foreach ($data as $key => $value) {
            // Do not process if there are no dots in the key name
            if (false === strpos($key, '.')) {
                continue;
            }
            
            // Transform the key containing dots to a multi-dimensional array
            $levels = explode('.', $key);
            
            $array = array();
            $ref = &$array;
            
            foreach ($levels as $level) {
                $ref[$level] = array();
                $ref = &$ref[$level];
            }
            
            $ref = $value;
            
            // Merge the array obtained from the key name with the original one
            $data = array_merge_recursive($data, $array);
            
            // And remove the original key
            unset($data[$key]);
        }

        return $data;
    }
}

/* EOF */