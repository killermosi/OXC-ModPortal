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
use Zend\Config\Config;
use OxcMP\Util\DateTime as  DateTimeUtil;

/**
 * User entity
 * TODO: move detail update population and update time due to service
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * Internal identifier
     * @var  \Ramsey\Uuid\DegradedUuid
     * 
     * @ORM\Id
     * @ORM\Column(name="user_id", type="uuid", nullable=false, unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;
    
    /**
     * If this user is orphan
     * @var boolean
     * 
     * @ORM\Column(name="is_orphan", type="boolean", nullable=false)
     */
    private $isOrphan = false;
    
    /**
     * Forum member identifier
     * @var integer 
     * 
     * @ORM\Column(name="member_id", type="integer", nullable=false, unique=true)
     */
    private $memberId;
    
    /**
     * Forum authentication token
     * @var string
     * 
     * @ORM\Column(name="authentication_token", type="string", length=64, nullable=true)
     */
    private $authenticationToken;
    
    /**
     * Display name
     * @var string
     * 
     * @ORM\Column(name="real_name", type="string", length=128, nullable=true)
     */
    private $realName;
    
    /**
     * Personal text
     * @var string 
     * 
     * @ORM\Column(name="personal_text", type="string", length=128, nullable=true)
     */
    private $personalText;
    
    /**
     * If the user is an administrator
     * @var boolean
     * 
     * @ORM\Column(name="is_administrator", type="boolean", nullable=false)
     */
    private $isAdministrator = false;
    
    /**
     * The member avatar URL
     * @var string
     * 
     * @ORM\Column(name="avatar_url", type="string", length=256, nullable=true)
     */
    private $avatarUrl;
    
    /**
     * The last date and time when the authentication token was validated
     * @var \DateTime 
     * 
     * @ORM\Column(name="last_token_check_date", type="datetime", nullable=false)
     */
    private $lastTokenCheckDate;
    
    /**
     * The last date and time when the member details were updated
     * @var \Datetime
     * 
     * @ORM\Column(name="last_detail_update_date", type="datetime", nullable=false) 
     */
    private $lastDetailUpdateDate;
    
    /**
     * Get the internal identifier
     * 
     * @return \Ramsey\Uuid\DegradedUuid
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Get if the user is orphan
     * 
     * @return boolean
     */
    public function getIsOrphan()
    {
        return $this->isOrphan;
    }

    /**
     * Set if the user is orphan
     * 
     * @param boolean $isOrphan If the user is orphan
     * @return void
     */
    public function setIsOrphan($isOrphan)
    {
        $this->isOrphan = $isOrphan;
    }

    /**
     * Get the forum member ID
     * 
     * @return integer
     */
    public function getMemberId()
    {
        return $this->memberId;
    }

    /**
     * Set the forum member ID
     * 
     * @param integer $memberId The forum member ID
     * @return void
     */
    public function setMemberId($memberId)
    {
        $this->memberId = $memberId;
    }

    /**
     * Get the authentication token
     * 
     * @return string
     */
    public function getAuthenticationToken()
    {
        return $this->authenticationToken;
    }

    /**
     * Set the authentication token
     * 
     * @param string $authenticationToken The new authentication token
     * @return void
     */
    public function setAuthenticationToken($authenticationToken)
    {
        $this->authenticationToken = $authenticationToken;
    }

    /**
     * Get the real name
     * 
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * Get the personal text
     * 
     * @return string
     */
    public function getPersonalText()
    {
        return $this->personalText;
    }

    /**
     * Get if the user is an administrator
     * 
     * @return boolean
     */
    public function getIsAdministrator()
    {
        return $this->isAdministrator;
    }
    
    /**
     * Get the avatar URL
     * 
     * @return string
     */
    public function getAvatarUrl()
    {
        return $this->avatarUrl;
    }
    
    /**
     * Get the last token check date and time
     * 
     * @return \DateTime
     */
    public function getLastTokenCheckDate()
    {
        if (is_null($this->lastTokenCheckDate)) {
            $this->lastTokenCheckDate = DateTimeUtil::newDateTimeUtc('1 Jan 1971');
        }
        
        return $this->lastTokenCheckDate;
    }

    /**
     * Update the last token check date and time
     * 
     * @return void
     */
    public function updateLastTokenCheckDate()
    {
        $this->lastTokenCheckDate = DateTimeUtil::newDateTimeUtc();
    }
    
    /**
     * Get the last date and time when the member details were updated
     * 
     * @return \DateTime
     */
    public function getLastDetailUpdateDate() {
        if (is_null($this->lastDetailUpdateDate)) {
            $this->lastDetailUpdateDate = DateTimeUtil::newDateTimeUtc('1 Jan 1971');
        }
        
        return $this->lastDetailUpdateDate;
    }

    /**
     * Update the current entity with the specified remote data
     * 
     * @param array $remoteData The remote data
     * @return void
     */
    public function updateDetails(array $remoteData)
    {
        $this->realName = $remoteData['RealName'];
        $this->personalText = $remoteData['PersonalText'];
        $this->isAdministrator = $remoteData['IsAdministrator'];
        $this->avatarUrl = $remoteData['AvatarUrl'];
        
        // Update the last update date
        $this->lastDetailUpdateDate = DateTimeUtil::newDateTimeUtc();
    }
    
    /**
     * Check if the 
     * @param Config $config
     */
    public function isDueTokenCheck(Config $config)
    {
        $timeInterval = 'PT' . $config->userRemote->tokenCheckDelay . 'S';
        
        return ($this->lastTokenCheckDate <= DateTimeUtil::newDateTimeUtc()->sub(new \DateInterval($timeInterval)));
    }

    /**
     * Check if the details of this user are due for update
     * 
     * @param Config $config The configuration
     * @return boolean
     */
    public function isDueDetailsUpdate(Config $config)
    {
        $timeInterval = 'PT' . $config->userRemote->displayRefreshDelay . 'S';
        
        return ($this->lastDetailUpdateDate <= DateTimeUtil::newDateTimeUtc()->sub(new \DateInterval($timeInterval)));
    }
    
    /**
     * PrePersist callback
     * 
     * @return void
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        // Set the default last detail and token check update date
        $date = DateTimeUtil::newDateTimeUtc('1 Jan 1971');
        
        if (is_null($this->lastDetailUpdateDate)) {
            $this->lastDetailUpdateDate = $date;
        }
        
        if (is_null($this->lastTokenCheckDate)) {
            $this->lastTokenCheckDate = $date;
        }
    }
}

/* EOF */