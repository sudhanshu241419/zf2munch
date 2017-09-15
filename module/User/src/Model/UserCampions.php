<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class UserCampions extends AbstractModel {
	public $id;
	public $destination;
	public $campion_code;	
	public $created_at;
	public $status;
	
	protected $_db_table_name = 'User\Model\DbTable\UserCampionTable';
	
	public function addCampion() {
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
	
}