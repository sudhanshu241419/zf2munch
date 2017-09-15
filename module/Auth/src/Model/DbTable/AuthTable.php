<?php

namespace Auth\Model\DbTable;

use Auth\Model\Auth;
use MCommons\Model\DbTable\AbstractDbTable;

class AuthTable extends AbstractDbTable {
	protected $_table_name = "auth";
	protected $_array_object_prototype = 'Auth\Model\Auth';
	public function fetchAll() {
		$resultSet = $this->getReadGateway ()->select ();
		return $resultSet;
	}
	public function save(Auth $auth) {
		$data = array (
				'token' => $auth->token,
				'user_details' => $auth->user_details,
				'user_id' => $auth->user_id,
				'ttl' => $auth->ttl,
				'created_at' => $auth->created_at,
				'last_update_timestamp' => $auth->last_update_timestamp 
		);
		
		$token = $auth->token;
		if ($token == 0) {
			$this->getWriteGateway ()->insert ( $data );
		} else {
			if ($this->get ( $token )) {
				$this->getWriteGateway ()->update ( $data, array (
						'last_update_timestamp' => $auth->last_update_timestamp 
				) );
			}
		}
	}
	public function delete($auth_id) {
		$this->getWriteGateway ()->delete ( array (
				'id' => $auth_id 
		) );
	}
	public function deleteByToken($token) {
		$this->getWriteGateway ()->delete ( array (
				'token' => $token 
		) );
	}
}