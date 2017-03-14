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

namespace OxcMP\Controller;

use Zend\View\Model\ViewModel;
use OxcMP\Service\Mod\ModRetrievalService;
use OxcMP\Util\Log;

/**
 * Description of ModController
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModController extends AbstractController
{
    /**
     * The mod retrieval service
     * @var ModRetrievalService 
     */
    private $modRetrievalService;
    
    /**
     * Class initialization
     * 
     * @param ModRetrievalService $modRetrievalService The mod retrieval service
     */
    function __construct(ModRetrievalService $modRetrievalService)
    {
        $this->modRetrievalService = $modRetrievalService;
    }

    /**
     * List all mods belonging to the user
     * 
     * @return ViewModel
     */
    public function myModsAction()
    {
        Log::info('Processing mod/my-mods action');
        
        return new ViewModel();
    }
}

/* EOF */