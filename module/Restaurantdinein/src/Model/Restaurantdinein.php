<?php

namespace Restaurantdinein\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class Restaurantdinein extends AbstractModel {

    public $id;
    public $booking_id;
    public $restaurant_id;
    public $restaurant_name;
    public $city_id;
    public $user_id;
    public $first_name;
    public $last_name;
    public $email;
    public $phone;
    public $reservation_date;
    public $hold_time = 0;
    public $first_hold_time = 0;
    public $seats;
    public $alternate_date;
    public $user_instruction;
    public $restaurant_offer;
    public $restaurant_instruction;
    public $host_name;
    public $user_ip;
    public $is_modify;
    public $created_at;
    public $status;
    public $archive = 0;
    protected $_db_table_name = 'Restaurantdinein\Model\DbTable\RestaurantdineinTable';
    protected $_primary_key = 'id';

    
    public function reserveTable() {
        $data = $this->toArray();
        $writeGateway = $this->getDbTable()->getWriteGateway();
       
        if (!$this->id) {
            $rowsAffected = $writeGateway->insert($data);
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
                    ));
        }
        // Get the last insert id and update the model accordingly
        $lastInsertId = $writeGateway->getAdapter()->getDriver()->getLastGeneratedValue();

        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this->toArray();
        }
        return false;
    }

    public function getRestaurantDineinArchice($options = array()) {
        $total_archive_record = 0;
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'reservation_id' => 'id',
            'booking_id',
            'restaurant_id',
            'restaurant_name',
            'seats',
            'reservation_date',
            'created_at',
            'first_name',
            'last_name',
            'phone',
            'email',
            'user_instruction',
            'status',
            'archive',
            'hold_time',
            'first_hold_time'
        ));
        $select->join(
                array('uri' => 'user_reservation_invitation'), 'uri.reservation_id=restaurant_dinein.id', array('id', 'to_id', 'friend_email', 'message', 'msg_status'), $select::JOIN_LEFT
        );

        $select->join(
                array('u' => 'users'), 'uri.to_id=u.id', array('invited_name' => 'first_name'), $select::JOIN_LEFT
        );

        $select->join(
                array('r' => 'restaurants'), 'r.id=restaurant_dinein.restaurant_id', array('rest_code', 'restaurant_image_name', 'address', 'zipcode', 'city_id', 'inactive', 'closed'), $select::JOIN_INNER
        );

        $select->join(
                array('c' => 'cities'), 'c.id=r.city_id', array('city_name', 'state_code'), $select::JOIN_INNER
        );

        $where = new Where ();
        if (isset($options ['reservationIds']) && !empty($options ['reservationIds'])) {
            $where->in('restaurant_dinein.id', $options ['reservationIds']);
            $invitationBy = 1;
        } else {
            $where->equalTo('restaurant_dinein.user_id', $options ['userId']);
            $invitationBy = 0;
        }
        $where->equalTo('restaurant_dinein.archive', $options ['archive']);
        $select->where($where);

        if (!empty($options ['orderBy'])) {
            $select->order($options ['orderBy']);
        }

        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservationData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        //pr($reservationData,true);
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

            $archiveStatus = "archive";
            $archieveReservation = $this->refineReservation($response, $reservationID, $archiveStatus, $invitationBy);
            $total_archive_record = count($archieveReservation);
            $archieveReservation = array_slice($archieveReservation, $options['offset'], $options['limit']);
            $user_function = new \User\UserFunctions();
            $response = $user_function->ReplaceNullInArray($archieveReservation);
        }
        $response['archive_count'] = $total_archive_record;
        return $response;
    }

    public function getReservationUpcommingDetails($options = array()) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'reservation_id' => 'id',
            'booking_id',
            'restaurant_id',
            'restaurant_name',
            'seats',
            'reservation_date',
            'created_at',
            'first_name',
            'last_name',
            'phone',
            'email',
            'user_instruction',
            'status',
            'archive',
            'hold_time',
            'first_hold_time',
        ));
        $select->join(
                array('uri' => 'user_reservation_invitation'), 'uri.reservation_id=restaurant_dinein.id', array('id', 'to_id', 'friend_email', 'message', 'msg_status'), $select::JOIN_LEFT
        );

        $select->join(
                array('u' => 'users'), 'uri.to_id=u.id', array('invited_name' => 'first_name'), $select::JOIN_LEFT
        );

        $select->join(
                array('r' => 'restaurants'), 'r.id=restaurant_dinein.restaurant_id', array('address', 'zipcode', 'city_id', 'inactive', 'closed'), $select::JOIN_LEFT
        );

        $select->join(
                array('c' => 'cities'), 'c.id=r.city_id', array('city_name', 'state_code'), $select::JOIN_LEFT
        );

        $where = new Where ();
        $varUserReservationId = 0;
        if (!empty($options ['reservationIds'])) {
            $where->in('restaurant_dinein.id', $options ['reservationIds']);
            $invitationBy = 1;
        } else {
            $varUserReservationId = $options ['userId'];
            $where->equalTo('restaurant_dinein.user_id', $options ['userId']);
            $invitationBy = 0;
        }
        $where->equalTo('restaurant_dinein.archive', $options ['archive']);
        $select->where($where);

        if (!empty($options ['orderBy'])) {
            $select->order($options ['orderBy']);
        }
        //pr($select->getSqlString($this->getPlatform('READ')),true);
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
            //print_r($response);
            $liveReservation = $this->refineReservation($response, $reservationID, $upcommingStatus, $invitationBy, '', $varUserReservationId);
            $user_function = new \User\UserFunctions();
            $response = $user_function->ReplaceNullInArray($liveReservation);
        }
        return $response;
    }

    private function refineReservation($userReservation, $reservationID, $status = false, $invitationBy, $currentTime = NULL, $varUserReservationId = 0) {
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $index = 0;
        $orderindex = 0;
        $i = 0;
        $invitationDetail = array();
        $userFunction = new \User\UserFunctions();
        $dineinFunctions = new \Restaurantdinein\RestaurantdineinFunctions();

        foreach ($userReservation as $key => $value) {

            $reservation[$value['reservation_id']]['reservation_id'] = $value['reservation_id'];

            $reservation[$value['reservation_id']]['booking_id'] = $value['booking_id'];
            $reservation[$value['reservation_id']]['restaurant_id'] = $value['restaurant_id'];
            $reservation[$value['reservation_id']]['restaurant_name'] = $value['restaurant_name'];
            $reservation[$value['reservation_id']]['is_restaurant_exist'] = ($value['inactive'] == 1 || $value['closed'] == 1) ? "No" : "Yes";
            $reservation[$value['reservation_id']]['seats'] = $value['seats'];
            $reservation[$value['reservation_id']]['reservation_date'] = $value['reservation_date'];
            $reservation[$value['reservation_id']]['hold_time'] = $value['hold_time'];
            $reservation[$value['reservation_id']]['hold_table_time'] = $dineinFunctions->holdTableDateTime($value['hold_time'], $value['reservation_date']);
            $reservation[$value['reservation_id']]['first_hold_time'] = $dineinFunctions->holdTableDateTime($value['first_hold_time'], $value['reservation_date']);
            $reservation[$value['reservation_id']]['created_at'] = $value['created_at'];
            $reservation[$value['reservation_id']]['first_name'] = $value['first_name'];
            $reservation[$value['reservation_id']]['last_name'] = $value['last_name'];
            $reservation[$value['reservation_id']]['email'] = $value['email'];
            $reservation[$value['reservation_id']]['phone'] = $value['phone'];
            $reservation[$value['reservation_id']]['status'] = (int) $value['status'];
            $reservation[$value['reservation_id']]['archive'] = (int)$value['archive'];

            $userInstruction = rtrim(str_replace("||", ', ', $value['user_instruction']), ', ');
            $reservation[$value['reservation_id']]['user_instruction'] = $userInstruction;
            $reservation[$value['reservation_id']]['restaurant_address'] = $value['address'] . ", " . $value['city_name'] . ", " . $value['state_code'] . " " . $value['zipcode'];
            if ($invitationBy == 1 && $value['msg_status'] == '0') {
                $reservation[$value['reservation_id']]['invitation_from_friend'] = (int) 1;
            } else {
                $reservation[$value['reservation_id']]['invitation_from_friend'] = (int) 0;
            }
            if (isset($value['friend_email']) && !empty($value['friend_email']) && $value['friend_email'] != NULL && $value['msg_status'] == '0') {
                $reservation[$value['reservation_id']]['reservation_is_invited'] = 1;
                if (empty($value['invited_name']) || $value['invited_name'] == null) {
                    $inviderDetail = explode('@', $value['friend_email']);
                } else {
                    $inviderDetail[0] = $value['invited_name'];
                }
                $varToDo = (int) $value['to_id'];
                if ($value['msg_status'] == '0' && isset($value['to_id']) && $varToDo > 0) {
                    $invitationDetail = array(
                        'id' => $value['id'],
                        'invited_id' => $value['to_id'],
                        'invited_email' => $value['friend_email'],
                        'invited_name' => $inviderDetail[0],
                        'user_message' => $value['message'],
                        'msg_status' => $value['msg_status']);
                    $reservation[$value['reservation_id']]['invitation'][] = $invitationDetail;
                } else {
                    $reservation[$value['reservation_id']]['invitation'] = array();
                }
            } else {
                $reservation[$value['reservation_id']]['reservation_is_invited'] = (int) 0;
//						$invitationDetail = array('inviter_id'=>"",'inviter_email'=>"",'inviter_name'=>"",'invitation_description'=>"",'message'=>"",'msg_status'=>"");
                $reservation[$value['reservation_id']]['invitation'] = array();
            }

            if ($invitationBy == 1 && $value['msg_status'] == '0') {
                $typeOfMeal = $userFunction->getMealSlot(StaticOptions::getFormattedDateTime($value['reservation_date'], 'Y-m-d H:i:s', 'H:i:s'));
                $reservation[$value['reservation_id']]['invitation_description'] = $typeOfMeal . " invitation from " . $value['first_name'];
            } else {
                $reservation[$value['reservation_id']]['invitation_description'] = "";
            }
        }


        $key_index = 0;
        foreach ($reservation as $key => $reservationD) {
            $reservationResponse[$key_index] = $reservationD;
            $key_index ++;
        }

        return $reservationResponse;
    }

    public function getRestaurantDineinDetails($options) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'reservation_id' => 'id',
            'booking_id',
            'restaurant_id',
            'restaurant_name',
            'seats',
            'reservation_date',
            'created_at',
            'first_name',
            'last_name',
            'phone',
            'email',
            'user_instruction',
            'status',
            'archive',
            'user_id',
            'hold_time',
            'first_hold_time',
            'alternate_date',
            'restaurant_instruction',
            'restaurant_offer',
            ));

        $select->join(
                array('r' => 'restaurants'), 'r.id=restaurant_dinein.restaurant_id', array('rest_code', 'restaurant_image_name', 'address', 'zipcode', 'city_id', 'inactive', 'closed','latitude','longitude','rest_phone'=>'phone_no'), $select::JOIN_LEFT
        );

        $select->join(
                array('c' => 'cities'), 'c.id=r.city_id', array('city_name', 'state_code'), $select::JOIN_LEFT
        );

        $where = new Where ();
        if (!empty($options ['reservationid'])) {
            $where->equalTo('restaurant_dinein.id', $options ['reservationid']);
            $invitationBy = "1";
        } else {
            $where->equalTo('restaurant_dinein.user_id', $options ['userId']);
            $invitationBy = "0";
        }
        //$where->in('restaurant_dinein.status', $options ['status']);
        //$where->greaterThanOrEqualTo( 'user_reservations.time_slot', $options ['currentDate'] );
        $select->where($where);

        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservationData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();

        $response = array();
        $dineinFunctions = new \Restaurantdinein\RestaurantdineinFunctions();
        if ($reservationData) {
            $reservationID = array_unique(array_map(function ($i) {
                        return $i['reservation_id'];
                    }, $reservationData));

            $i = 0;
            $response = array();
            foreach ($reservationData as $key => $value) {
                $value['hold_table_time'] = $dineinFunctions->holdTableDateTime($value['hold_time'], $value['reservation_date']);
                $value['first_hold_time'] = $dineinFunctions->holdTableDateTime($value['first_hold_time'], $value['reservation_date']);
                $value['status']=(int)$value['status'];
                $value['archive'] = (int)$value['archive'];
                $value['wating_time']= WATING_TIME;
                $response[] = $value;
            }
        }
        return $response;
    }
    
    public function dashboardRestaurantDineinList($options){
        $total_archive_record = 0;
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'reservation_id' => 'id',
            'booking_id',
            'restaurant_id',
            'restaurant_name',
            'seats',
            'reservation_date',
            'created_at',
            'first_name',
            'last_name',
            'phone',
            'email',
            'user_instruction',
            'status',
            'archive',
            'hold_time'
        ));
        $where = new Where ();
        if (!empty($options ['restaurantid'])) {
            $where->equalTo('restaurant_id', $options ['restaurantid']);
        }
        if (!empty($options ['start_date'])) {
            $where->between('reservation_date', $options ['start_date'],$options ['end_date']);
        }
        $where->equalTo('restaurant_dinein.archive', $options ['archive']);
        $select->where($where);

        if (!empty($options ['orderBy'])) {
            $select->order($options ['orderBy']);
        }

        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservationData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $reservationData;
    }
    
     public function getDashboardDineinDetails($options) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'reservation_id' => 'id',
            'booking_id',
            'restaurant_id',
            'restaurant_name',
            'seats',
            'reservation_date',
            'created_at',
            'first_name',
            'last_name',
            'phone',
            'email',
            'user_instruction',
            'status',
            'archive',
            'user_id',
            'hold_time',
            'first_hold_time',
            'restaurant_instruction',
            'restaurant_offer',
            'is_modify'
        ));

        $select->join(
            array('r' => 'restaurants'), 'r.id=restaurant_dinein.restaurant_id',array(
                'rest_code', 
                'restaurant_image_name', 
                'address', 
                'zipcode', 
                'city_id', 
                'inactive', 
                'closed',
                'latitude',
                'longitude',
                'rest_phone'=>'phone_no'
                ), 
            $select::JOIN_LEFT
        );

        $select->join(
                array('c' => 'cities'), 'c.id=r.city_id', array('city_name', 'state_code'), $select::JOIN_LEFT
        );
        
        $select->join(
                array('u' => 'users'), 'u.id=restaurant_dinein.user_id', array('user_pic'=>'display_pic_url_large'), $select::JOIN_LEFT
        );
        $where = new Where ();
        
        //$where->in('restaurant_dinein.status', $options ['status']);
        $where->equalTo('restaurant_dinein.id', $options['reservationIds']);
        $where->equalTo('restaurant_dinein.restaurant_id', $options['restaurantid']);
        $select->where($where);
        $resCalendar = new \Restaurant\Model\Calendar();
        $openCloseTime  = $resCalendar->restaurantOpenCloseTime($options['restaurantid']);
        
        //pr($openCloseTime[0],1);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservationData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        $userFunctions = new \User\UserFunctions();
        //pr($reservationData,1);
        $userPic = "";
        if(isset($reservationData[0]['user_id'])){
            $userPic = $userFunctions->findImageUrlNormal($reservationData[0]['user_pic'],$reservationData[0]['user_id']);
        }
        $dineinFunctions = new \Restaurantdinein\RestaurantdineinFunctions();
        $reservationData[0]['hold_table_time'] = $dineinFunctions->holdTableDateTime($reservationData[0]['hold_time'], $reservationData[0]['reservation_date']);
        $reservationData[0]['first_hold_time'] = $dineinFunctions->holdTableDateTime($reservationData[0]['first_hold_time'], $reservationData[0]['reservation_date']);
        $reservationData[0]['status']=(int)$reservationData[0]['status'];
        $reservationData[0]['archive']=(int)$reservationData[0]['archive'];
        $reservationData[0]['open_time']=isset($openCloseTime[0]['open_time'])?$openCloseTime[0]['open_time']:"";
        $reservationData[0]['close_time']=isset($openCloseTime[0]['close_time'])?$openCloseTime[0]['close_time']:"";
        $reservationData[0]['user_pic'] = $userPic;
        $reservationData[0]['wating_time']=WATING_TIME;
        
        return $reservationData;
    }
    
    public function reservationUpdate($data){
        
        $writeGateway = $this->getDbTable()->getWriteGateway();
       
        if ($this->id) {           
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
                    ));
        }
       
        $options = array('reservationIds'=>$this->id,'restaurantid'=>$data['restaurant_id'],'status'=>array($data['status']),'current_date'=>$data['created_at']);
        return $this->getDashboardDineinDetails($options);
        
    }
    
    public function getTotalUserReservations($restId, $userId, $email) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_reservations' => new \Zend\Db\Sql\Predicate\Expression('COUNT(id)'),
        ));
        $select->where->nest->equalTo('user_id', $userId)->or->equalTo('email', $email)->unnest->and->equalTo('restaurant_id', $restId);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        if (!empty($data)) {
            return $data['total_reservations'];
        } else {
            return 0;
        }
    }
    public function getReservationStatus($reservationid){
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'status' 
        ));
        $select->where->equalTo('id', $reservationid);
        $select->where->equalTo("status",1);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if (!empty($data)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function totalSnagaSport($userId) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_reservations' => new \Zend\Db\Sql\Predicate\Expression('COUNT(id)'),
        ));
        $select->where->equalTo('user_id', $userId);
        //$select->where->nest->equalTo('user_id', $userId)->or->equalTo('email', $email)->unnest->and->equalTo('restaurant_id', $restId);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        if (!empty($data)) {
            return $data['total_reservations'];
        } else {
            return 0;
        }
    }
    public function update($id, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if ($id) {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $id
            ));
        }
        if ($rowsAffected) {
            return true;
        }
        return false;
    }
    
    public function latestThreeSnagSpotTable($options){
        
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'reservation_id' => 'id',
            'booking_id',
            'restaurant_id',
            'restaurant_name',
            'seats',
            'reservation_date',
            'created_at',
            'first_name',
            'last_name',
            'phone',
            'email',
            'user_instruction',
            'status',
            'archive',
            'hold_time'
        ));
        $where = new Where ();
        if (!empty($options ['restaurantid'])) {
            $where->equalTo('restaurant_id', $options ['restaurantid']);
        }
        if (!empty($options ['start_date'])) {
            $where->between('reservation_date', $options ['start_date'],$options ['end_date']);
        }
        $where->equalTo('restaurant_dinein.archive', $options ['archive']);
        $select->where($where);

        if (!empty($options ['orderBy'])) {
            $select->order($options ['orderBy']);
        }
        $select->limit(3);

        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservationData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $reservationData;
    }
    
    public function countSnagSpotTable($restaurantId){
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_snagspot' => new Expression('COUNT(id)'),
        ));
        
        $where = new Where ();
        $where->equalTo('restaurant_id', $restaurantId);
        $where->in("status", array(0,3,6));        
        $where->equalTo("archive", 0);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (empty($data)) {
            return $data[0]['total_snagspot'] = 0;
        } else {
            return $data[0]['total_snagspot'];
        }
    }

}
