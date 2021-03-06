<?php

namespace FHJ\Entities;

/**
 * Project
 * @package FHJ\Entities
 */
class Project extends BaseEntity {
    
    /**
     * @var int
     */ 
    private $id;
    
    /**
     * @var int
     */ 
    private $userId;
    
    /**
     * @var boolean
     */ 
    private $enabled;
    
    /**
     * @var string
     */ 
    private $facebookGroupId;
    
    /**
     * @var string
     */ 
    private $secretKey = '';

    private $title;
    
    private $description;
    
    /**
     * @var string
     */ 
    private $lastBuildState = '';

    public function __construct($id, $userId, $facebookGroupId, $enabled = false, $secretKey = '', $title = '',
                                $description = '', $lastBuildState = '') {
        $this->setId($id);
        $this->setUserId($userId);
        $this->setFacebookGroupId($facebookGroupId);
        $this->setEnabled($enabled);
        $this->setSecretKey($secretKey);
        $this->setTitle($title);
        $this->setDescription($description);
        $this->setLastBuildState($lastBuildState);
    }

	private function setId($id) {
		if (!is_int($id)) {
			throw new \InvalidArgumentException('The id must be an integer');
		}

		$this->id = $id;
	}

    public function getId() {
        return $this->id;
    }
    
    public function setUserId($userId) {
        $this->checkInt($userId, 'userId');
        $this->userId = $userId;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function setEnabled($isEnabled) {
        $this->checkBoolean($isEnabled, 'isEnabled');
        $this->enabled = $isEnabled;
    }
    
    public function isEnabled() {
        return $this->enabled;
    }
    
    public function setFacebookGroupId($facebookGroupId) {
        if (empty($facebookGroupId)) {
            throw new \InvalidArgumentException('The facebookGroupId cannot be empty');
        }
        
        $this->facebookGroupId = $facebookGroupId;
    }
    
    public function getFacebookGroupId() {
        return $this->facebookGroupId;
    }
    
    public function setSecretKey($secretKey) {
        $this->secretKey = $secretKey;
    }
    
    public function getSecretKey() {
        return $this->secretKey;
    }

    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setLastBuildState($lastBuildState) {
        $this->lastBuildState = $lastBuildState;
    }
    
    public function getLastBuildState() {
        return $this->lastBuildState;
    }

}