<?php
namespace User\Model;

use MCommons\Model\AbstractModel;

class UserDashboardNotification extends AbstractModel {
	public $id;
	public $user_id;
	public $notification_msg;
	public $type;
	public $restaurant_id;
	public $channel;
	public $created_on;
	
	protected $_db_table_name = 'User\Model\DbTable\PubnubDashboardNotificationTable';
	protected $_primary_key = 'id';
	public function createPubNubDashboardNotification($data){
		if(!empty($data)){
			if($data['type']=='reservation'){
				$type = 3;
			}elseif($data['type']=='order'){
			    $type = 2;
			}
			$dataArray = array(
					'user_id' => $data['userId'],
					'notification_msg' => $data['msg'],
					'type' => $type,
					//'read_status' => 0,
					'restaurant_id' => $data['restaurantId'],
					'channel' => $data['channel'],
					'created_on' => $data['curDate']
			);
			$writeGateway = $this->getDbTable ()->getWriteGateway ();
			$rowsAffected = $writeGateway->insert ( $dataArray );
			if($rowsAffected){
				return true;
			}
		}
	}
}