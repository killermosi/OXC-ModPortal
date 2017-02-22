<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service\User;

use Doctrine\ORM\EntityManager;
use OxcMP\Entity\User;
use OxcMP\Util\Log;

/**
 * Handle users retrieval
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class UserRetrievalService
{
    /**
     * The Entity Manager
     * @var EntityManager 
     */
    private $entityManager;
    
    /**
     * Class initialization
     * 
     * @param EntityManager $em The entity manager
     */
    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }
    
    /**
     * Retrieve a user by its internal ID
     * 
     * @param integer $id The user internal ID
     * @return User
     */
    public function findById($id)
    {
        Log::info('Trying to retrieve the user having the ID: ', $id);
        
        $user = $this->entityManager->getRepository(User::class)->find($id);
        
        if ($user instanceof User) {
            Log::debug('User found');
        } else {
            Log::debug('User not found');
        }
        
        return $user;
    }
    
    /**
     * Retrieve a user by its forum member id
     * 
     * @param integer $memberId The forum member id
     * @return User
     */
    public function findByMemberId($memberId)
    {
        Log::info('Trying to retrieve the user having the Member ID: ', $memberId);
        
        $user = $this->entityManager->getRepository(User::class)->findOneBy(array('memberId' => $memberId));
        
        if ($user instanceof User) {
            Log::debug('User found');
        } else {
            Log::debug('User not found');
        }
        
        return $user;
    }
}

/* EOF */