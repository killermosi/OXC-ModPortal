<?php

namespace OxcMP\Controller;

use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;
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
        /* @var $entityManager EntityManager */
        $entityManager = $this->getService('doctrine.entitymanager.orm_default');
        $user = $entityManager->getRepository(User::class)->find(1);
        Log::notice($user);
        return new ViewModel();
    }
}

/* EOF */