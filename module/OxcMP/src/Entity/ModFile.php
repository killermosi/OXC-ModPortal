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
 * ModFile Entity
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @ORM\Table(name="mod_file")
 * @ORM\HasLifecycleCallbacks
 */
class ModFile
{
    /*
     * File type
     */
    const TYPE_RESOURCE   = 0;
    const TYPE_IMAGE      = 1;
    const TYPE_BACKGROUND = 2;
    
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
     * File order, for gallery images
     * @var integer
     * 
     * @ORM\Column(name="image_order", type="integer", nullable=false)
     */
    private $imageOrder = 0;
    
    /**
     * The original file name, must be unique per mod_id and type
     * @var string
     * 
     * @ORM\Column(name="name", type="string", length=256, nullable=false)
     */
    private $name;
    
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
     * Get the internal identifier
     * 
     * @return Uuid
     */
    public function getId()
    {
        return $this->id;
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
    public function setImageOrder($imageOrder)
    {
        $this->imageOrder = $imageOrder;
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
     * PrePersist callback
     * 
     * @return void
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->dateAdded = new \DateTime();
    }
}

/* EOF */