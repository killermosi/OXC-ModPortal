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

namespace OxcMP\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\DegradedUuid as Uuid;

/**
 * Mod entity
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @ORM\Entity(repositoryClass="\OxcMP\Entity\Repository\ModRepository")
 * @ORM\Table(name="mod_data")
 * @ORM\HasLifecycleCallbacks
 */
class Mod
{
    /*
     * Base game types
     */
    const BASE_GAME_UFO  = 0;
    const BASE_GAME_TFTD = 1;
    
    /**
     * Internal identifier
     * @var Uuid
     * 
     * @ORM\Id
     * @ORM\Column(name="mod_id", type="uuid", nullable=false, unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;
    
    /**
     * The owner identifier
     * @var Uuid
     * 
     * @ORM\Column(name="user_id", type="uuid", nullable=false)
     */
    private $userId;
    
    /**
     * If the mod is published
     * @var boolean
     * 
     * @ORM\Column(name="is_published", type="boolean", nullable=false)
     */
    private $isPublished = false;
    
    /**
     * For which base game was this mod designed: 0 - UFO, 1 - TFTD
     * @var integer
     * 
     * @ORM\Column(name="base_game", type="integer", nullable=false)
     */
    private $baseGame = self::BASE_GAME_UFO;
    
    /**
     * Mod title
     * @var string
     * 
     * @ORM\Column(name="title", type="string", length=128, nullable=false)
     */
    private $title;
    
    /**
     * Mod summary
     * @var string
     * 
     * @ORM\Column(name="summary", type="string", length=256, nullable=false)
     */
    private $summary;
    
    /**
     * Mod description
     * @var string
     * 
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;
    
    /**
     * Mod slug
     * @var string
     * 
     * @ORM\Column(name="slug", type="integer", nullable=false)
     */
    private $slug;
    
    /**
     * The date and time when this mod was created
     * @var \DateTime 
     * 
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;
    
    /**
     * The date and time when this mod was updated
     * @var \DateTime 
     * 
     * @ORM\Column(name="date_updated", type="datetime", nullable=false)
     */
    private $dateUpdated;
    
    /**
     * Completed downloads for the mod
     * @var string
     * 
     * @ORM\Column(name="downloads", type="integer", nullable=false)
     */
    private $dowloads = 0;
    
    /**
     * Get the internal identifier
     * 
     * @return Uuid
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get the user ID
     * 
     * @return Uuid
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the user ID
     * 
     * @param integer $userId The user ID
     * @return void
     */
    public function setUserId(Uuid $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get the published status
     * 
     * @return boolean
     */
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Set the published status
     * 
     * @param boolean $isPublished The status
     * @return void
     */
    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
    }

    /**
     * Get the base game type
     * 
     * @return integer
     */
    public function getBaseGame()
    {
        return $this->baseGame;
    }

    /**
     * Set the base game type
     * 
     * @param integer $baseGame Game type
     * @return void
     */
    public function setBaseGame($baseGame)
    {
        $this->baseGame = $baseGame;
    }

    /**
     * Get the mod title
     * 
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the mod title
     * 
     * @param string $title The title
     * @return void
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get the mod summary
     * 
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set the mod summary
     * 
     * @param string $summary The summary
     * @return void
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;
    }
    
    /**
     * Get the mod description
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the mod description
     * 
     * @param string $description The description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the mod slug
     * 
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the mod slug
     * 
     * @param string $slug The slug
     * @return void
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get the date and time when this mod was created
     * 
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
    
    /**
     * Get the date and time when this mod was updated
     * 
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }
    
    /**
     * Completed downloads for the mod
     * 
     * @return integer
     */
    public  function getDowloads()
    {
        return $this->dowloads;
    }

    /**
     * Increment the completed downloads for the mod
     * 
     * @return void
     */
    public function incrDownloads()
    {
        $this->dowloads++;
    }

    /**
     * PreUpdate callback
     * 
     * @return void
     * @ORM\PreUpdate
     */
    public function PreUpdate()
    {
        // Set the date and time for dateCreated and dateUpdated as needed
        $date = new \DateTime();
        
        // Set the dateCreated if not aready set
        if (is_null($this->dateCreated)) {
            $this->dateCreated = $date;
        }
        
        // Update the dateUpdated before every update
        $this->dateUpdated = $date;
    }
}

/* EOF */