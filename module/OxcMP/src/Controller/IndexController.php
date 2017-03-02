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
use Zend\Session\SessionManager;
use OxcMP\Service\User\UserRetrievalService;
use OxcMP\Service\User\UserPersistenceService;
use OxcMP\Service\User\UserRemoteService;
use OxcMP\Entity\User;
use OxcMP\Util\Log;

/**
 * Primary application controller
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class IndexController extends AbstractController
{
    public function indexAction()
    {
        /* @var $userRemoteService UserRemoteService */
//        $userRemoteService = $this->getService(UserRemoteService::class);
//        
//        $userRemoteService->getDisplayData(15);
        
        //$this->session()->blabla = 'ahahaah';
        
        //Log::info($this->session()->n);
//        $session = $this->getService('OxcMpSession');
//        
//        $session->test = 'value';
//        
//        Log::info($session->test);
        
        //Log::notice($this->getService(\OxcMP\Service\Config\ConfigService::class)->toArray());
        
//        $user = new User();
//        $memberId = rand(100, 1000);
//        $user->setMemberId($memberId);
//        
//        /* @var $userPersistenceService UserPersistenceService */
//        $userPersistenceService = $this->getService(UserPersistenceService::class);
//        
//        /* @var $userRetrievalService UserRetrievalService */
//        $userRetrievalService = $this->getService(UserRetrievalService::class);
//        
//        $userPersistenceService->create($user);
//        
//        $dbUser = $userRetrievalService->findByMemberId($memberId);
//        
//        Log::debug($dbUser);
        
        return new ViewModel();
    }
    
    public function authorizationAction()
    {
        Log::notice('Authorization');
    }
}

/* EOF */