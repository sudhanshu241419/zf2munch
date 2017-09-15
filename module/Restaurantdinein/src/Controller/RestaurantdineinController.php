<?php

namespace Restaurantdinein\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReservation;
use Restaurant\ReservationFunctions;
use User\Model\UserInvitation;
use User\UserFunctions;
use MCommons\StaticOptions;
use User\Model\User;
use User\Model\PointSourceDetails;


class RestaurantdineinController extends AbstractRestfulController {

    public function get($reservation_id = 0) {
        if ($reservation_id == 0) {
            throw new \Exception('Reservation id is not valid', 404);
        }
        
        $user_function = new UserFunctions ();
        $reservationModel = new \Restaurantdinein\Model\Restaurantdinein();        
        $session = $this->getUserSession();        
        $userId = $session->getUserId();        
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        
        $upcomingCondition = array(
            'reservationid' => $reservation_id,
            'currentDate' => $currentDate,
            'userId' => $userId,
            'orderBy' => 'time_slot ASC'
        );
        $response = $reservationModel->getRestaurantDineinDetails($upcomingCondition);
        if (!$response) {         
            throw new \Exception('Reservation not found');
        }
        return $response[0];
    }

    public function getInvitationDetail($reservationId, $reservationDetail, $userId = false) {
        $reservationInvitationModel = new UserInvitation();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'u' => 'users'
            ),
            'on' => 'u.id = user_reservation_invitation.to_id',
            'columns' => array(
                'invited_name' => 'first_name',
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'ur' => 'user_reservations'
            ),
            'on' => 'ur.id = user_reservation_invitation.reservation_id',
            'columns' => array(
                'invitation_by_phone' => 'phone',
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'id',
                'invited_id' => 'to_id',
                'invitation_by' => 'user_id',
                'invited_email' => 'friend_email',
                'user_message' => 'message',
                'msg_status'
            ),
            'where' => array(
                'user_reservation_invitation.reservation_id' => $reservationId,
                'user_reservation_invitation.to_id' => $userId,
                'user_reservation_invitation.msg_status' => '0'
            ),
            'joins' => $joins
        );

        $reservationInvitationModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        if ($reservationInvitationModel->find($options)->toArray()) {
            $invitationResponse = $reservationInvitationModel->find($options)->toArray();
            $refineInvitationList = $this->refineInvitationResponse($invitationResponse, $reservationDetail);
            $reservationDetail['reservation_is_invited'] = 1;
            $reservationDetail['invitation_from_friend'] = $refineInvitationList['invitation_from_friend'];
            $reservationDetail['invitation_description'] = $refineInvitationList['invitation_description'];
            $reservationDetail['invitation'] = $invitationResponse;
        } else {
            $reservationDetail['reservation_is_invited'] = 0;
            $reservationDetail['invitation'] = array();
            $reservationDetail['invitation_from_friend'] = 0;
            $reservationDetail['invitation_description'] = "";
        }

        return $reservationDetail;
    }

    public function refineInvitationResponse($data, $reservationDetail) {

        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $userId = $session->getUserId();
        $userFunction = new UserFunctions();
        $user = new User();
        $invitation_description = "";
        foreach ($data as $key => $val) {

            if ($val['invitation_by'] == $userId) {
                $invitation_from_friend = 0;
            } else {
                $invitation_from_friend = 1;
            }

            if ($invitation_from_friend == 1) {
                $typeOfMeal = $userFunction->getMealSlot(StaticOptions::getFormattedDateTime($reservationDetail['reservation_date'], 'Y-m-d H:i:s', 'H:i:s'));
                $invitation_description = $typeOfMeal . " invitation from " . $reservationDetail['first_name'];
            }
        }
        $data['invitation_from_friend'] = $invitation_from_friend;
        $data['invitation_description'] = $invitation_description;
        return $data;
    }

    public function update($id, $data) {        
        $userReservatioModel = new \Restaurantdinein\Model\Restaurantdinein();
        
        if (!isset($data ['status'])) {
            throw new \Exception("Status is required", 405);
        }      

        if(!isset($data['restaurant_id'])){
            throw new \Exception("Restaurant is required", 405);
        }
         $currentDateTime = StaticOptions::getRelativeCityDateTime(array(
                'restaurant_id' => $data ['restaurant_id']
            ))->format(StaticOptions::MYSQL_DATE_FORMAT);
        $userReservatioModel->id = $id;
        $updateData = array('status'=>$data ['status'],'restaurant_id'=>$data['restaurant_id'],'created_at'=>$currentDateTime);  

        if ($userReservatioModel->id) {
           return $userReservatioModel->reservationUpdate($updateData)[0];
        }
        
        return array();       
    }

    public function create($data) {
        $userPointTable = 0;

        $userId = $this->getUserSession()->getUserId();
        $this->validate($data, $userId);

        $currentDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $data ['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT);

        $holdTime = ($data['hold_time']) ? $data['hold_time'] : 0;

        $ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        $selectedLocation = $this->getUserSession()->getUserDetail('selected_location', array());
        $cityId = isset($selectedLocation ['city_id']) ? $selectedLocation ['city_id'] : 18848;

        $userModel = new User ();
        $restaurantDinein = new \Restaurantdinein\Model\Restaurantdinein ();        
        $reservationFunctions = new ReservationFunctions();
        $dineinFunctions = new \Restaurantdinein\RestaurantdineinFunctions();
        $pointSourceModel = new PointSourceDetails ();
               

        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $pointID = isset($config ['constants'] ['point_source_detail']) ? $config ['constants'] ['point_source_detail'] : array();

        $restaurantDinein->city_id = $cityId;
        $restaurantDinein->restaurant_id = $data ['restaurant_id'];
        $restaurantDinein->reservation_date = $currentDateTime;
        $restaurantDinein->seats = $data ['seats'];
        $restaurantDinein->user_instruction = isset($data ['user_instruction']) ? $data ['user_instruction'] : '';
        $restaurantDinein->first_name = $data ['first_name'];
        $restaurantDinein->last_name = isset($data ['last_name']) ? $data ['last_name'] : '';
        $restaurantDinein->phone = $data ['phone'];
        $restaurantDinein->email = $data ['email'];
        $restaurantDinein->restaurant_name = $data ['restaurant_name'];
        $restaurantDinein->host_name = (StaticOptions::$_userAgent === "iOS") ? "iphone" : "android";
        $restaurantDinein->booking_id = $reservationFunctions->generateReservationReceipt();
        $restaurantDinein->created_at = $currentDateTime;
        $restaurantDinein->user_ip = $ipAddress;
        $restaurantDinein->hold_time = $holdTime;
        $restaurantDinein->first_hold_time = $holdTime;
        $restaurantDinein->status = 0;
        $restaurantDinein->archive= 0;
        $restaurantDinein->is_modify = 0;

        if ($userId) {
            $restaurantDinein->user_id = $userId;
        }

        $reservation = $restaurantDinein->reserveTable();
        
        $dineinFunctions->dashboardCmsNotification($userId, $data, $restaurantDinein->id, $currentDateTime);
                
        $userFunctions = new UserFunctions ();
        $userPoints = '';
        $awardPoint = 0;
        $confirm = 0;
        if ($userId) {           
            $userModel = new User ();
            $userData = $userModel->getUserDetail(array(
                'column' => array(
                    'points',
                    'phone'
                ),
                'where' => array(
                    'id' => $userId
                )
            ));
            if ($userData ['phone'] == null) {
                $userModel->id = $userId;
                $userModel->update(array(
                    'phone' => $data ['phone']
                ));
            }
            $userPointsModel = new \User\Model\UserPoint();
            $totalPoints = $userPointsModel->countUserPoints($userId);
            $redeem_points = $totalPoints[0]['redeemed_points'];
            $userPoints = strval($totalPoints[0]['points'] - $redeem_points);
            $pointId = $pointID['reserveATable'];
            $reservationPoints = $pointSourceModel->getPointSourceDetail(array(
                'column' => array(
                    'points',
                    'id'
                )
                ,
                'where' => array(
                    'id' => $pointId
                )
            ));
            ########## Dine and more awards point calculation ###############
            $userPoints = $userPoints + $reservationPoints['points'];
            $userPointTable = $reservationPoints['points'];
            $userFunctions->userId = $userId;
            $userFunctions->restaurantId = $restaurantDinein->restaurant_id;
            $userFunctions->activityDate = $currentDateTime;
            $userFunctions->restaurant_name = $restaurantDinein->restaurant_name;
            $userFunctions->typeValue = $reservation['id'];
            $userFunctions->typeKey = 'reservation_id';
            $awardPoint_reservation = $userFunctions->dineAndMoreAwards("awardsreservation");
            $awardPoint = (isset($awardPoint_reservation['points'])) ? $awardPoint_reservation['points'] : 0;

            if (isset($awardPoint_reservation['points'])) {
                $userPoints = ($userPoints + $awardPoint_reservation['points']) - $reservationPoints['points'];
                $userPointTable = $awardPoint_reservation['points'];
            } 
        }
       
        if ($data ['first_name']) {
            $userName = $data ['first_name'];
        } else {
            $emailArr = explode("@", $data ['email']);
            $userName = $emailArr [0];
        }
        $instruction = "";
        if (!empty($data ['user_instruction'])) {
            $instruction = str_replace('||', '<br>', $data ['user_instruction']);
        }       

        $webUrl = PROTOCOL . $config ['constants'] ['web_url'];

        $sendMail = $userModel->checkUserForMail($userId, 'reservation');
        $specChar = $config ['constants']['special_character'];
//        if ($sendMail) {
//            $emailData = array(
//                'receiver' => array(
//                    $data ['email']
//                ),
//                'variables' => array(
//                    'username' => $userName,
//                    'restaurantName' => $data ['restaurant_name'],
//                    'reservationDate' => StaticOptions::getFormattedDateTime($currentDateTime, 'Y-m-d H:i:s', 'D, M d, Y'),
//                    'reservationtime' => StaticOptions::getFormattedDateTime($currentDateTime, 'Y-m-d H:i:s', 'h:i A'),
//                    'seats' => $data ['seats'],
//                    'url' => $webUrl
//                ),
//                'subject' => 'New Table Request from a Munch Ado Customer!',
//                'template' => 'user-snag-a-spot-placed',
//                'layout' => 'email-layout/default_new'
//            );
//            //StaticOptions::resquePush ( $emailData, "SendEmail" );
//            $userFunctions->sendMails($emailData);
//        }

//        $friendList = array();
//        
//        if ($userId) {
//           $dineinFunctions->bookMarkRestaurant($userId,$data,$currentDateTime);
//           $friendList = $dineinFunctions->getFriendList($userId);
//        }
        if($reservation){
            $restDetails = $userFunctions->getRestOrderFeatures($restaurantDinein->restaurant_id);
            $clevertapData = array(
                "user_id"=>$userId,
                "name"=>$restaurantDinein->first_name." ".$restaurantDinein->last_name,
                "email"=>$restaurantDinein->email,
                "identity"=>$restaurantDinein->email,
                "reservation_date"=>StaticOptions::getFormattedDateTime($currentDateTime, 'Y-m-d H:i:s', 'Y-m-d'),
                "reservation_time"=> StaticOptions::getFormattedDateTime($currentDateTime, 'Y-m-d H:i:s', 'h:i A'),
                "restaurant_name"=>$restaurantDinein->restaurant_name,
                "restaurant_id"=>$restaurantDinein->restaurant_id,
                "seats"=>$restaurantDinein->seats,
                "earned_points"=>$userPointTable,                
                "eventname"=>"snag_a_spot",
                "event"=>1,
                "reservation_id"=>$reservation ['id'],
                "delivery_enabled" => $restDetails['delivery'],
                "takeout_enabled" => $restDetails['takeout'],
                "is_register"=>($userId)?"yes":"no"
                
            );
            
            $userFunctions->createQueue($clevertapData, 'clevertap');
            
            $options = array('reservationIds'=>$reservation ['id'],'restaurantid'=>$data ['restaurant_id'],'status'=>array(0),'current_date'=>$currentDateTime);
            $response = $restaurantDinein->getDashboardDineinDetails($options)[0];
            $response['wating_time'] = WATING_TIME;
            return $response;
        }
        
        return array();       
        
    }
    
   
    public function getList() {
        // Get reservation data
        $reservationModel = new \Restaurantdinein\Model\Restaurantdinein();
        $user_function = new UserFunctions ();
        $user_invitation = new UserInvitation ();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $friendId = $this->getQueryParams('friendid', false);
        if ($isLoggedIn) {
            if ($friendId) {
                $userId = $friendId;
            } else {
                $userId = $session->getUserId();
            }
        } else {
            throw new \Exception('User detail not found', 404);
        }

        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $reservationStatus = isset($config ['constants'] ['reservation_status']) ? $config ['constants'] ['reservation_status'] : array();
        $page = $this->getQueryParams('page', 1);
        $limit = $this->getQueryParams('limit', SHOW_PER_PAGE);
        $orderReservation = $this->getQueryParams('order', 'date');
        $orderBy = 'restaurant_dinein.hold_time DESC';
        if ($orderReservation == 'date') {
            $orderBy = 'restaurant_dinein.hold_time DESC';
        } elseif ($orderReservation == 'restaurant') {
            $orderBy = 'restaurant_dinein.restaurant_name ASC';
        }
        /*
         * Archive Reservation
         */
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
        $archiveCondition = array(
            'userId' => $userId,
            'offset' => $offset,
            'limit' => $limit,
            'currentDate' => $currentDate,
            'archive' => 1,
            'orderBy' => $orderBy
        );
        $archiveReservationResponse = $reservationModel->getRestaurantDineinArchice($archiveCondition);

        $totalArchiveRecords = $archiveReservationResponse['archive_count'];
        unset($archiveReservationResponse['archive_count']);
        /*
         * Upcomming Reservation & Friend invitation
         */
        $reservation_id = $user_invitation->getAllUserInvitation(array(
            'columns' => array('reservation_id'),
            'where' => array('to_id' => $userId, 'msg_status' => array('0', '1')),
            'order' => array('created_on DESC')
                )
        );
        if ($reservation_id) {
            foreach ($reservation_id as $value) {
                $reservationIds [] = $value ['reservation_id'];
            }

            $upcomingCondition = array(
                'reservationIds' => $reservationIds,
                'offset' => $offset,
                'limit' => SHOW_PER_PAGE,
                'currentDate' => $currentDate,
                'archive' => 0,
                'orderBy' => 'hold_time ASC'
            );
            $upcomingInvitationReservationResponse = $reservationModel->getReservationUpcommingDetails($upcomingCondition);
        }
        // pr($upcomingInvitationReservationResponse,1);
        $upcomingCondition = array(
            'userId' => $userId,
            'offset' => $offset,
            'limit' => $limit,
            'currentDate' => $currentDate,
            'archive' => 0,
            'orderBy' => 'hold_time ASC'
        );
        $upcomingReservationResponse = $reservationModel->getReservationUpcommingDetails($upcomingCondition);

        if (isset($upcomingInvitationReservationResponse)) {
            $upcomingReservationResponse = array_merge($upcomingInvitationReservationResponse, $upcomingReservationResponse);
        }

        $reservation = array();
        if ($archiveReservationResponse) {
            $reservation['archive_reservation'] = $archiveReservationResponse;
        } else {
            $reservation['archive_reservation'] = array();
        }

        if ($upcomingReservationResponse) {
            $reservation['upcomming_reservation'] = $upcomingReservationResponse;
        } else {
            $reservation['upcomming_reservation'] = array();
        }

        foreach ($reservation as $key => $reservationDetail) {
            foreach ($reservationDetail as $k => $v) {

                if ($v['is_restaurant_exist'] == 'Yes') {
                    $reservation[$key][$k]['is_restaurant_exist'] = true;
                } else {
                    $reservation[$key][$k]['is_restaurant_exist'] = false;
                }
                if (!$v['invitation_from_friend'])
                    $reservation[$key][$k]['invitation_from_friend'] = 0;
                if (!$v['reservation_is_invited'])
                    $reservation[$key][$k]['reservation_is_invited'] = 0;

                if ($v['invitation'] == "") {
                    $reservation[$key][$k]['invitation'] = array();
                }
            }
        }
        $reservation['total_archive_records'] = $totalArchiveRecords;

        return $reservation;
    }

    public function reservationAgain($data) {
        $userReservatioModel = new UserReservation ();
        $reservationId = $data['reservation_id'];
        $reservation = $userReservatioModel->getUserReservation(array(
            'columns' => array(
                'id',
                'receipt_no',
                'restaurant_id',
                'user_id',
                'time_slot',
                'reserved_seats',
                'party_size',
                'reserved_on',
                'user_instruction',
                'restaurant_name',
                'status',
                'first_name',
                'last_name',
                'phone',
                'email',
                'order_id'
            ),
            'where' => array(
                'id' => $reservationId
            )
        ));

        if (!$reservation) {
            throw new \Exception('Reservation details not found', 404);
        }
        $previousReservationDetail = $reservation[0];
        return $previousReservationDetail;
    }

    public function reservation_invited_by_friend($reservationId, $reservationDetail, $userId = false) {
        $reservationInvitationModel = new UserInvitation();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'u' => 'users'
            ),
            'on' => 'u.id = user_reservation_invitation.to_id',
            'columns' => array(
                'invited_name' => 'first_name',
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'ur' => 'user_reservations'
            ),
            'on' => 'ur.id = user_reservation_invitation.reservation_id',
            'columns' => array(
                'invitation_by_phone' => 'phone',
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'id',
                'invited_id' => 'to_id',
                'invitation_by' => 'user_id',
                'invited_email' => 'friend_email',
                'user_message' => 'message',
                'msg_status'
            ),
            'where' => array(
                'user_reservation_invitation.reservation_id' => $reservationId,
                'user_reservation_invitation.to_id' => $userId,
            ),
            'joins' => $joins
        );

        $reservationInvitationModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        if ($reservationInvitationModel->find($options)->toArray()) {
            $reservation_invited_by_friend = 1;
        } else {
            $reservation_invited_by_friend = 0;
        }

        return $reservation_invited_by_friend;
    }

    private function validate($data, $userId) {

        if (!isset($data ['restaurant_id']) || empty($data ['restaurant_id'])) {
            throw new \Exception('Please provide restaurant id', 400);
        }

        if (!isset($data ['seats']) || empty($data ['seats'])) {
            throw new \Exception('Please seats no.', 400);
        }

        if (!isset($data ['first_name']) || empty($data ['first_name'])) {
            throw new \Exception('First name can not be empty', 400);
        }
        if (!isset($data ['email']) || empty($data ['email'])) {
            throw new \Exception('Email can not be empty', 400);
        }
        if (!isset($data ['phone']) || empty($data ['phone'])) {
            throw new \Exception('Phone can not be empty', 400);
        }
        if (!isset($data ['restaurant_name']) || empty($data ['restaurant_name'])) {
            throw new \Exception('Restaurant name can not be empty', 400);
        }
    }

}
