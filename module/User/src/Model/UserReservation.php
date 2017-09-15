<?php

namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Restaurant\Model\Restaurant;
use User\Model\User;
use User\UserFunctions;
use MCommons\StaticOptions;

class UserReservation extends AbstractModel {
	public $id;
	public $user_id;
	public $restaurant_id;
	public $seat_type_id;
	public $party_size;
	public $reserved_on;
	public $user_instruction;
	public $restaurant_comment;
	public $time_slot;
	public $meal_slot;
	public $status;
	public $restaurant_name;
	public $first_name;
	public $last_name;
	public $phone;
	public $email;
	public $reserved_seats;
	public $receipt_no;
	public $is_reviewed = 0;
    public $host_name;
    public $user_ip;
	protected $_db_table_name = 'User\Model\DbTable\UserReservationTable';
	protected $_primary_key = 'id';
    public $order_id;
    public $city_id = NULL;
    public $is_modify=0;
	public function getUserReservation(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		return $this->find ( $options )->toArray();
		
	}
	public function getAllReservation(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$reservations = $this->find ( $options )->toArray ();
		return $reservations;
	}
	public function getUserReservationToCheckSeat($options){
		//print_r($options);
		$reservationData =array();
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		
		$select->columns ( array (
				
				'time_slot',
				'reserved_seats',
				'party_size',
				'reserved_on',
				
		) );
		
		$where = new Where ();
		$where->in ( 'status', array(1,4) );
		if(isset($options['reservation_id']) && $options['reservation_id']){
			$where->notEqualTo('id',$options['reservation_id']);
		}
		if(isset($options['checkfrom']) && $options['type']=="backword"){
			$where->between('time_slot', $options['checkfrom'], $options['time_slot']);
			
		}elseif(isset($options['checkfrom']) && $options['type']=="forword"){
		    $where->between('time_slot', $options['time_slot'],$options['checkfrom']);
		}
		else{
			$where->equalTo ( 'time_slot', $options['time_slot'] );
		}
		
		if(isset($options['groupType']) && $options['groupType']=="small"){
			$where->lessThanOrEqualTo('reserved_seats', $options['smallGroupValue']);
			
		}elseif(isset($options['groupType']) && $options['groupType']=="large"){
			$where->greaterThan('reserved_seats', $options['smallGroupValue']);
		}
		
		$where->equalTo ( 'restaurant_id', $options['restaurant_id'] );
		$select->where ( $where );
		
		//var_dump($select->getSqlString($this->getPlatform('READ')));
		
		$reservationData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
				
		return $reservationData;
	}
	public function getReservationDetails($options = array()) {
		$reservationData =array();
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
	
		$select->columns ( array (
				'id',
				'restaurant_id',
				'restaurant_name',
				'status',
				'user_id',
				'time_slot',
				'reserved_seats',
				'party_size',
				'reserved_on',
				'user_instruction',
				'status',
				'first_name',
				'last_name',
				'phone',
				'email',
				'restaurant_comment',
				'is_reviewed',
				'review_id',
				'cron_status',
                'order_id'
		) );
	
		$where = new Where ();
		if (! empty ( $options ['reservationIds'] )) {
			$where->in ( 'user_reservations.id', $options ['reservationIds'] );
		} else {
			$where->equalTo ( 'user_reservations.user_id', $options ['userId'] );
		}		
		
		$select->join ( array (
				'rs' => 'restaurants'
		), 'rs.id =  user_reservations.restaurant_id', array (
				'address','city_id','zipcode','closed','inactive','accept_cc_phone'
		), $select::JOIN_INNER );
		
		$select->join ( array (
				'city' => 'cities'
		), 'city.id = rs.city_id', array (
				'city_name'
		), $select::JOIN_INNER );
		
		if (! empty ( $options ['orderBy'] )) {
			$select->order ( $options ['orderBy'] );
		}
		if (empty ( $options ['limit'] )) {
			$select->join ( array (
					'ci' => 'cities'
			), 'ci.id =  rs.city_id', array (
					'city_name'
			), $select::JOIN_INNER );
			/* $where->in ( 'user_reservations.status', $options ['status'] )->AND
			->greaterThan ( 'user_reservations.time_slot', $options ['currentDate'] ); */
			$where->in ( 'user_reservations.status', $options ['status'] )->AND
			->equalTo('cron_status', 0);
		}		
		
		if (! empty ( $options ['limit'] )) {
			/* $where->NEST->NEST->in ( 'user_reservations.status', $options ['status'] )->AND
				->lessThan ( 'user_reservations.time_slot', $options ['currentDate'] )->UNNEST
				->OR->NEST->equalTo('user_reservations.status', 2)->UNNEST->UNNSET; */
			$where->equalTo('cron_status', 1);
			$select->limit ( $options ['limit'] );
		}
		if (! empty ( $options ['offset'] )) {
			$select->offset ( $options ['offset'] );
		}
		$select->where ( $where );
		
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$reservationData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
	
		return $reservationData;
	}
	public function reserveTable() {
		$data = $this->toArray ();
		
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
			throw new \Exception ( "Invalid reservation detail provided", 500 );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		return $rowsAffected;
	}
	public function updateReservation() {
		$data = array (
				'first_name' => $this->first_name,
				'last_name' => $this->last_name,
				'email' => $this->email,
				'phone' => $this->phone,
				'party_size' => $this->party_size,
				'reserved_seats' => $this->reserved_seats,
				'reserved_on' => $this->reserved_on,
				'time_slot' => $this->time_slot 
		);
		
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$dataUpdated = array ();
		if ($this->id == 0) {
			throw new \Exception ( "Invalid reservation ID provided", 500 );
		} else {
			$dataUpdated = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		
		if (! $dataUpdated) {
			throw new \Exception ( "Data Not Updated", 424 );
		}
		
		return $this->toArray ();
	}
	public function getTotalReservationOfUser(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				
				'total_reservation' => new Expression ( 'COUNT(id)' ) 
		) );
		$select->where ( array (
				'user_id' => $options ['columns'] ['user_id'] 
		) );
		$select->where->in ( 'status', array (
				'0,1,4' 
		) );
		$select->group ( 'user_id' );
		
