<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Util;

use Doctrine\ORM\Id\AbstractIdGenerator;
use Doctrine\ORM\EntityManager;
use OxcMP\Entity\User;
use OxcMP\Util\Log;

/**
 * Generate a random ID for entities
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class IdGenerator extends AbstractIdGenerator
{
    private $entityManager;
    
    /**
     * Generates an identifier for an entity.
     *
     * @param EntityManager $em     Entity manager
     * @param mixed         $entity The entity to generate the identifier for
     * @return integer
     */
    public function generate(EntityManager $em, $entity)
    {
        $this->entityManager = $em;
        mt_srand();
        
        switch (get_class($entity)) {
            case User::class:
                return $this->generateForUser($entity);
            default:
                Log::error('Entity of type ', get_class($entity), ' is not supported by the ID generator');
                return null;
        }
    }
    
    /**
     * Generate a random ID for a user entity
     * 
     * @param User $user The entity
     * @return int
     */
    private function generateForUser(User $user)
    {
        Log::info('Generating random ID for a "User" entity');
        
        // Some configuration - TODO: move to global when implementig other entities with randonm IDs
        $rangeMin    = $user->getConfig()->id_generator->range_min;
        $rangeMax    = $user->getConfig()->id_generator->range_max;
        $attemptsMax = $user->getConfig()->id_generator->attempts_max;
        
        // Attempts counter
        $attempts = 0;
        // List of generated IDs, to avoid duplicate checks
        $ids = [];
        
        // Generate
        while ($attempts < $attemptsMax) {
            // Count the attempts
            $attempts++;
            
            // Roll the dice
            $id = mt_rand($rangeMin, $rangeMax);
            
            if (in_array($id, $ids)) {
                continue;
            }
            
            // Check for jackpot
            $dbUser = $this->entityManager->getRepository(User::class)->find($id);
            
            if (!$dbUser instanceof User) {
                Log::debug('Generated ID: ', $id);
                return $id;
            }
            
            // Add it to the generated IDs list
            $ids[] = $id;
        }
        
        Log::critical('Failed to find a random ID after ', $attempts, ' attempts');
        return null;
    }
}

/* EOF */