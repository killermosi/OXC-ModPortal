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
use Ramsey\Uuid\DegradedUuid as Uuid;
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
        Log::info('Initializing UserRetrievalService');
        
        $this->entityManager = $em;
    }
    
    /**
     * Retrieve a user by its internal ID
     * 
     * @param string $id The user internal ID
     * @return User
     */
    public function findById(Uuid $id)
    {
        Log::info('Trying to retrieve the user having the ID: ', $id->toString());
        
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
        
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['memberId' => $memberId]);
        
        if ($user instanceof User) {
            Log::debug('User found');
        } else {
            Log::debug('User not found');
        }
        
        return $user;
    }
}

/* EOF */