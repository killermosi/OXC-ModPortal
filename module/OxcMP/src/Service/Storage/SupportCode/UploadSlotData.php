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

namespace OxcMP\Service\Storage\SupportCode;

use InvalidArgumentException;
use OverflowException;
use Ramsey\Uuid\DegradedUuid as Uuid;
use OxcMP\Service\Storage\StorageService;

/**
 * Describes a temporary upload slot details
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 */
class UploadSlotData
{
    /**
     * The slot UUID
     * @var string
     */
    private $uuid;
    
    /**
     * The file type
     * @var integer
     */
    private $type;
    
    /**
     * The file size
     * @var integer
     */
    private $size;
    
    /**
     * The file name
     * @var string
     */
    private $name;
    
    /**
     * The total number of file chunks
     * @var integer 
     */
    private $chunksTotal;
    
    /**
     * The number of uploaded chunks
     * @var integer 
     */
    private $chunksUploaded = 0;
    
    /**
     * Class initialization
     * 
     * @param integer $type   File type
     * @param integer $size   File size, in bytes
     * @param string  $name   File name
     * @param integer $chunks Total number of chunks expected for this file
     * @throws InvalidArgumentException
     */
    function __construct($type, $size, $name, $chunks)
    {
        $this->type        = (int) $type;
        $this->size        = (int) $size;
        $this->name        = (string) $name;
        $this->chunksTotal = (int) $chunks;
        
        if (!in_array($this->type, StorageService::TYPE_MAP, true)) {
            throw new InvalidArgumentException('Invalid file type');
        }
        
        if ($this->size <= 0) {
            throw new InvalidArgumentException('Invalid file size');
        }
        
        if (strlen($this->name) == 0) {
            throw new InvalidArgumentException('Invalid file name');
        }
        
        if ($this->chunksTotal < 1) {
            throw new InvalidArgumentException('Invalid file chunks');
        }
        
        $this->uuid = Uuid::uuid4()->toString();
    }
    
    /**
     * Get the UUID
     * 
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
    
    /**
     * Get the file type
     * 
     * @return integer
     */
    function getType()
    {
        return $this->type;
    }

    /**
     * Get the file size
     * 
     * @return integer
     */
    function getSize()
    {
        return $this->size;
    }

    /**
     * Get the file name
     * 
     * @return type
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * Get the total number of chunks
     * 
     * @return integer
     */
    function getChunksTotal()
    {
        return $this->chunksTotal;
    }

    /**
     * Get the total number of uploaded chunks
     * 
     * @return integer
     */
    function getChunksUploaded()
    {
        return $this->chunksUploaded;
    }

    /**
     * Increment the number of uploaded chunks
     * 
     * @throws OverflowException
     */
    function incrementChunksUploaded()
    {
        if ($this->chunksUploaded == $this->chunksTotal) {
            throw new OverflowException('All chunks for this file have been already uploaded');
        }
        
        $this->chunksUploaded++;
    }
    
    /**
     * If the file was completely uploaded
     * 
     * @return boolean
     */
    public function isFileUploadCompleted()
    {
        return ($this->chunksUploaded == $this->chunksTotal);
    }
}

/* EOF */