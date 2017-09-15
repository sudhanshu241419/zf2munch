<?php
namespace Restaurant\Model;
use MCommons\Model\AbstractModel;
class UserFeedback extends AbstractModel{
	public $id;
	public $review_id;
	public $user_id;
	public $feedback;
	protected $_db_table_name = 'Restaurant\Model\DbTable\UserFeedbackTable';
	
	public function addFeedback(){
		$data = $this->toArray();
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->insert ( $data );
		return true;
	}
}