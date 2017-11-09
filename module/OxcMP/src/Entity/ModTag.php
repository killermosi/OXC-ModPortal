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
 * ModTag Entity
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @ORM\Entity
 * @ORM\Table(name="mod_tag")
 * @ORM\Entity(repositoryClass="\OxcMP\Entity\Repository\ModTagRepository")
 */
class ModTag
{
    /**
     * Mod identifier
     * @var Uuid
     * 
     * @ORM\Id
     * @ORM\Column(name="mod_id", type="uuid", nullable=false)
     */
    private $modId;
    
    /**
     * Tag
     * @var string
     * 
     * @ORM\Id
     * @ORM\Column(name="tag", type="string", length=32, nullable=false)
     */
    private $tag;
    
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
     * Get the tag
     * 
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Set the tag
     * 
     * @param string $tag The tag
     * @return void
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    }
}

/* EOF */