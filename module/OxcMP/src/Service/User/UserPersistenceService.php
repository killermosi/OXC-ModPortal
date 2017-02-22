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
 * Handle users persistence
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class UserPersistenceService
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
     * Create a user entry in the database
     * 
     * @param User $user The User entity
     * @return void
     */
    public function create(User $user)
    {
        Log::info('Creating new User');
        
        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($user);
            $this->entityManager->flush($user);
            $this->entityManager->commit();
        } catch (\Exception $exc) {
            Log::error('User creation failed: ', $exc->getMessage());
            try {
                $this->entityManager->rollback();
            } catch (Exception $exc) {
                Log::critical('Failed to rollback transaction: ', $exc->getMessage());
            }
            
            return;
        }
        
        Log::debug('User successfully created with ID: ', $user->getId());
    }
    
    /**
     * Update a user entry in the database
     * 
     * @param User $user The User entity
     * @return void
     */
    public function update(User $user)
    {
        Log::info('Updating user having the ID: ', $user->getId());
        
        try {
            $this->entityManager->beginTransaction();
            $this->entityManager->persist($user);
            $this->entityManager->flush($user);
            $this->entityManager->commit();
        } catch (\Exception $exc) {
            Log::error('User creation failed: ', $exc->getMessage());
            try {
                $this->entityManager->rollback();
            } catch (Exception $exc) {
                Log::critical('Failed to rollback transaction: ', $exc->getMessage());
            }
            
            return;
        }
        
        Log::debug('Successfully updated the user having the ID: ', $user->getId());
    }
    
    /**
     * Delete a user
     * 
     * @param User $user The user entity
     * @return void
     */
    public function delete(User $user)
    {
        throw new \Exception('User deletion not yet implemented');
    }
}

/* EOF */