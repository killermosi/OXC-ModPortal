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
 * ModFile Entity
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @ORM\Entity
 * @ORM\Table(name="mod_file")
 * @ORM\HasLifecycleCallbacks
 */
class ModFile
{
    /*
     * File types
     */
    const TYPE_RESOURCE   = 0;
    const TYPE_IMAGE      = 1;
    const TYPE_BACKGROUND = 2;
    
    /*
     * Extensions
     */
    const EXTENSION_IMAGE    = 'png';
    const EXTENSION_RESOURCE = 'zip';
    
    /*
     * Image name format for cache
     */
    const IMAGE_CACHE_NAME = '{name}-{width}-{height}.' . self::EXTENSION_IMAGE;
    
    /*
     * Background image name
     */
    const BACKGROUND_NAME = 'background.' . self::EXTENSION_IMAGE;
    
    /*
     * Mime types
     */
    const MIME_RESOURCE = 'application/zip';
    const MIME_IMAGE    = 'image/png';
    
    /*
     * The image format to use
     */
    const IMAGE_FORMAT = 'png';
    
    /**
     * Internal identifier
     * @var Uuid
     * 
     * @ORM\Id
     * @ORM\Column(name="file_id", type="uuid", nullable=false, unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;
    
    /**
     * The user identifier
     * @var Uuid 
     * 
     * @ORM\Column(name="user_id", type="uuid", nullable=false)
     */
    private $userId;
    
    /**
     * The mod identifier
     * @var Uuid 
     * 
     * @ORM\Column(name="mod_id", type="uuid", nullable=false)
     */
    private $modId;
    
    /**
     * The file purpose: 0 - downloadable resource, 1 - gallery image, 2 - background image
     * @var integer
     * 
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type = self::TYPE_RESOURCE;
    
    /**
     * File order, for gallery images and resources
     * @var integer
     * 
     * @ORM\Column(name="file_order", type="integer", nullable=false)
     */
    private $fileOrder = 0;
    
    /**
     * The base file name, doubles as slug, must be unique per mod_id and type
     * @var string
     * 
     * @ORM\Column(name="name", type="string", length=128, nullable=false)
     */
    private $name;
    
    /**
     * A short file description, used for images as caption and for resources as details
     * @var string
     * 
     * @ORM\Column(name="description", type="string", length=256, nullable=true)
     */
    private $description;
    
    /**
     * Date and time when the file was uploaded
     * @var \DateTime
     * 
     * @ORM\Column(name="date_added", type="datetime", nullable=false)
     */
    private $dateAdded;
    
    /**
     * Completed downloads for the file
     * @var integer
     * 
     * @ORM\Column(name="downloads", type="integer", nullable=false)
     */
    private $downloads = 0;
    
    /**
     * File size, in bytes
     * 
     * @var integer
     * @ORM\Column(name="size", type="integer", nullable=false)
     */
    private $size = 0;
    
    /**
     * Temporary UUID for this file, used to retrieve the associated entry from the database
     * or from the temporary uploaded files
     * @var string
     */
    private $temporaryUuid;
    
    /**
     * If the entity was updated this session
     * @var boolean 
     */
    private $wasUpdated = false;
    
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
     * @param Uuid $userId The used identifier
     * @return void
     */
    public function setUserId(Uuid $userId)
    {
        $this->userId = $userId;
    }

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
     * Get the file type
     * 
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the file type
     * 
     * @param integer $type The file type
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Set the image order
     * 
     * @param integer $imageOrder The order
     * @return void
     */
    public function setFileOrder($imageOrder)
    {
        $this->fileOrder = $imageOrder;
    }
        
    /**
     * Get the file name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the file name
     * 
     * @param string $name The file name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * Get the file description
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the file description
     * 
     * @param string $description The description
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the date and time when the file was uploaded
     * 
     * @return \DateTime
     */
    public function getDateAdded()
    {
        return $this->dateAdded;
    }

    /**
     * Get the completed downloads for the file
     * 
     * @return integer
     */
    public function getDownloads()
    {
        return $this->downloads;
    }

    /**
     * Increment the downloads counter
     * 
     * @return void
     */
    public function incrDownloads()
    {
        $this->downloads++;
    }

    /**
     * Get the file size
     * 
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set the file size
     * 
     * @param integer $size The file size
     * @return void
     */
    public function setSize($size)
    {
        $this->size = $size;
    }
    
    /**
     * Set the temporary UUID
     * 
     * @param Uuid $uuid The UUID
     * @return void
     */
    public function setTemporaryUuid(Uuid $uuid)
    {
        $this->temporaryUuid = $uuid;
    }
    
    /**
     * Get the temporary UUID
     * 
     * @return Uuid
     */
    public function getTemporaryUuid()
    {
        return $this->temporaryUuid;
    }
    
    /**
     * Get if the entity was updated this session
     * 
     * @return boolean
     */
    public function wasUpdated()
    {
        return $this->wasUpdated;
    }
    
    /**
     * PrePersist callback
     * 
     * @return void
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->dateAdded = DateTimeUtil::newDateTimeUtc();
    }
    
    /**
     * PostUpdate callback
     * 
     * @return void
     * @ORM\PostUpdate
     */
    public function postUpdate()
    {
        $this->wasUpdated = true;
    }
}

/* EOF */