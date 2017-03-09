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
use Zend\Config\Config;

/**
 * User entity
 *
 * @author Silviu Ghita <killermosi@yahoo.com>
 * 
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * Internal identifier
     * @var integer
     * 
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(name="user_id", type="integer", nullable=false, unique=true)
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
     * Constructor
     */
    public function __construct()
    {
        // Init token and update dates in the past, for safe measure
       $this->lastTokenCheckDate   = new \DateTime('1 Jan 1971');
       $this->lastDetailUpdateDate = new \DateTime('1 Jan 1971');
    }
    
    /**
     * Get the internal identifier
     * 
     * @return integer
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
    function getIsOrphan()
    {
        return $this->isOrphan;
    }

    /**
     * Set if the user is orphan
     * 
     * @param boolean $isOrphan If the user is orphan
     * @return void
     */
    function setIsOrphan($isOrphan)
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
        return $this->lastTokenCheckDate;
    }

    /**
     * Update the last token check date and time
     * 
     * @return void
     */
    public function updateLastTokenCheckDate()
    {
        $this->lastTokenCheckDate = new \DateTime();
    }
    
    /**
     * Get the last date and time when the member details were updated
     * 
     * @return \DateTime
     */
    function getLastDetailUpdateDate() {
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
        $this->lastDetailUpdateDate = new \DateTime();
    }
    
    /**
     * Check if the 
     * @param Config $config
     */
    public function isDueTokenCheck(Config $config)
    {
        $timeInterval = 'PT' . $config->userRemote->tokenCheckDelay . 'S';
        
        return ($this->lastTokenCheckDate <= (new \DateTime())->sub(new \DateInterval($timeInterval)));
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
        
        return ($this->lastDetailUpdateDate <= (new \DateTime())->sub(new \DateInterval($timeInterval)));
    }
}

/* EOF */