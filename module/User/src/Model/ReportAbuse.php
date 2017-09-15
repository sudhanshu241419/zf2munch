<?php

namespace User\Model;

use MCommons\Model\AbstractModel;

class ReportAbuse extends AbstractModel {
	public $id;
	public $user_id;
	public $review_id = NULL;
	public $restaurant_id;
	public $created_on;
	public $abuse_type = NULL;
	public $comment;
	protected $_db_table_name = 'User\Model\DbTable\ReportAbuseTable';
	public function __construct() {
		$this->created_on = date ( "Y-m-d H:i:s" );
	}
	public function addReport() {
		$data = $this->toArray ();
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		if (! $this->id) {
			$rowsAffected = $writeGateway->insert ( $data );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		
		if ($rowsAffected >= 1) {
			$this->id = $lastInsertId;
			return $this->toArray ();
		}
		return false;
	}
}