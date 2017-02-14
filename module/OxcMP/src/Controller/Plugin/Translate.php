<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Translate the specified string
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class Translate extends AbstractPlugin{
    /**
     * Translate a string
     * 
     * @param string $string The string to translate
     * @param mixed  $values Additional values to format the translated string with
     * @return string
     */
    public function __invoke($string, ...$values) {
        $translate = $this->getController()->getService('ViewHelperManager')->get('translate');
        
        return sprintf($translate($string), ...$values);
    }
}

/* EOF */