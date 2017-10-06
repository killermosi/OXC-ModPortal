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

namespace OxcMP\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Ramsey\Uuid\DegradedUuid as Uuid;
use OxcMP\Entity\Mod;

/**
 * Mod repository
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class ModRepository extends EntityRepository
{
    /**
     * Retrieve the latest mods (by creation order)
     * 
     * @param integer $limit How many mods to retrieve
     * @return array
     */
    public function getLatestMods($limit)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        
        $queryBuilder->select('m')
            ->from(Mod::class, 'm')
            ->where('m.isPublished = 1')
            ->orderBy('m.creationDate', 'desc')
            ->setMaxResults($limit);
        
        return $queryBuilder->getQuery()->getResult();
    }
    
    /**
     * Retrieve all mods owned by the specified user ID
     * 
     * @param Uuid    $userId            The user identifier
     * @param boolean $publishedModsOnly If to retrieve only published mods
     * @return array
     */
    public function getModsByUserId(Uuid $userId, $publishedModsOnly = true)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        
        $queryBuilder->select('m')
            ->from(Mod::class, 'm')
            ->where('m.userId = :userId')
            ->orderBy('m.dateCreated', 'desc')
            ->setParameter('userId', $userId);
        
        if ($publishedModsOnly) {
            $queryBuilder->andWhere('m.isPublished = 1');
        }
        
        return $queryBuilder->getQuery()->getResult();
    }
}

/* EOF */