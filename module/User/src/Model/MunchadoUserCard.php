<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class MunchadoUserCard extends AbstractModel {
	public $id;
	public $user_id;
	public $card_number;
	public $card_type;
	public $name_on_card;
	public $expired_on;
	public $created_on;
	public $updated_at;
	public $status = 1;	
	public $zipcode;	
	protected $_db_table_name = 'User\Model\DbTable\MunchadoUserCardTable';
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
				'zipcode',
				'status',                
		) );
		$where = new Where ();        
		$where->equalTo ( 'user_id', $user_id );
		$where->equalTo ( 'status', 1 );
		$select->where ( $where );		
        //var_dump($select->getSqlString($this->getPlatform('READ')));
		$munchadoUserCardDetails = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		$cc = $munchadoUserCardDetails->toArray ();        
        return $cc;
	}	
}