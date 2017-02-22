<?php

namespace OxcMP\Controller;

use Zend\View\Model\ViewModel;
use OxcMP\Service\User\UserRetrievalService;
use OxcMP\Service\User\UserPersistenceService;
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
        //Log::notice($this->getService(\OxcMP\Service\Config\ConfigService::class)->toArray());
        
        $user = new User();
        $memberId = rand(100, 1000);
        $user->setMemberId($memberId);
        
        /* @var $userPersistenceService UserPersistenceService */
        $userPersistenceService = $this->getService(UserPersistenceService::class);
        
        /* @var $userRetrievalService UserRetrievalService */
        $userRetrievalService = $this->getService(UserRetrievalService::class);
        
        $userPersistenceService->create($user);
        
        $dbUser = $userRetrievalService->findByMemberId($memberId);
        
        Log::debug($dbUser);
        
        return new ViewModel();
    }
}

/* EOF */