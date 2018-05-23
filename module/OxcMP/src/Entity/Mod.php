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
use OxcMP\Util\DateTime as DateTimeUtil;

/**
 * Mod entity
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @ORM\Table(name="mod_data")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="\OxcMP\Entity\Repository\ModRepository")
 */
class Mod
{
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
     * Mod title
     * @var string
     * 
     * @ORM\Column(name="title", type="string", length=64, nullable=false)
     */
    private $title;
    
    /**
     * Mod summary
     * @var string
     * 
     * @ORM\Column(name="summary", type="string", length=256, nullable=true)
     */
    private $summary;
    
    /**
     * Mod description, compiled to HTML
     * @var string
     * 
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;
    
    /**
     * Mod description, as entered by the owner
     * @var string
     * 
     * @ORM\Column(name="description_raw", type="text", nullable=true)
     */
    private $descriptionRaw;
    
    /**
     * Mod slug
     * @var string
     * 
     * @ORM\Column(name="slug", type="string", length=128, nullable=false)
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
     * Six-digit code used to validate the delete operation
     * @var int
     * @ORM\Column(name="delete_code", type="integer", nullable=false)
     */
    private $deleteCode;
    
    /**
     * The initial title, set when the entity is loaded, used to determine if the title was changed
     * @var string 
     */
    private $initialTitle;
    
    /**
     * The initial raw description, set when the entity is loaded, used to determine if the raw description was changed
     * @var string 
     */
    private $initialDescriptionRaw;
    
    /**
     * The initial slug, set when the entity is loaded, used to determine if the slug was changed
     * @var string 
     */
    private $initialSlug;
    
    /**
     * Class initialization
     */
    public function __construct()
    {
        // Set the initial date and time for dateCreated and dateUpdated
        $this->dateCreated = $this->dateUpdated = DateTimeUtil::newDateTimeUtc();
        
        // Generate delete code
        $this->deleteCode = mt_rand(100000, 999999);
    }
    
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
     * Get the mod description, compiled to HTML
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the mod description, compiled to HTML
     * 
     * @param string $description The description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the raw description text
     * 
     * @return string
     */
    public function getDescriptionRaw()
    {
        return $this->descriptionRaw;
    }

    /**
     * Set the raw description text
     * 
     * @param string $descriptionRaw The raw description
     * @return void
     */
    public function setDescriptionRaw($descriptionRaw)
    {
        $this->descriptionRaw = $descriptionRaw;
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
     * Get the code used to validate the delete operation
     * @return integer
     */
    public function getDeleteCode()
    {
        return $this->deleteCode;
    }

    /**
     * Check if the mod title was changed
     * 
     * @return boolean
     */
    public function wasTitleChanged()
    {
        return $this->title !== $this->initialTitle;
    }
    
    /**
     * Check if the mod raw description was changed
     * 
     * @return boolean
     */
    public function wasDescriptionRawChanged()
    {
        if (strlen($this->descriptionRaw) != strlen($this->initialDescriptionRaw)) {
            return true;
        }
        
        return $this->descriptionRaw !== $this->initialDescriptionRaw;
    }
    
    /**
     * Check if the mod slug was changed
     * 
     * @return string
     */
    public function wasSlugChanged()
    {
        return $this->slug !== $this->initialSlug;
    }
    
    /**
     * Get the initial slug for the mod
     * 
     * @return string
     */
    public function getInitialSlug()
    {
        return $this->initialSlug;
    }
    
    /**
     * Update the dateUpdated value
     * 
     * @return void
     */
    public function markUpdated()
    {
        $this->dateUpdated = DateTimeUtil::newDateTimeUtc();
    }
    
    /**
     * PreUpdate callback
     * 
     * @return void
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->markUpdated();
    }
    
    /**
     * PostLoad callback
     * 
     * @return void
     * @ORM\PostLoad
     */
    public function postLoad()
    {
        $this->initialTitle = $this->title;
        $this->initialDescriptionRaw = $this->descriptionRaw;
        $this->initialSlug = $this->slug;
    }
}

/* EOF */