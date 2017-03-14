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

/**
 * Mod entity
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @ORM\Entity
 * @ORM\Table(name="mod_data")
 * @ORM\Entity(repositoryClass="\OxcMP\Entity\Repository\ModRepository")
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
     * @var integer
     * 
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="mod_id", type="integer", nullable=false, unique=true)
     */
    private $id;
    
    /**
     * The owner identifier
     * @var integer
     * 
     * @ORM\Column(name="user_id", type="integer", nullable=false)
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
     * For which base game was this mod designed:
     * 0 - UFO, 1 - TFTD
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
     * @ORM\Column(name="slug", type="integer", length=128, nullable=false)
     */
    private $slug;
    
    /**
     * The date and time when this mod was created
     * @var \DateTime 
     * 
     * @ORM\Column(name="creation_date", type="datetime", nullable=false)
     */
    private $creationDate;
    
    /**
     * MD5 hash of the image to use for page background
     * @var string
     * 
     * @ORM\Column(name="background", type="string", length=32, nullable=true)
     */
    private $background;
    
    /**
     * Class initialization
     */
    public function __construct()
    {
        $this->creationDate = new \DateTime();
    }
    
    /**
     * Get the internal identifier
     * 
     * @return integer
     */
    function getId()
    {
        return $this->id;
    }
    
    /**
     * Get the user ID
     * 
     * @return integer
     */
    function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set the user ID
     * 
     * @param integer $userId The user ID
     * @return void
     */
    function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Get the published status
     * 
     * @return boolean
     */
    function getIsPublished()
    {
        return $this->isPublished;
    }

    /**
     * Set the published status
     * 
     * @param boolean $isPublished The status
     * @return void
     */
    function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;
    }

    /**
     * Get the base game type
     * 
     * @return integer
     */
    function getBaseGame()
    {
        return $this->baseGame;
    }

    /**
     * Set the base game type
     * 
     * @param integer $baseGame Game type
     * @return void
     */
    function setBaseGame($baseGame)
    {
        $this->baseGame = $baseGame;
    }

    /**
     * Get the mod title
     * 
     * @return string
     */
    function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the mod title
     * 
     * @param string $title The title
     * @return void
     */
    function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get the mod description
     * 
     * @return string
     */
    function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the mod description
     * 
     * @param string $description The description
     * @return void
     */
    function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the mod slug
     * 
     * @return string
     */
    function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set the mod slug
     * 
     * @param string $slug The slug
     * @return void
     */
    function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get the creation date
     * 
     * @return \DateTime
     */
    function getCreationDate()
    {
        return $this->creationDate;
    }
    
    /**
     * Get the background image
     * 
     * @return string
     */
    function getBackground()
    {
        return $this->background;
    }

    /**
     * Set the background image
     * 
     * @param string $background the background image
     * @return void
     */
    function setBackground($background)
    {
        $this->background = $background;
    }
}

/* EOF */