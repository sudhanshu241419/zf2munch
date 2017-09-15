<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\PointSourceDetails;
use MCommons\StaticOptions;

class DashboardRestaurantDineinController extends AbstractRestfulController {

    public function getList() {
        $data = [];
        $reservationModel = new \Restaurantdinein\Model\Restaurantdinein();
        $dashboardFunctions = new DashboardFunctions();
        $restId = $dashboardFunctions->getRestaurantId();
        
        $currentDate = $dashboardFunctions->CityTimeZone();
        $page = $this->getQueryParams('page', 1);
        $limit = $this->getQueryParams('limit', SHOW_PER_PAGE);       
        
        # Archive Reservation
        
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
        $archiveCondition = array(
            'restaurantid' => $restId,
            'offset' => $offset,
            'limit' => $limit,
            'currentDate' => $currentDate,
            'archive' => 1,
            'orderBy' => 'created_at DESC'
        );
        $archiveReservationResponse = $reservationModel->dashboardRestaurantDineinList($archiveCondition);

        $upcomingCondition = array(
            'restaurantid' => $restId,
            'offset' => $offset,
            'limit' => $limit,
            'currentDate' => $currentDate,
            'archive' => 0,
            'orderBy' => 'created_at DESC'
        );
        $upcomingReservationResponse = $reservationModel->dashboardRestaurantDineinList($upcomingCondition);


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
        return $reservation;
   }
    
    public function get($reservation_id){
        
        if ($reservation_id == 0) {
            throw new \Exception('Reservation id is not valid', 404);
        }

        $dashboardFunctions = new DashboardFunctions();
        $reservationModel = new \Restaurantdinein\Model\Restaurantdinein();
        $dineinFunction = new \Restaurantdinein\RestaurantdineinFunctions();
        $restaurantid = $dashboardFunctions->getRestaurantId();
        $currentDate = $dashboardFunctions->CityTimeZone();
        
        
      
        $upcomingCondition = array(
            'reservationIds' => $reservation_id,
            'created_at' => $currentDate,
            'restaurantid' => $restaurantid,            
            'orderBy' => 'time_slot ASC'
        );
        $response = $reservationModel->getDashboardDineinDetails($upcomingCondition);
        if (!$response) {         
            throw new \Exception('Reservation not found');
        }
        
        $pastActivity = $dineinFunction->getUserPastActivities($response[0]['user_id'], $response[0]['restaurant_id'], $response[0]['email']);
        $response[0]['user_activity'] = $pastActivity;
        
        return $response['0'];
    }

    public function update($id,$data) {
        $dineinReservation = new \Restaurantdinein\Model\Restaurantdinein();
        $dashboardFunctions = new DashboardFunctions();        
        $restaurantid = $dashboardFunctions->getRestaurantId();
        $currentDate = $dashboardFunctions->CityTimeZone();        
        $dineinReservation->id = $id;         
        
       if($dineinReservation->id){
        $updateData = array(
          'restaurant_id' => $restaurantid,
          'status'=>isset($data['status'])?$data['status']:0,
          'archive'=>(isset($data['archive']) && $data['archive'] > 0)?$data['archive']:0,
          'restaurant_instruction'=>isset($data['restaurant_instruction'])?$data['restaurant_instruction']:'',
          'hold_time'=>isset($data['hold_time'])?$data['hold_time']:0,
          'restaurant_offer'=>isset($data['offer'])?$data['offer']:'',
          'is_modify'=>1,
          'created_at'=>$currentDate
        );
        $reservationDetails = $dineinReservation->reservationUpdate($updateData);      
       
        $this->userReservationNotification($reservationDetails);
        $this->snagAsportPoint($reservationDetails,$currentDate);
        if(isset($data['status']) && $data['status']==1){
            $this->addFeed($reservationDetails,$currentDate);
        }
        return $reservationDetails[0];   
       }else{
           return array();
       }
        
    }
    
