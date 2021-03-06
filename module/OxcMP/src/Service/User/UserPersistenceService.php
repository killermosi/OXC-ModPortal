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
     * @param EntityManager $entityManager The entity manager
     */
    public function __construct(EntityManager $entityManager)
    {
        Log::info('Initializing UserPersistenceService');
        
        $this->entityManager = $entityManager;
    }
    
    /**
     * Create an user entry in the database
     * 
     * @param User $user The User entity
     * @return void
     * @throws Exception\UserCannotCreateUserException
     */
    public function create(User $user)
    {
        Log::info('Creating new User');
        
        // Save the user to the database
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
            
            throw new Exception\UserCannotCreateUserException();
        }
        
        Log::debug('User successfully created with ID: ', $user->getId()->toString());
    }
    
    /**
     * Update a user entry in the database
     * 
     * @param User $user The User entity
     * @return void
     */
    public function update(User $user)
    {
        Log::info('Updating user having the ID: ', $user->getId()->toString());
        
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
        
        Log::debug('Successfully updated the user having the ID: ', $user->getId()->toString());
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