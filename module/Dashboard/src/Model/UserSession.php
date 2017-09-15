<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use MCommons\StaticOptions;

class UserSession extends AbstractModel {
	public $id;
	public $token;
	public $dashboard_details;
	public $dashboard_id;
	public $ttl;
	public $created_at;
	public $last_update_timestamp;
    protected $_db_table_name = 'Auth\Model\DbTable\AuthTable';
	protected $_dashboard_data = array ();

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
		$prevUserDetails = @unserialize ( $this->dashboard_details );
		if (! $prevUserDetails) {
			$prevUserDetails = array ();
		}
		$this->_dashboard_data = @array_replace_recursive ( $prevUserDetails, $this->_dashbord_data );
		$data ['dashboard_details'] = @serialize ( $this->_dashboard_data );

		$writeGateway = $this->getDbTable ()->getWriteGateway ();

		if (! $this->token) {
			throw new \Exception ( "Invalid user. User may have expired", 403 );
		} else {
			if($this->_redisCache) {
				$this->_redisCache->replaceItem($this->token, $data);
			} else {
				$rowsAffected = $writeGateway->update ( $data, array (
					'token' => $this->token
				) );
			}
		}
		return true;
	}
	public function delete() {
		$writeGateway = $this->getDbTable ()->getWriteGateway ();

		if (! $this->token) {
			throw new \Exception ( "Invalid token provided", 500 );
		} else {
			if($this->_redisCache) {
				if( $this->_redisCache->hasItem($this->token) ) {
					$this->_redisCache->removeItem($this->token);
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
	public function getDashboardDetail($key = false, $defaultValue = false) {
		$prevUserDetails = @unserialize ( $this->dashboard_details );
		if (! $prevUserDetails) {
			$prevUserDetails = array ();
		}
		$allDetails = @array_replace_recursive ( $prevUserDetails, $this->_dashboard_data );
		if (! $key) {
			return $allDetails;
		}
		if (! isset ( $allDetails [$key] )) {
			return $defaultValue;
		}
		return $allDetails [$key];
	}
	/**
	 * Set the details of user
	 *
	 * @param string $key
	 * @param string $value
	 * @throws \Exception
	 * @return \Auth\Model\UserSession
	 */
	public function setUserDetail($key = false, $value = false) {
		if (is_array ( $key )) {
			foreach ( $key as $k => $v ) {
				$this->_dashboard_data [$k] = $v;
			}
		} else if (! $key) { //|| $value
			throw new \Exception ( "Invalid key sent for setting user detail" );
		} else {
			$this->_dashboard_data [$key] = $value;
		}
		return $this;
	}
	public function isLoggedIn() {
		if (( int ) $this->dashboard_id) {
			return true;
		}
		return false;
	}
	public function getUserId() {
		return ( int ) $this->dashboard_id;
	}
	public function setUserId($id) {
		$this->dashboard_id = ( int ) ($id);
		return $this;
	}   
}