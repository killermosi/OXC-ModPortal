<?php

/*
 * This tool builds a series of JS files containing language strings, as defined in the "definitions.php" file,
 * using the template defined in the template.txt file
 * 
 * Each lang file will contain the respective strings in a global JSON variable namd "Lang"
 * 
 * The files will be created/updated in the application's "public/js/lang" directory
 */

// Project root directory
define('ROOT_DIR', dirname(__FILE__, 4));

// Where to create the JS files
define('JS_DIR', ROOT_DIR . '/public/js/lang');

// Language map, an associative array having the format:
// [file_name_1 => [lang_key_1, lang_key_2, ....], file_name_2 => [lang_key_3, lang_key_4, ....], ...]
$langMap = require(dirname(__FILE__) . '/definitions.php');

// Translations
$lang = require(ROOT_DIR . '/module/OxcMP/locales/en_US.php');

$template = file_get_contents(dirname(__FILE__) . '/template.txt');

if (false === $template) {
    die('Failed to read file: template.txt' . PHP_EOL);
}

// Create the JS directory, if needed
if (!is_dir(JS_DIR) && !mkdir(JS_DIR, 0755, true)) {
    die ('Failed to create directory ' . JS_DIR . PHP_EOL) ;
}

// What to replace in the templates
$searchReplace = [
    '{copyYear}' => date('Y')
];


// Create each of the language files
foreach ($langMap as $fileName => $langStrings) {
    
    $fileStrings = [];
    
    foreach ($langStrings as $langString) {
        $fileStrings[] = $langString . ': ' . json_encode($lang[$langString]);
    }
    
    $filePath = JS_DIR . '/' . $fileName . '.js';
    
    $searchReplace['{langStrings}'] = implode(',' . PHP_EOL . '    ', $fileStrings);
    
    $fileContent = str_replace(array_keys($searchReplace), array_values($searchReplace), $template);
    
    if (false === file_put_contents($filePath, $fileContent)) {
        die ('Failed to create file ' . $filePath . PHP_EOL) ;
    }
    
    echo 'Created file ' . $filePath . PHP_EOL;
}

echo 'Successfully created ' . count($langMap) . ' language file(s)' . PHP_EOL;

/* EOF */