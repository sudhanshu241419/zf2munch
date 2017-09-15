<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserPoint;
use User\UserFunctions;
use User\Model\Promotions;
use MCommons\StaticOptions;
class WebRedemptionOpenNightController extends AbstractRestfulController {

    public function create($data) {
        $session = $this->getUserSession();
        $config = $this->getServiceLocator()->get('Config'); 
        $isLoggedIn = $session->isLoggedIn();
        $userModel = new \User\Model\User();
        $user = new \User\Model\User();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
            $uoptions = array('where'=>array('id'=>$userId));
            $userEmail = $user->getUserDetail($uoptions);
            $userDetail = $userModel->getUserEmailSubscriber($userEmail['email']);
        } else {
            throw new \Exception('User detail not found', 404);
        }
        if(!isset($data ['restaurant_id']) && !empty($data ['restaurant_id'])){
             throw new \Exception('Restaurant not found', 404);
        }
        
        $restaurant = new \Restaurant\Model\Restaurant();
        $restaurantOption = array('where'=>array('id'=>$data ['restaurant_id']));
        $restaurantData = $restaurant->findRestaurant($restaurantOption);
        if(!$restaurantData){
          throw new \Exception('Restaurant is not valid', 404);  
        }       
        
        $promotions = new Promotions();
        $userPoint = new UserPoint();
        $userFunctions = new UserFunctions();
        $userReservationModel = new \User\Model\UserReservation ();
        $reservationFunctions = new \Restaurant\ReservationFunctions ();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $type = isset($data['type'])?$data['type']:'opennight';
        $redemptionSpecial = $config['constants']['redemptionSpecial'];
        if($type == 'opennight'){
            $promotionId = $redemptionSpecial['opennight'];
        }elseif($type=='hispanic'){
            $promotionId = $redemptionSpecial['hispanicnight'];
        }else{
            throw new \Exception('Redemption special type is not define', 404);
        }
       
        $options = array('where' => array('promotionId' => $promotionId,'promotionStatus'=>'1'));
        $promotionsData = $promotions->getPromotions($options);
        if (!$promotionsData) {
            throw new \Exception('Redemption special detail is not valid', 404);
        }
                
        
        ############### Reservation Process ################        
        $cityId = isset ( $locationData ['city_id'] ) ? $locationData ['city_id'] : 18848;
        $userReservationModel->city_id = $cityId;
        $userReservationModel->order_id = isset($data['order_id'])?$data['order_id']:NULL;
        $userReservationModel->restaurant_id = $data ['restaurant_id'];
        $userReservationModel->time_slot = $data ['time_slot'];
        $userReservationModel->party_size = 2;
        $userReservationModel->reserved_seats = 2;
        $userReservationModel->user_instruction = isset($data ['user_instruction']) ? $data ['user_instruction'] : '';
        $userReservationModel->first_name = $userDetail['first_name'];
        $userReservationModel->last_name = isset($userDetail['last_name']) ? $userDetail['last_name'] : '';
        $userReservationModel->phone = $userDetail ['phone'];
        $userReservationModel->email = $userDetail ['email'];
        $userReservationModel->restaurant_name = $data ['restaurant_name'];
        $userReservationModel->receipt_no = $reservationFunctions->generateReservationReceipt();
        $userReservationModel->reserved_on = $currentDate;
        $userReservationModel->status = 4;
        $userReservationModel->user_id = $userId;
        $reservation = $userReservationModel->reserveTable();
        
