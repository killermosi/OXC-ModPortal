<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Service\User;

use OxcMP\Service\AbstractService;
use OxcMP\Entity\User;

/**
 * Handle users persistence
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class UserPersistenceService extends AbstractService
{
    /**
     * Create a user entry in the database
     * 
     * @param User $user The user entity
     * @return void
     */
    public function create(User $user)
    {
        
    }
}

/* EOF */