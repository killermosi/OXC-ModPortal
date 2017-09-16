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

namespace OxcMP\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\DegradedUuid as Uuid;

/**
 * ModVote Entity
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @ORM\Table(name="mod_vote")
 * @ORM\HasLifecycleCallbacks
 */
class ModVote
{
    const VOTE_DOWN = 0;
    const VOTE_UP   = 1;
    
    /**
     * The mod identifier
     * @var Uuid
     * 
     * @ORM\Id
     * @ORM\Column(name="file_id", type="uuid", nullable=false, unique=true)
     */
    private $modId;
    
    /**
     * The user identifier
     * @var Uuid
     * 
     * @ORM\Id
     * @ORM\Column(name="user_id", type="uuid", nullable=false)
     */
    private $userId;
    
    /**
     * The vote type: 0 - negative, 1 - positive
     * @var integer
     * 
     * @ORM\Column(name="vote", type="integer", nullable=false)
     */
    private $vote;
    
    /**
     * Date and time when the vote was cast
     * @var \DateTime 
     * 
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;
    
    /**
     * Get the mod identifier
     * 
     * @return Uuid
     */
    public function getModId()
    {
        return $this->modId;
    }

    /**
     * Set the mod identifier
     * 
     * @param Uuid $modId The mod identifier
     * @return void
     */
    public function setModId(Uuid $modId)
    {
        $this->modId = $modId;
    }

    /**
     * Get the user identifier
     * 
     * @return Uuid
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the user identifier
     * 
     * @param Uuid $userId The user identifier
     * @return void
     */
    public function setUserId(Uuid $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get the vote
     * 
     * @return integer
     */
    public function getVote()
    {
        return $this->vote;
    }

    /**
     * Set the vote
     * 
     * @param integer $vote The vote
     * @return void
     */
    public function setVote($vote)
    {
        $this->vote = $vote;
    }

    /**
     * Get the date
     * 
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
    
    /**
     * PrePersist callback
     * 
     * @return void
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->date = new \DateTime();
    }
}