        ####################################################        
        if($reservation){
            $totalPoints = $userPoint->countUserPoints($userId);
            $previousRedeemPoint = ($totalPoints[0]['redeemed_points'] > 0) ? intval($totalPoints[0]['redeemed_points']) : intval(0);
            $balancePoint = $totalPoints[0]['points'] - $previousRedeemPoint;
            $redeemPoint = $promotionsData[0]['promotionPoints'];
            $promotionId = $promotionsData[0]['promotionId'];
            if ($redeemPoint > $balancePoint) {
                throw new \Exception('You have not balanced to redeem point', 404);
            }

            $pointDescription = "Redemption using " . $promotionsData[0]['promotionName'];
            $insertData = array(
                'user_id' => $userId,
                'point_source' => '47',
                'points_descriptions' => $pointDescription,
                'redeemPoint' => $redeemPoint,
                'promotionId' => $promotionId,
                'created_at' => $currentDate,
                'status' => '1');
            if ($userPoint->createPointDetail($insertData)) {
                
                if($type == 'opennight'){
                    ############# Send Mail to user #################
                    $webUrl = PROTOCOL . $config ['constants'] ['web_url'];
                    $dateTime = explode(" ", date('Y-m-d H:i',strtotime($data ['time_slot'])));                   
                    $date = StaticOptions::getFormattedDateTime($dateTime [0], 'Y-m-d', 'D, M d, Y');
                    $time = StaticOptions::getFormattedDateTime($dateTime [1], 'H:i', 'h:i A');                    
                    
                    $template = 'redemption_opennight';
                    $layout = 'default_new';
                    $subject = 'Your Free Opening Night Reservation is Confirmed';
                    $variables = array(
                        'first_name' => ucfirst($userDetail['first_name']),
                        'restaurant_name'=>$userReservationModel->restaurant_name,
                        'date'=>$date,
                        'time'=>$time,
                        'restaurant_address'=>$restaurantData->address,
                        'web_url'=>$webUrl,
                    );
                    $mailData = array('recievers' => $userDetail['email'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
                    $userFunctions->emailSubscription($mailData);
                    ###################################################
                }
                
                return $returnInfo = array(
                    'userId' => $userId,
                    'currentRedeemPoint' => intval($redeemPoint),
                    'redeemptionName' => $promotionsData[0]['promotionName'],
                    'totalRedeemPoint' => $previousRedeemPoint + $redeemPoint,
                    'balancePoint' => $balancePoint - $redeemPoint,
                    'currentDate' => $currentDate,
                    'restaurantName'=>$userReservationModel->restaurant_name,
                    'recieptNo'=>$userReservationModel->receipt_no,
                );
        } else {
            throw new \Exception('We are unable to save Redemption detail', 404);
        }
    }else{
        throw new \Exception('Reservation is not done', 404);
    }
}
    
    public function getList(){
        $session = $this->getUserSession();
        $config = $this->getServiceLocator()->get('Config'); 
        $userFunctions = new UserFunctions();
        $promotions = new Promotions();
        $restaurant = new \Restaurant\Model\Restaurant();
        $type = $this->getQueryParams('type');
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        
        $redemptionSpecial = $config['constants']['redemptionSpecial'];
        if($type == 'opennight'){
            $promotionId = $redemptionSpecial['opennight'];
        }elseif($type=='hispanic'){
            $promotionId = $redemptionSpecial['hispanicnight'];
        }else{
            throw new \Exception('Redemption special type is not define', 404);
        }
        $options = array('where' => array('promotionId' =>$promotionId,'promotionStatus'=>'1'));
        $promotionsData = $promotions->getPromotions($options);
        if (!$promotionsData) {
            throw new \Exception('Redemption special detail is not valid', 404);
        }
        if($type == 'opennight'){
            $openNightRestaurant = $userFunctions->restaurantPromotionEvent($promotionId,$currentDate);
        }elseif($type=='hispanic'){
            $openNightRestaurant = $restaurant->getHispanicRestaurant($promotionId,$currentDate);
        }
        if($openNightRestaurant){
            $cuisines = array ();
            $cuisineModel = new \Restaurant\Model\Cuisine();
            $cuisineText = '';
            foreach($openNightRestaurant as $key => $val){
                //echo $openNightRestaurant[$key]['restaurant_image_name'];
                $openNightRestaurant[$key]['restaurant_image_name']=$config['constants']['protocol']."://".$config['constants']['imagehost'].'munch_images/'.strtolower($val['rest_code'])."/".$val['restaurant_image_name'];
                $openNightRestaurant[$key]['eventDate'] = date("D, M d \a\\t g:i a",strtotime($val['restaurantEventStartDate']));
                $openNightRestaurant[$key]['has_deal']= 0;
                
                // Cuisine data
		
                $cuisineData = $cuisineModel->getRestaurantCuisine ( array (
				'columns' => array (
						'restaurant_id' => $val['restaurant_id'] 
                        ) 
                ) )->toArray ();
		
		
                if (! empty ( $cuisineData )) {
                    foreach ( $cuisineData as $cuisine ) {
                        $cuisines [] = $cuisine ['cuisine'];
                    }
                    $cuisineText = implode ( ', ', $cuisines );
                }
		
                $openNightRestaurant[$key]['cuisine'] = $cuisineText;
            }
            return $openNightRestaurant;
        }else{
            throw new \Exception('There is no any Open Night restaurant available', 404);
        }
    }

}