    public function userReservationNotification($reservationDetail) {
        $responce = $this->userNotificationMsg($reservationDetail);
       
        $pubnubInfo = array("user_id" => $reservationDetail[0]['user_id'], "restaurant_id" => $reservationDetail[0]['restaurant_id'], "restaurant_name" => $reservationDetail[0]['restaurant_name'],"reservation_id"=>$reservationDetail[0]['reservation_id']);
        $currDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $reservationDetail[0]['restaurant_id']
        ));
        $userNotificationArray = array(
            "msg" => $responce['userMessage'],
            "channel" => "mymunchado_" . $reservationDetail[0]['user_id'],
            "userId" => $reservationDetail[0]['user_id'],
            "type" => 'snag-a-spot',
            "restaurantId" => $reservationDetail[0]['restaurant_id'],
            'restaurantName' => $reservationDetail[0]['restaurant_name'],
            'reservationStatus' => $reservationDetail[0]['status'],
            'firstName' => $reservationDetail[0]['first_name'],
            'isFriend' => 0,
            'curDate' => $currDateTime->format(StaticOptions::MYSQL_DATE_FORMAT),
            'reservation_id'=>$reservationDetail[0]['reservation_id']
        );
        if ($reservationDetail[0]['status'] != 3) {
            $userNotificationModel = new \Dashboard\Model\UserNotification();
            $userNotificationModel->createPubNubNotification($userNotificationArray, $pubnubInfo);
            \MCommons\StaticOptions::pubnubPushNotification($userNotificationArray);
        }
    }
    
     public function userNotificationMsg($reservationDetail) {
        $reservationModel = new \Dashboard\Model\DashboardReservation();
        $userMessage = '';
        $dashboardMessage = '';
        $dateTimeObject = new \DateTime($reservationDetail[0]['hold_table_time']);
        switch ($reservationDetail[0]['status']) {            
            case 1:
                $userMessage = "You've sucessfully snagged a spot at " . $reservationDetail[0]['restaurant_name'] . " at " .$dateTimeObject->format("h:i A"). " today!";
                $dashboardMessage = "You've confirmed reservation number: " . $reservationDetail[0]['booking_id'];
                $reservationModel->sendStagTableConfirmationMail($reservationDetail[0]);                
                break;
            case 2:
                $userMessage = "We're sorry, " . $reservationDetail[0]['restaurant_name'] . " could not accommodate your hold request. Don't worry, there are plenty of other places around town waiting for you to warm their seats.";
                $dashboardMessage = "You'successfully rejected reservation No. " . $reservationDetail[0]['booking_id'];
                $reservationModel->sendStagTableCancelMailToHost($reservationDetail[0], 0,$reservationDetail[0]['restaurant_instruction']);
                break;
            case 3:
                $userMessage = $reservationDetail[0]['restaurant_name'] . " give a alternate time of your reservation. Don't worry, there are plenty of other time around restaurant waiting for you to warm their seats.";
                $dashboardMessage = "You'successfully alternate reservation No. " . $reservationDetail[0]['booking_id'];
                $reservationModel->sendStagTableModificationMail($reservationDetail[0]);
                break;
        }
        return ['userMessage' => $userMessage, 'dashboardMessage' => $dashboardMessage];
    }
    
    public function snagAsportPoint($reservationDetail,$currentDate) {
        if ($reservationDetail[0]['status'] == 1 || $reservationDetail[0]['status'] == 7) {
            //You snagged a spot at [restaurant]! This calls for a celebration, here are [x] points!
            $pointSourceModel = new PointSourceDetails();
            $restServerModel = new \Dashboard\Model\RestaurantServer();
            $userPoint = new \Dashboard\Model\UserPoint();
            $user = new \Dashboard\Model\User();
            $insertData = [];
            $pointSource = $pointSourceModel->getPoint(array(PointSourceDetails::RESERVE_A_TABLE));
            $reservationPoints = $pointSource[0]['points'];
            $firstReservationPoint = 0;
            if ($reservationDetail[0]['user_id'] != null && $reservationDetail[0]['user_id'] != '') {
                $userWithDineMore = $restServerModel->userRegisterWithDineAndMore($reservationDetail[0]['restaurant_id'],$reservationDetail[0]['user_id']);
                if ($userWithDineMore > 0) {
                    //$firstReservation = $dineModel->totalSnagaSport($reservationDetail[0]['user_id']);
                   // if ($firstReservation == 1) {
                        $earlyBird = $restServerModel->earlyBirdSpecial($reservationDetail[0]['user_id'], $reservationDetail[0]['restaurant_id'], $reservationDetail[0]['reservation_date'], PointSourceDetails::EARLY_BIRD_SPECIAL_DAYS);
                        if ($earlyBird) {
                            $insertData['user_id'] = $reservationDetail[0]['user_id'];
                            $insertData['restaurant_id'] = $reservationDetail[0]['restaurant_id'];
                            $insertData['point_source'] = PointSourceDetails::RESERVE_A_TABLE;
                            $insertData['points'] = PointSourceDetails::DINE_MORE_EARLY_BIRD_POINT;
                            $insertData['created_at'] = $currentDate;
                            $insertData['status'] = 1;
                            $insertData['points_descriptions'] = "Bonus points for you at " . $reservationDetail[0]['restaurant_name'] . " for snagging spot during your first 30 days with Dine & More";
                            $insertData['ref_id'] = $reservationDetail[0]['reservation_id'];
                            $userPoint->save(0, $insertData);
                            $firstReservationPoint += PointSourceDetails::DINE_MORE_EARLY_BIRD_POINT;
                        }
                    //}
                    $insertData['user_id'] = $reservationDetail[0]['user_id'];
                    $insertData['restaurant_id'] = $reservationDetail[0]['restaurant_id'];
                    $insertData['point_source'] = PointSourceDetails::RESERVE_A_TABLE;
                    $insertData['points'] = PointSourceDetails::DINE_MORE_RESERVATION_POINT;
                    $insertData['created_at'] = date("Y-m-d H:i:s");
                    $insertData['status'] = 1;
                    $insertData['points_descriptions'] = "You snagged a spot at " . $reservationDetail[0]['restaurant_name'] . "! This calls for a celebration, here are " . PointSourceDetails::DINE_MORE_RESERVATION_POINT . " points!";
                    $insertData['ref_id'] = $reservationDetail[0]['reservation_id'];
                    $userPoint->save(0, $insertData);
                    $firstReservationPoint += PointSourceDetails::DINE_MORE_RESERVATION_POINT;
                } else {
                    $insertData['user_id'] = $reservationDetail[0]['user_id'];
                    $insertData['restaurant_id'] = $reservationDetail[0]['restaurant_id'];
                    $insertData['point_source'] = PointSourceDetails::RESERVE_A_TABLE;
                    $insertData['points'] = $reservationPoints;
                    $insertData['created_at'] = date("Y-m-d H:i:s");
                    $insertData['status'] = 1;
                    $insertData['points_descriptions'] = "You snagged a spot at " . $reservationDetail[0]['restaurant_name'] . "! This calls for a celebration, here are " . $reservationPoints . " points!";
                    $insertData['ref_id'] = $reservationDetail[0]['reservation_id'];
                    $userPoint->save(0, $insertData);
                    $firstReservationPoint += $reservationPoints;
                    //$userPoint->updatePointEntry($reservationDetail[0]['user_id'], $reservationDetail[0]['reservation_id'], $reservationPoints, $reservationDetail[0]['restaurant_name']);
                }
                $user->updateUserPoints($reservationDetail[0]['user_id'], $reservationDetail[0]['reservation_id'], $firstReservationPoint, $reservationPoints);
            }
            return $firstReservationPoint;
        }
    }
    
    public function addFeed($reservationDetail,$currentDate){
        try { 
            $commonFunctiion = new \MCommons\CommonFunctions();
            $replacementData = array('restaurant_name' => $reservationDetail[0]['restaurant_name']);
            $otherReplacementData = array('user_name'=>$reservationDetail[0]['first_name'],'restaurant_name'=>$reservationDetail[0]['restaurant_name']);
                       
            $feed = array(
                'restaurant_id' => $reservationDetail[0]['restaurant_id'],
                'restaurant_name' => $reservationDetail[0]['restaurant_name'],
                'user_name' => ucfirst($reservationDetail[0]['first_name']),
                'img' => array(),                
            );
            $activityFeedType = $commonFunctiion->getActivityFeedTypeByName("snag_a_spot_confirm");
            $commonFunctiion->addActivityFeed($feed, $activityFeedType[0]['id'], $replacementData, $otherReplacementData);
            return true;
        } catch (\Exception $ex) {
           return true;
        }
    }

}
