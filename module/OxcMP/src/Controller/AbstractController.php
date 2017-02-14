<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Controller;

use Zend\Mvc\Controller\AbstractActionController;

/**
 * Collection of useful elements
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * @method mixed getService(string $service The service name) Retrieve the specified service
 * @method string translate(string $string The string to translate, mixed $values Additional values to format the translated string with) Translate a string
 */
class AbstractController extends AbstractActionController {
    
    /**
     * Add the specified title to the actual page title
     * 
     * @param type $title The translation key for the title
     * @return void
     */
    protected function addPageTitle($title)
    {
        $this->getService('ViewHelperManager')->get('headTitle')->prepend($this->translate($title));
    }
}
