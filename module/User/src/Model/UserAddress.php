<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class UserAddress extends AbstractModel {
	public $id;
	public $user_id;
	public $address_name;
	public $email = NULL;
	public $phone;
	public $mobile = NULL;
	public $street = NULL;
	public $city;
	public $zipcode = NULL;
	public $state;
	public $country;
	public $delivery_instructions = NULL;
	public $takeout_instructions = NULL;
	public $address_type = NULL;
	public $created_on;
	public $updated_at;
	public $status;
	public $apt_suite;
    public $latitude;
    public $longitude;
    public $google_addrres_type;
        
	protected $_db_table_name = 'User\Model\DbTable\UserAddressTable';
	public function __construct() {
		$this->created_on = date ( "Y-m-d H:i:s" );
		$this->updated_at = date ( "Y-m-d H:i:s" );
	}
	public function update($data) {
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
        $rowAffected = $writeGateway->update ( $data, array (
				'id' => $this->id 
		) );
                
		return $rowAffected;
	}
	public function addAddress() {
		$data = $this->toArray ();    

		$writeGateway = $this->getDbTable ()->getWriteGateway ();		
		if (! $this->id) {
			$rowsAffected = $writeGateway->insert ( $data );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
            if ($rowsAffected >= 1) {
                return $this->toArray ();
            }
		}	
//        try{
//            $rowsAffected = $writeGateway->insert ($data);   
//        } catch (\Exception $e){
//           \Zend\Debug\Debug::dump($e->__toString()); exit; 
//        }
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		if ($rowsAffected >= 1) {
			$this->id = $lastInsertId;
			return $this->toArray ();
		}
		return false;
	}
	public function updateUserAddress() {
		$data = array (
				'address_name' => $this->address_name,
				'street' => $this->street,
				'state' => $this->state,
				'city' => $this->city,
				'phone' => $this->phone,
				'apt_suite' => $this->apt_suite,
				'zipcode' => $this->zipcode,
				'apt_suite' => $this->apt_suite,
				'updated_at' => $this->updated_at 
		);
		
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$dataUpdated = array ();
		if ($this->id == 0) {
			throw new \Exception ( "Invalid address id provided", 400 );
		} else {
			$dataUpdated = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		
		if (! $dataUpdated) {
			throw new \Exception ( "Data Not Updated", 424 );
		}
		
		return $data;
	}
	public function insert($data) {
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$writeGateway->insert ( $data );
		$data ['id'] = $writeGateway->getLastInsertValue ();
		return $data;
	}
	public function getUserAddressDetail($user_id = 0, $city_name=false) {
		$select = new Select ();
		$select->columns ( array (
				'id',
				'user_id',
				'address_name',
				'email',
				'phone',
				'mobile',
				'street',
				'city',
				'zipcode',
				'state',
				'country',
				'delivery_instructions',
				'takeout_instructions',
				'address_type',
				'apt_suite',
                'latitude',
                'longitude'
		) );
		$select->from ( $this->getDbTable ()->getTableName () );
		$where = new Where ();
		$where->equalTo ( 'user_id', $user_id );
        $where->equalTo ( 'city', $city_name );
        $where->equalTo ( 'status', 1 );
        $where->notEqualTo('latitude', 0);
        $where->notEqualTo('longitude', 0);
		$select->where ( $where );
		$select->order ( 'id desc' );
        $select->group('latitude','longitude');
                $userAddressDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $userAddressDetails->toArray ();
	}
	public function getUserAddressInfo(array $options=array()){
		$this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->current();
	}
	public function addressExist($user_id = 0, $address = 0, $address_type = 0, $email = 0,$apt_suite,$city,$state) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$where = new Where ();
		$where->equalTo ( 'user_id', $user_id );
		$where->equalTo ( 'street', $address );
		$where->equalTo ( 'address_type', $address_type );
        $where->equalTo ('apt_suite',$apt_suite);
        $where->equalTo ('city',$city);
        $where->equalTo ('state',$state);
		// $where->equalTo ( 'email', $email );
		$select->where ( $where );
		$select->order ( 'created_on asc' );
		// var_dump($select->getSqlString($this->getPlatform('READ')));
		// die;
		$userAddressDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $userAddressDetails->current ();
	}
	public function delete() {
        //$rowsAffected=$this->getDbTable()->getWriteGateway()->delete(array('id' => $this->id));
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$data = array (
				'status' => 2 
		);
		if ($this->id == 0) {
			throw new \Exception ( "Invalid reservation detail provided", 500 );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		return $rowsAffected;
	}
}