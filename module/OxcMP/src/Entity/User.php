<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace OxcMP\Entity;

use Doctrine\ORM\Mapping as ORM;

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
     * @ORM\Column(name="user_id", type="integer", nullable=false)
     */
    private $id;
    
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
     * Application config
     * @var Config
     */
    private $config;
    
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
     * Set the real name
     * 
     * @param string $realName The real name
     * @return void
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;
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
     * Set the personal text
     * 
     * @param string $personalText The personal text
     * @return void
     */
    public function setPersonalText($personalText)
    {
        $this->personalText = $personalText;
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
     * Set if the user is an administrator
     * 
     * @param boolean $isAdministrator The administrator status
     * @return void
     */
    public function setIsAdministrator($isAdministrator)
    {
        $this->isAdministrator = $isAdministrator;
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
     * Set the avatar URL
     * 
     * @param string $avatarUrl The URL
     * @return void
     */
    public function setAvatarUrl($avatarUrl)
    {
        $this->avatarUrl = $avatarUrl;
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
     * Update the last date and time when the member details were updated
     * 
     * @return void
     */
    public function updateLastDetailUpdateDate()
    {
        $this->lastDetailUpdateDate = new \DateTime();
    }
}

/* EOF */