		// var_dump($select->getSqlString($this->getPlatform('READ')));
		
		$totalReview = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $totalReview;
	}
	public function getCurrentNotificationReservation($user_id = null, $currentDate) {
		$output = array ();
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'id',
				'reserved_on' 
		) );
		$select->where->equalTo ( 'user_id', $user_id );
		$select->where->greaterThan ( 'time_slot', $currentDate );
		$select->order ( 'time_slot DESC' );
		$select->limit ( 1 );
		$currentNotification = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		
		if (! empty ( $currentNotification )) {
			
			$output ['order_created_time'] = 'available';
		}
		return $output;
	}
	public function getCurrentReservation($options = array()) {
		$output = array ();
		// print_r($status);die;
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		
		$select->where->equalTo ( 'user_id', $options ['userId'] );
		//$select->where->greaterThan ( 'time_slot', $options ['currentDate'] );
		$select->where->equalTo('cron_status', 0);
		$select->where->in ( 'status', $options ['status'] );
		$select->order ( $options ['orderBy'] );
		$select->limit ( 1 );
		
		$currentReservation = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current ();
		
		if (! empty ( $currentReservation )) {
			
			$output = $currentReservation->getArrayCopy ();
		}
		return $output;
	}
	public function getReservationMultiId($userId, $id = null, $status = null, $currentDate = null,$flag=null) {
		$currentReservation = array ();
		if(!empty($flag)){
			$statusArray = array($status ['rejected'] );
		}
		else{
			$statusArray = array($status ['upcoming'],$status['confirmed'] );
		}
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->join ( array (
				'ui' => 'user_reservation_invitation' 
		), 'ui.reservation_id = user_reservations.id', array (
				'invitation_id' => 'id' 
		), $select::JOIN_INNER );
		$select->where->in ( 'user_reservations.id', $id );
		$select->where->equalTo('cron_status', 0);
                $select->where->equalTo('ui.to_id', $userId);
		//$select->where->greaterThan ( 'user_reservations.time_slot', $currentDate );
		$select->where->in ( 'user_reservations.status', $statusArray);
		$select->order('user_reservations.time_slot ASC');
		$select->limit ( 1 );
		
		$currentReservation = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current ();
		
		if ($currentReservation) {
			return $currentReservation->getArrayCopy ();
		}
		return $currentReservation;
	}
	public function getUserReservationDetails($user_id, $currentDate) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		
		$select->columns ( array (
				'id',
				'restaurant_name',
				'restaurant_id',
				'reserved_on' 
		) );
		$where = new Where ();
		$where->equalTo('user_id', $user_id)->AND->equalTo('is_reviewed', 0)->AND->equalTo('cron_status', 1)->AND->equalTo('status', 1);
		$select->where ( $where );
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$reservationData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		return $reservationData;		
	}
	public function userReservationDetail($reservation_id) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		
		$select->columns ( array (
				'id',
				'restaurant_name',
				'restaurant_id',
				'user_id',
                'status' 
		) );
		$where = new Where ();
		$where->equalTo ( 'user_reservations.id', $reservation_id );
		$select->where ( $where );
		// var_dump($select->getSqlString($this->getPlatform('READ')));die;
		
		$reservationDetail = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		return $reservationDetail->toArray ();
	}
	/**
	 * Mob Api Friend list of my reservation
	 * @param unknown $where
	 * @param unknown $data
	 * @return Ambigous <multitype:, multitype:NULL multitype: Ambigous <\ArrayObject, unknown> >
	 */
	public function getFriendListOfMyReservation($where, $data) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'reservation_id' => 'id',
				'restaurant_id',
				'time_slot',
				'reserved_seats',
				'party_size',
				'reserved_on',
				'user_instruction',
				'restaurant_name',
				'status' 
		) );
		$select->join ( array (
				'uri' => 'user_reservation_invitation' 
		), 'uri.reservation_id =  user_reservations.id', array (
				'invitaion_id' => 'id',
				'friend_id' => 'to_id',
				'friend_email' 
		), $select::JOIN_INNER );
		
		$where = new Where ();
		if ($where == 'email') {
			$where->equalTo ( 'user_reservations.email', $data );
		} else if ($where == 'userid') {
			$where->equalTo ( 'user_reservations.user_id', $data );
		}
		
		$select->where ( $where );
		
		// var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$friendList = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		return $friendList;
	}
	public function cancelReservation() {
		$data = array (
				'status' => $this->status,
				'cron_status' => 1 
		);
		
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$dataUpdated = array ();
		if ($this->id == 0) {
			throw new \Exception ( "Invalid reservation ID provided", 500 );
		} else {
			$dataUpdated = $writeGateway->update ( $data, array (
					'id' => $this->id
			) );
		}
		if($dataUpdated){
			return true;
		}
		else{
			return false;
		}
	}
	public function getTotalReservation(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				
				'total_reservation' => new Expression ( 'COUNT(id)' ),
				'user_id' 
		) );
		$where = new Where ();
		
		if(!empty($options['group'])){
			$where->in ( 'user_id', $options ['userId'] );
			$select->group('user_id');
		}
		else{
			$where->equalTo ( 'user_id', $options ['userId'] );
		}
		 if (! empty ( $options ['currentDate'] )) {
		/* 	$where->NEST->NEST->in ( 'status', $options ['status'] )
				->lessThan ( 'time_slot', $options ['currentDate'] )->UNNEST
				->OR->NEST->equalTo('user_reservations.status', 2)->UNNEST->UNNSET; */
		 	$where->equalTo('cron_status', 1);
		}
		else{
			$where->in ( 'status', $options ['status'] );
		}
		$select->where ( $where );
		
		//var_dump($select->getSqlString($this->getPlatform('READ'))); die();
		$totalReservation = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current ()->getArrayCopy ();
		return $totalReservation;
	}
	public function updateUserReservation() {
		$data = array (
				'first_name' => $this->first_name,
				'last_name' => $this->last_name,
				'email' => $this->email,
				'phone' => $this->phone,
				'party_size' => $this->party_size,
				'reserved_seats' => $this->reserved_seats,
				'time_slot' => $this->time_slot,
				'reserved_on' => $this->reserved_on,
				'is_read' => $this->is_read,
		        'status' =>$this->status,
		        'is_modify' =>$this->is_modify,                
		);
	
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$dataUpdated = array ();
		if ($this->id == 0) {
			throw new \Exception ( "Invalid reservation ID provided", 500 );
		} else {
			$dataUpdated = $writeGateway->update ( $data, array (
					'id' => $this->id,
					'user_id' => $this->user_id 
			) );
		}
		return $this->toArray ();
	}
	/**
	 * Mob api Archive reservation list
	 * @param unknown $options
	 * @return unknown|multitype:
	 */
	public function getReservationArchiceDetails($options = array()) {
		$total_archive_record=0;
        $select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
	
		$select->columns ( array (
				'reservation_id'=>'id',
                'receipt_no',
				'restaurant_id',
				'restaurant_name',
				'reservation_member_count'=>'party_size',
				'reservation_date'=>'time_slot',
				'reservation_created_on'=>'reserved_on',
				'first_name',
                'last_name',
                'phone',
                'email',
				'user_instruction',
                'order_id',
                'status',
                'cron_status',
	
		) );
		$select->join(
				array('uri'=>'user_reservation_invitation'),
				'uri.reservation_id=user_reservations.id',
				array('id','to_id','friend_email','message','msg_status'),
				$select::JOIN_LEFT
		);
        
        $select->join(
				array('u'=>'users'),
				'uri.to_id=u.id',
				array('invited_name'=>'first_name'),
				$select::JOIN_LEFT
		);
        
		$select->join(
				array('r'=>'restaurants'),
				'r.id=user_reservations.restaurant_id',
				array('rest_code','restaurant_image_name','address','zipcode','city_id','inactive','closed'),
				$select::JOIN_INNER
		);
		
		$select->join(
				array('c'=>'cities'),
				'c.id=r.city_id',
				array('city_name','state_code'),
				$select::JOIN_INNER
		);
		
		$where = new Where ();
		if (isset($options ['reservationIds']) && ! empty ( $options ['reservationIds'] )) {
			$where->in ( 'user_reservations.id', $options ['reservationIds'] );
			$invitationBy = 1;
		} else {
			$where->equalTo ( 'user_reservations.user_id', $options ['userId'] );
			$invitationBy = 0;
		}
		$where->equalto('cron_status', 1);
        $select->where ( $where );
	
		if (! empty ( $options ['orderBy'] )) {
			$select->order ( $options ['orderBy'] );
		}
			
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$reservationData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		//pr($reservationData,true);
		$response = array();
		if ($reservationData) {
			$reservationID = array_unique(array_map(function ($i)
			{
				return $i['reservation_id'];
			}, $reservationData));
		
			$i = 0;
			$response = array();
			foreach ($reservationData as $key => $value) {
		
				$response[] = $value;
			}
			
			$archiveStatus = "archive";
			$archieveReservation = $this->refineReservation($response, $reservationID, $archiveStatus, $invitationBy);
			$total_archive_record = count($archieveReservation);
            $archieveReservation = array_slice($archieveReservation, $options['offset'], $options['limit']);
			$user_function = new UserFunctions();
			$response = $user_function->ReplaceNullInArray($archieveReservation);
		}
		$response['archive_count'] = $total_archive_record;
        return $response;
        
	}
	
	/**
	 * Mob api Refine reservation
	 * @param unknown $userReservation
	 * @param unknown $reservationID
	 * @param unknown $status
	 * @param string $currentTime
	 * @return unknown
	 */
	private function refineReservation($userReservation, $reservationID,$status=false,$invitationBy,$currentTime = NULL,$varUserReservationId=0)
	{
		$response = array();
		$orders = array();
		$items = array();
		$orderData = array();
		$index = 0;
		$orderindex = 0;
		$i = 0;
        $invitationDetail = array();
		$userFunction = new UserFunctions();    
        
        
		//foreach ($reservationID as $k => $v) {
			           
			foreach ($userReservation as $key => $value) {
								
				//if ($v == $value['reservation_id']) {
					//$i++;
					$reservation[$value['reservation_id']]['reservation_id'] = $value['reservation_id'];
                    $reservation[$value['reservation_id']]['order_id'] = $value['order_id'];
                    $reservation[$value['reservation_id']]['receipt_no'] = $value['receipt_no'];
					$reservation[$value['reservation_id']]['restaurant_id'] = $value['restaurant_id'];
					$reservation[$value['reservation_id']]['restaurant_name'] = $value['restaurant_name'];
					$reservation[$value['reservation_id']]['is_restaurant_exist']=($value['inactive']==1 || $value['closed']==1)?"No":"Yes";
					$reservation[$value['reservation_id']]['reservation_member_count'] = $value['reservation_member_count'];
					$reservation[$value['reservation_id']]['reservation_date'] = $value['reservation_date'];
					$reservation[$value['reservation_id']]['reservation_created_date'] = $value['reservation_created_on'];
					$reservation[$value['reservation_id']]['first_name'] = $value['first_name'];
                    $reservation[$value['reservation_id']]['last_name'] = $value['last_name'];
                    $reservation[$value['reservation_id']]['email'] = $value['email'];
                    $reservation[$value['reservation_id']]['phone'] = $value['phone'];
                    $reservation[$value['reservation_id']]['cron_status']=($value['cron_status']==1)?(int)1:(int)0;
                    $statusDescription = '';
                        if($value['status']== 0){
                            $status = 'archived';
                            $statusDescription = 'Reservations Archive';
                        }elseif($value['status']== 1){
                            $status = 'upcoming';
                            $statusDescription = 'Upcoming Reservation'; 
                        }elseif($value['status']== 2){
                            $status = 'canceled';
                            $statusDescription = 'Reservations Cancel';
                        }elseif($value['status']== 3 && $value['status']){
                            $status = 'rejected';
                            $statusDescription = 'Rejected by the restaurant';
                        }elseif($value['status']== 4){
                            $status = 'confirmed';
                            $statusDescription = 'Confirmed by the restaurant';
                        }
                   
					$reservation[$value['reservation_id']]['reservation_status']=$status;
                    $reservation[$value['reservation_id']]['status_description']=$statusDescription;
					$userInstruction = rtrim(str_replace("||", ', ', $value['user_instruction']),', ');
					$reservation[$value['reservation_id']]['user_instruction'] = $userInstruction;
					$reservation[$value['reservation_id']]['restaurant_address'] = $value['address'].", ".$value['city_name'].", ".$value['state_code']." ".$value['zipcode'];
					if($invitationBy==1 && $value['msg_status']=='0'){
                        $reservation[$value['reservation_id']]['invitation_from_friend'] = (int)1;
                    }else{
                        $reservation[$value['reservation_id']]['invitation_from_friend'] = (int)0;
                    }
					if(isset($value['friend_email']) && !empty($value['friend_email']) && $value['friend_email']!=NULL && $value['msg_status']=='0'){
						$reservation[$value['reservation_id']]['reservation_is_invited'] =1;
                    if(empty($value['invited_name']) || $value['invited_name']==null){
                        $inviderDetail = explode('@',$value['friend_email']);
                    }else{
                        $inviderDetail[0] = $value['invited_name'];
                    }
                    $varToDo =(int) $value['to_id'];
                        if($value['msg_status']=='0' && isset($value['to_id']) && $varToDo>0){
                        $invitationDetail = array(
                            'id'=>$value['id'],
                            'invited_id'=>$value['to_id'],
                            'invited_email'=>$value['friend_email'],
                            'invited_name'=>$inviderDetail[0],
                            'user_message'=>$value['message'],
                            'msg_status'=>$value['msg_status']);
                        $reservation[$value['reservation_id']]['invitation'][]=$invitationDetail;
                        }else{
                          $reservation[$value['reservation_id']]['invitation']=array();  
                        }
						
					}else{                        
						$reservation[$value['reservation_id']]['reservation_is_invited'] =(int)0;
//						$invitationDetail = array('inviter_id'=>"",'inviter_email'=>"",'inviter_name'=>"",'invitation_description'=>"",'message'=>"",'msg_status'=>"");
						$reservation[$value['reservation_id']]['invitation']=array();    
												
					}
					
					if($invitationBy == 1 && $value['msg_status']=='0'){
						$typeOfMeal = $userFunction->getMealSlot( StaticOptions::getFormattedDateTime($value['reservation_date'], 'Y-m-d H:i:s', 'H:i:s'));
						$reservation[$value['reservation_id']]['invitation_description']=$typeOfMeal." invitation from ".$value['first_name'];
					}else{
                        $reservation[$value['reservation_id']]['invitation_description']="";
                    }
				//}
			}
			//$i=0;
            
		//}
	
		$key_index = 0;
		foreach ($reservation as $key => $reservationD) {
            
			$reservationResponse[$key_index] = $reservationD;
			$key_index ++;
		}
	
		return $reservationResponse;
	}
	
	public function getReservationUpcommingDetails($options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
	
		$select->columns ( array (
				'reservation_id'=>'id',
                'order_id',
                'receipt_no',
				'restaurant_id',
				'restaurant_name',
				'reservation_member_count'=>'party_size',
				'reservation_date'=>'time_slot',
				'reservation_created_on'=>'reserved_on',
				'first_name',
                'last_name',
                'phone',
                'email',
				'user_instruction',
                'status',
                'cron_status'
				) );
		$select->join(
				array('uri'=>'user_reservation_invitation'),
				'uri.reservation_id=user_reservations.id',
				array('id','to_id','friend_email','message','msg_status'),
				$select::JOIN_LEFT
		);
        
         $select->join(
				array('u'=>'users'),
				'uri.to_id=u.id',
				array('invited_name'=>'first_name'),
				$select::JOIN_LEFT
		);
		
		$select->join(
				array('r'=>'restaurants'),
				'r.id=user_reservations.restaurant_id',
				array('address','zipcode','city_id','inactive','closed'),
				$select::JOIN_LEFT
		);
		
		$select->join(
				array('c'=>'cities'),
				'c.id=r.city_id',
				array('city_name','state_code'),
				$select::JOIN_LEFT
		);
	
		$where = new Where ();
        $varUserReservationId=0;
		if (! empty ( $options ['reservationIds'] )) {
			$where->in ( 'user_reservations.id', $options ['reservationIds'] );
			$invitationBy = 1;
		} else {
            $varUserReservationId=$options ['userId'];
			$where->equalTo ( 'user_reservations.user_id', $options ['userId'] );
			$invitationBy = 0;
		}
		$where->in ( 'user_reservations.status', $options ['status'] )->AND->equalTo('cron_status', 0);
		$select->where ( $where );
		
		if (! empty ( $options ['orderBy'] )) {
			$select->order ( $options ['orderBy'] );
		}
        //pr($select->getSqlString($this->getPlatform('READ')),true);
		$reservationData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		
		$response = array();
		
		if ($reservationData) {
				$reservationID = array_unique(array_map(function ($i)
				{
					return $i['reservation_id'];
				}, $reservationData));
			
				$i = 0;
				$response = array();
				foreach ($reservationData as $key => $value) {
			
					$response[] = $value;
				}
				$upcommingStatus = false;		
				//print_r($response);
				$liveReservation = $this->refineReservation($response, $reservationID,$upcommingStatus,$invitationBy,'',$varUserReservationId);			
				$user_function = new UserFunctions();
				$response = $user_function->ReplaceNullInArray($liveReservation);
			}
			return $response;
		
	}
	public function update($data) {
		$this->getDbTable ()->getWriteGateway ()->update ( $data, array (
				'id' => $this->id
		) );
		return true;
	}
	public function getUserReservationCurrent(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		return $this->find ( $options )->current();
	}
	public function getTotalUserReservations($userId)
	{
		$select = new Select();
		$select->from($this->getDbTable()
				->getTableName());
		$select->columns(array(
	
				'total_reservation' => new Expression('COUNT(id)'),'user_id'
		));
		$select->where(array(
				'user_id' => $userId
		));
		 
		//$select->group('user_id');
	
		//var_dump($select->getSqlString($this->getPlatform('READ')));
	
		$totalOrder = $this->getDbTable()
		->setArrayObjectPrototype('ArrayObject')
		->getReadGateway()
		->selectWith($select);
		return $totalOrder->toArray();
	}
	public function getArchiveList($userId,$currentDate=null){
		
		$reservationData =array();
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		
		$select->columns ( array (
				'created_at' => 'reserved_on',
				'reservation_id' => 'id',
				'restaurant_name',
				'restaurant_id',
                'pre_orderid'=>'order_id'
		) );
		$select->join(
				array('r'=>'restaurants'),
				'r.id=user_reservations.restaurant_id',
				array('closed','inactive'),
				$select::JOIN_LEFT
		);
		$where = new Where ();
		$where->equalTo('user_id', $userId)->AND->equalTo('is_reviewed', 0)->AND->equalTo('cron_status', 1)->in('status',array(0,1,4));
	
		
		$select->where ( $where );
		
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$reservationData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		
		return $reservationData;
	}
	public function setReservationStatusArchived($response){
		$reservationModel = new UserReservation();
		
		$predicate = array (
				'id' => $response ['id']
		);
		$data = array (
				'cron_status' => 1,
                'status' => 0
		);
		$reservationModel->abstractUpdate ( $data, $predicate );
		
		
	}
	public function getTotalReservationForReview(array $options = array()) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
	
				'total_reservations' => new Expression ( 'COUNT(id)' ),
				'user_id'
		) );
		$where = new Where ();
	
		if(!empty($options['group'])){
			$where->in ( 'user_id', $options ['userId'] );
			$select->group('user_id');
		}
		else{
			$where->equalTo ( 'user_id', $options ['userId'] );
		}
		if (! empty ( $options ['currentDate'] )) {
			/* 	$where->NEST->NEST->in ( 'status', $options ['status'] )
			 ->lessThan ( 'time_slot', $options ['currentDate'] )->UNNEST
			->OR->NEST->equalTo('user_reservations.status', 2)->UNNEST->UNNSET; */
			$where->equalTo('cron_status', 1);
		}
		else{
			$where->in ( 'status', $options ['status'] );
		}
		$select->where ( $where );
	
		//var_dump($select->getSqlString($this->getPlatform('READ'))); die();
		$totalReservation = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		return $totalReservation;
	}
    public function getReservationDetailForMob($options){
        $select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
	
		$select->columns ( array (
				'reservation_id'=>'id',
                'order_id',
                'receipt_no',
				'restaurant_id',
				'restaurant_name',
				'reservation_member_count'=>'party_size',
				'reservation_date'=>'time_slot',
				'reservation_created_on'=>'reserved_on',
				'first_name',
                'last_name',
                'phone',
                'email',
				'user_instruction',
                'status',
                'is_reviewed',
                'review_id',
                'user_id'
				) );
		
		$select->join(
				array('r'=>'restaurants'),
				'r.id=user_reservations.restaurant_id',
				array('rest_code','restaurant_image_name','address','zipcode','city_id','inactive','closed'),
				$select::JOIN_LEFT
		);        
		
		$select->join(
				array('c'=>'cities'),
				'c.id=r.city_id',
				array('city_name','state_code'),
				$select::JOIN_LEFT
		);
	
		$where = new Where ();
		if (! empty ( $options ['reservationIds'] )) {
			$where->in ( 'user_reservations.id', $options ['reservationIds'] );
			$invitationBy = "1";
		} else {
			$where->equalTo ( 'user_reservations.user_id', $options ['userId'] );
			$invitationBy = "0";
		}
		$where->in ( 'user_reservations.status', $options ['status'] );
		//$where->greaterThanOrEqualTo( 'user_reservations.time_slot', $options ['currentDate'] );
		$select->where ( $where );
				
		//var_dump($select->getSqlString($this->getPlatform('READ')));die;
		$reservationData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray ();
		
		$response = array();
		
		if ($reservationData) {
				$reservationID = array_unique(array_map(function ($i)
				{
					return $i['reservation_id'];
				}, $reservationData));
			
				$i = 0;
				$response = array();
				foreach ($reservationData as $key => $value) {
			
					$response[] = $value;
				}
				$upcommingStatus = false;

			}
			return $response;
		
    }

    
     public function getUserReservationIds($user=false){
       $options=array('columns' => array('id','restaurant_id'),'where'=>array('user_id'=>$user,'status'=>'4','assignMuncher'=>'0')); 
       $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
       $userOrder = $this->find($options)->toArray();
       return $userOrder;
   }

    public function getTotalPrePaidReservations($userId) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_reservation' => new Expression('COUNT(id)')
        ));
        $where = new Where ();
        $where->equalTo('user_id', $userId);
        $where->notEqualTo('order_id', '');
        $where->in('status', array(4,0));
        
        $select->where($where);
        //pr($select->getSqlString($this->getPlatform('READ')),true);
        $response = $this->getDbTable()->getReadGateway()->selectWith($select)->toArray();
        return $response;
    }

    public function updateMuncher($data){
        $this->getDbTable ()->getWriteGateway ()->update ( $data, array (
				'id' => $this->id
		) );
		return true;
    }
    
    public function updateCronReservation($id=false){
        $this->getDbTable ()->getWriteGateway ()->update ( array('cronUpdate'=>1), array (
				'id' => $id
		) );
		return true;
    }
    public function getReservationInvitationDetails($options) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'reservation_id' => 'id',
            'order_id',
            'receipt_no',
            'restaurant_id',
            'restaurant_name',
            'reservation_member_count' => 'party_size',
            'reservation_date' => 'time_slot',
            'reservation_created_on' => 'reserved_on',
            'first_name',
            'last_name',
            'phone',
            'email',
            'user_instruction',
            'status',
            'is_reviewed',
            'review_id',
            'user_id'
        ));

        $select->join(
                array('r' => 'restaurants'), 'r.id=user_reservations.restaurant_id', array('address', 'zipcode', 'city_id', 'inactive', 'closed'), $select::JOIN_LEFT
        );
        $select->join(
                array('c' => 'cities'), 'c.id=r.city_id', array('city_name', 'state_code'), $select::JOIN_LEFT
        );
        $where = new Where ();
        if (!empty($options ['reservationIds'])) {
            $where->in('user_reservations.id', $options ['reservationIds']);
            $invitationBy = "1";
        }
        $where->in('user_reservations.status', $options ['status']);
        $where->greaterThanOrEqualTo('user_reservations.time_slot', $options ['currentDate']);
        $select->where($where);
        $reservationData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        $response = array();
        if ($reservationData) {
            $reservationID = array_unique(array_map(function ($i) {
                        return $i['reservation_id'];
                    }, $reservationData));

            $i = 0;
            $response = array();
            foreach ($reservationData as $key => $value) {

                $response[] = $value;
            }
            $upcommingStatus = false;
        }
        return $response;
    }
    
    public function updateCronOrder($id=false){
        $this->getDbTable ()->getWriteGateway ()->update ( array('cronUpdateForCancelation'=>1), array (
				'id' => $id
		) );
		return true;
    }
     public function getRestaurantReservations($restId, $startDate, $endDate) {
        $status = array('0', '4');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'restaurant_id',
            'user_id',
            'party_size',
            'status'
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->between('reserved_on', $startDate, $endDate);
        $select->where($where);
        $select->order('reserved_on DESC');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)->toArray();
        if (empty($data)) {
            return '';
        } else {
            return $data;
        }
    }
    public function getRestaurantTotalReservationsAndSeats($restId,$restStartDate,$restEndDate) {
        $status = array('0','1','2','3', '4');
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'restaurant_id',
            'total_reservation' => new Expression('COUNT(id)'),
            'total_seat' => new Expression('SUM(party_size)')
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->between('reserved_on', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)->toArray();
        if (empty($data)) {
            return '';
        } else {
            return $data[0];
        }
    }
    public function getRestaurantTotalSuccessReservations($restId,$restStartDate,$restEndDate) {
        $status = array('0','4');
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'success_reservations' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->between('reserved_on', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)->toArray();
        if (empty($data)) {
            return '';
        } else {
            return $data[0]['success_reservations'];
        }
    }
     public function getRestaurantTotalCancellations($restId,$restStartDate,$restEndDate) { 
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_cancellations' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('status', '2');
        $where->between('reserved_on', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)->toArray();
        if (empty($data)) {
            return '';
        } else {
            return $data[0]['total_cancellations'];
        }
    }
    public function getRestaurantNewVSReturningCustomers($restId,$restStartDate,$restEndDate) {
        $status = array('0', '2','3','4');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'restaurant_id',
            'total_reservations' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->between('reserved_on', $restStartDate, $restEndDate);
        $select->where($where);
        $select->group('email');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        $newCustomers = 0;
        $retuCustomers = 0;
        $customerType = [];
        if (!empty($data)) {
            foreach ($data as $value) {
                if ($value['total_reservations'] > 1) {
                    $retuCustomers ++;
                } else {
                    $newCustomers ++;
                }
            }
        } 
        $customerType['total_customers'] = $newCustomers + $retuCustomers;
        $customerType['new_customers'] = $newCustomers;
        $customerType['returning_customers'] = $retuCustomers;
        return $customerType;
    }
    public function getUserTotalReservations($userId,$restId,$restStartDate,$restEndDate) {
        $status = array('0', '4');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_reservations' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('user_id', $userId);
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', $status);
        $where->between('reserved_on', $restStartDate, $restEndDate);
        $select->where($where);
        $select->group('user_id');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (!empty($data)) {
            return $data[0]['total_reservations'];
        }else{
            return 0;
        } 
        
    }
    public function getRestaurantMembersReservations($restId,$restStartDate,$restEndDate) {
        $status = array('0', '2','3','4');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_reservations' => new Expression('COUNT( user_reservations.id)'),
        ));
        $select->join(array(
            'rs' => 'restaurant_servers'
                ), 'rs.user_id = user_reservations.user_id', array(
                ), $select::JOIN_INNER);
        $where = new Where ();
        $where->equalTo('user_reservations.restaurant_id', $restId);
        $where->in('user_reservations.status', $status);
        $where->between('user_reservations.reserved_on', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (empty($data)) {
            return 0;
        } else {
            return $data[0]['total_reservations'];
        }
    }
    public function getRestaurantNormalUserReservations($restId,$ids,$restStartDate,$restEndDate) {
        $status = array('0', '2','3','4');
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_reservations' => new Expression('COUNT(id)'),
        ));
        
        $where = new Where ();
        $where->NEST->in('status', $status)->AND->equalTo('restaurant_id', $restId)->UNNEST->AND->NEST->isNull('user_id')->OR->orPredicate(new \Zend\Db\Sql\Predicate\NotIn('user_id ', $ids))->UNNEST;
        $where->between('reserved_on', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (empty($data)) {
            return 0;
        } else {
            return $data[0]['total_reservations'];
        }
    }

}