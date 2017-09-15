<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class UserCard extends AbstractModel {
	public $id;
	public $user_id;
	public $card_number;
	public $card_type;
	public $name_on_card;
	public $expired_on;
	public $created_on;
	public $updated_at;
	public $status = 1;
	public $stripe_token_id;
	public $zipcode;
	public $stripe_user_id = NULL;
    public $encrypt_card_number = NULL;
	protected $_db_table_name = 'User\Model\DbTable\UserCardTable';
	public function __construct() {
		$this->created_on = date ( "Y-m-d H:i:s" );
		$this->updated_at = date ( "Y-m-d H:i:s" );
	}
	public function fetchUserCard($user_id = 0,$orderpass=false) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'id',
				'card_number',
				'card_type',
				'name_on_card',
				'expired_on',
				'stripe_token_id',
				'zipcode',
				'status',
                'encrypt_card_number'
		) );
		$where = new Where ();
        
        if($orderpass == 1){
            $where->notEqualTo('encrypt_card_number', '');            
        }
		$where->equalTo ( 'user_id', $user_id );
		$where->equalTo ( 'status', 1 );
		$select->where ( $where );
		$select->order ( 'created_on asc' );
        //var_dump($select->getSqlString($this->getPlatform('READ')));
		$userCardDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		$cc = $userCardDetails->toArray ();        
        return $cc;
	}
	public function getUserCardDetail($user_id = 0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'id',
				'card_number',
				'card_type',
				'name_on_card',
				'expired_on',
				'stripe_token_id',
				'status',
                'encrypt_card_number',
		) );
		$where = new Where ();
		$where->equalTo ( 'user_id', $user_id );
		$select->where ( $where );
		$select->order ( 'created_on asc' );
		$userCardDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $userCardDetails->toArray ();
	}
    
    public function getUserDecriptCard($user_id = false,$id=false) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'id',
				'card_number',
				'card_type',
				'name_on_card',
				'expired_on',
				'stripe_token_id',
				'status',
                'encrypt_card_number',
                'zipcode'
		) );
		$where = new Where ();
		$where->equalTo ( 'user_id', $user_id );
        $where->equalTo ( 'id', $id );
		$select->where ( $where );		
		$userCardDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $userCardDetails->toArray ();
	}
	public function addCard() {
		$data = $this->toArray ();
		
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		if (! $this->id) {
			$rowsAffected = $writeGateway->insert ( $data );
            $lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
            $lastInsertId = $this->id;
		}
		
		
		
		if ($rowsAffected >= 1) {
			$this->id = $lastInsertId;
			return $this->toArray ();
		}
		return false;
	}
	public function delete() {
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$data = array (
				'status' => 2 
		);
		if ($this->id == 0) {
			throw new \Exception ( "Invalid card detail", 400 );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		return $rowsAffected;
	}
	public function fetchUserCardByToken($token) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'id',
				'card_number',
				'card_type',
				'name_on_card',
				'expired_on',
				'status'
		) );
		$where = new Where ();
		$where->equalTo ( 'stripe_token_id', $token );
	//	$where->equalTo ( 'status', 1 );
		$select->where ( $where );
		//$select->order ( 'created_on asc' );
		$userCardDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current();
		return $userCardDetails->getArrayCopy();
	}
}