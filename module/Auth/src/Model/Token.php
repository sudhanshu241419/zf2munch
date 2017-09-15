<?php

namespace Auth\Model;

use MCommons\Model\AbstractModel;
use MCommons\StaticOptions;

class Token extends AbstractModel {
	public $id;
	public $token;
	public $user_details;
	public $user_id;
	public $ttl;
	public $created_at;
	public $last_update_timestamp;
	protected $_db_table_name = 'Auth\Model\DbTable\AuthTable';
	protected $_redisCache = false;
	public function __construct() {
		$this->_redisCache = StaticOptions::getRedisCache();
	}
	
	/**
	 * Return false if save was not successfull else return the updated object
	 *
	 * @return \Auth\Model\Token boolean
	 */
	public function save() {
		$data = $this->toArray ();
		
		if($this->_redisCache) {
			$this->_redisCache->setItem($this->token, $data);
			$data['id'] = $this->token;
			return $this;
		}
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		
		if (! $this->id) {
			$rowsAffected = $writeGateway->insert ( $data );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		
		// Get the last insert id and update the model accordingly
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		if ($rowsAffected >= 1) {
			$this->id = $lastInsertId;
			return $this;
		}
		return false;
	}
	public function delete() {
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		
		if (! $this->token) {
			throw new \Exception ( "Invalid token provided", 500 );
		} else {
			if($this->_redisCache) {
				if($this->_redisCache->hasItem($this->token)) {
					$this->_redisCache->removeItem($token);
					return 1;
				}
				return 0;
			}
			$rowsAffected = $writeGateway->delete ( array (
					'token' => $this->token 
			) );
		}
		return $rowsAffected;
	}
	public function findToken($token) {
		if($this->_redisCache) {
			if($this->_redisCache->hasItem($token)) {
				$data = $this->_redisCache->getItem($token);
				$data['user_details'] = @serialize($data['user_details']);
                                $this->exchangeArray($this->_redisCache->getItem($token));
				return $this;
			}
			return false;
		}
        return $this->find(array(
            'where' => array(
                'token' => $token
            )
        ))->current();
	}
        
        public function findExpireTimeToken($token){
            if($this->_redisCache) {
			if($this->_redisCache->hasItem($token)) {
				$data = $this->_redisCache->getItem($token);
				$data['user_details'] = @serialize($data['user_details']);                                
                                $tokenExpireTime = (int) $data['ttl'] + (int) $data['last_update_timestamp'];
                                return $tokenExpireTime;
			}
			return false;
		}
                return false;
        }
}