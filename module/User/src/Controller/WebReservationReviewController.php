<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReview;
use User\Model\UserMenuReview;
use User\Model\UserReviewImage;
use User\Model\DbTable\UserReviewTable;
use MCommons\StaticOptions;
use User\Model\UserReservation;
use Restaurant\Model\Restaurant;
use User\Model\UserNotification;

class WebReservationReviewController extends AbstractRestfulController {

    public function create($data) {
        if (!isset($data ['rating']) || $data ['rating'] == null) {
            throw new \Exception('Select Rating');
        }
        if (!isset($data ['restaurant_id']) || $data ['restaurant_id'] == null) {
            throw new \Exception('Please send Restaurant Id');
        }
        $userId = $this->getUserSession()->getUserId();
        if (!$userId) {
            throw new \Exception('User Not Logged in');
        }
        try {
            $dbtable = new UserReviewTable ();
            $restaurantModel = new Restaurant();
            $userNotificationModel = new UserNotification();
            $userrestaurantimagemodel = new \User\Model\UserRestaurantimage();
            $selectedLocation = $this->getUserSession()->getUserDetail ( 'selected_location', array () );   
            $cityModel = new \Home\Model\City();//18848
            $cityId = isset($selectedLocation ['city_id'])?$selectedLocation ['city_id']:18848;
            $cityDetails = $cityModel->cityDetails($cityId);
            $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
                        'state_code' => $cityDetails [0] ['state_code']
                    ));
       
             $currentDateTime = $cityDateTime->format('Y-m-d H:i:s');
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->beginTransaction();
            $userReviewModel = new UserReview ();
            $operation_type = 'insert';
            $review_id = isset($data ['review_id']) ? $data ['review_id'] : 0;
            $value ['noise_level'] = isset($data ['noice_level']) ? $data ['noice_level'] : 1;
            $value ['services'] = isset($data ['service']) ? $data ['service'] : 1;
            $value ['taste_test'] = isset($data ['taste_test']) ? $data ['taste_test'] : 1;
            $value ['come_back'] = isset($data ['come_back']) ? $data ['come_back'] : 0;
            $value ['rating'] = $data ['rating'];
            $value['sentiment'] = ($data['rating'] > 2) ? 1 : 0;
            $value ['review_desc'] = isset($data ['review_desc']) ? $data ['review_desc'] : '';
            $value ['review_for'] = 3;
            $value ['restaurant_id'] = $data ['restaurant_id'];
            $value ['user_id'] = $userId;
            $value ['status'] = 0;
            $value ['order_id'] = isset($data ['reservation_id']) ? $data ['reservation_id'] : 0;
            $value ['created_on'] = $currentDateTime;
            $restaurantDetailOption = array('columns'=>array('restaurant_name','rest_code'),'where'=>array('id'=>$data ['restaurant_id']));
            $restCode = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray();
            $userModel = new \User\Model\User();
            $userDetailOption = array('columns' => array('first_name','last_name','email'), 'where' => array('id' => $userId));
            $userDetail = $userModel->getUser($userDetailOption);
            if ($review_id != 0) {
                $operation_type = 'update';
                $userReviewModel->abstractUpdate($value, array(
                    'id' => $review_id
                ));
                $userMenuReview = new UserMenuReview ();
                $userMenuReview->abstractDelete(array(
                    'user_review_id' => $review_id
                ));
            } else {
                $review_id = $userReviewModel->insert($value);
            }
            if (!empty($data ['menu'])) {
                $image = array();
                $item_ids = array();
                $loved_it = array();
                $userMenuReview = new UserMenuReview ();
                
               
                foreach ($data ['menu'] as $single) {
                    if ($single ['image'] != null) {
                        if (strpos($single ['image'], 'data:image') === false) {
                            $image = array_pop(explode('/', $single ['image']));
                        } else {     
                           // echo APP_PUBLIC_PATH.USER_REVIEW_IMAGE_UPLOAD . $restCode['rest_code'] . DS . 'reviews' . DS;
                            $response = StaticOptions::getImagePath($single ['image'], APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . strtolower($restCode['rest_code']) . DS . 'reviews' . DS);
                            if ($response) {
                                $imagePathToArray = explode('/', $response);
                                $image = array_pop($imagePathToArray);
                                //$image = array_pop ( $imagePathToArray ) . '/' . $imageName;
                            } else {
                                $image = '';
                            }
                        }
                    } else {
                        $image = '';
                    }
                    $singleMenu ['image_name'] = $image;
                    $singleMenu ['menu_id'] = $single ['item_id'];
                    $singleMenu ['liked'] = $single ['loved_it'];
                    $singleMenu ['user_review_id'] = $review_id;
                    $userMenuReview->insert($singleMenu);
                }
            }
            if (isset($data ['picture']) && $data ['picture'] != null) {
                $bill['restaurant_id'] = $data ['restaurant_id'];
                $bill['user_id'] = $userId;
                $bill['caption'] = isset($data['caption'])? $data['caption'] : '';
                $bill['status'] = 2;
                $bill['image_type'] = 'b';
                $bill['image_status'] = 0;
                $bill['created_on'] = $currentDateTime;
                $bill['updated_on'] = $currentDateTime;
                $bill['sweepstakes_status_winner'] = 0;
                
                $response = StaticOptions::getImagePath($data ['picture'], APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . strtolower($restCode['rest_code']) . DS . 'gallery' . DS);
                $bill['image_url'] = $response;
                if ($response) {
                    $imagePathToArray = explode('/', $response);
                    $image = array_pop($imagePathToArray);
                } else {
                    $image = '';
                }
                $bill['image'] = $image;
                $bill['source'] = '0';
                $userrestaurantimagemodel->createRestaurantImage($bill);
            }
            //give points for review
            /* if($userId){
              $userFunctions = new UserFunctions();
              $points = $userFunctions->getAllocatedPoints ( 'i_ratereview' );
              $message = 'Your opinion is worth the world to us! Also, it\'s worth 5 points.';
              $userFunctions->givePoints($points,$userId,$message);
              } */
            ########## Dine and More Point will awarded ####################
            $awardsPoint = [];
            if($userId){
                $userFunctions = new \User\UserFunctions();
                $userFunctions->userId = $userId;
                $userFunctions->restaurantId = $value ['restaurant_id'];
                $userFunctions->activityDate = $currentDateTime;
                $awardsPoint = $userFunctions->dineAndMoreAwards("awardsreview");
            }
            ##################################################
            if ($value ['order_id'] != 0) {
                // update is_review field
                $userReservationModel = new UserReservation();
                $data = array(
                    'is_reviewed' => 1,
                    'review_id' => $review_id
                );
                $userReservationModel->id = $value ['order_id'];
                $userReservationModel->update($data);
            }
            /**
             * Send Review Mail To Resturant Owner
             */
            $notificationMsg='We’re reading your review of '.$restCode['restaurant_name'].' with bated breath.';
               $channel = "mymunchado_" .$userId;
               $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $userId,
                    "type" => 'reviews',    
                    "restaurantId" => $value['restaurant_id'],        
                    'curDate' => $currentDateTime,
                    'user_id'=>$userId,
                   'review_id'=>$review_id,
                   'first_name'=>ucfirst($userDetail['first_name']),
                   'restaurant_exist'=>1,
                   'restaurant_name'=>$restCode['restaurant_name'],
                   'is_friend'=>0
                );
                $notificationJsonArray = array('is_friend'=>0,'user_id'=>$userId,'review_id'=>$review_id,'first_name'=>ucfirst($userDetail['first_name']),'restaurant_exist'=>1,'restaurant_id' => $value['restaurant_id'],'restaurant_name'=>$restCode['restaurant_name']);
                $pub = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);        
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
            //$webOrderController = new WebOrderReviewController();
            //$email = $webOrderController->sendReviewMailToOwner($value ['restaurant_id'], $userId);
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->commit();
            if(isset($awardsPoint['points'])){
                $points = (int)$awardsPoint['points'];
            }else{
                $points = 5;
            }
            
            $cleverTap = array(
                "review_id"=>$review_id,                
                "user_id"=>$userId,
                "name"=>$userDetail['first_name'],
                "email"=>$userDetail['email'],
                "identity"=>$userDetail['email'],
                "restaurant_name"=>$restCode['restaurant_name'],
                "restaurant_id"=>$value['restaurant_id'],
                "eventname"=>"review",  
                "review_type"=>"reservation",
                "earned_points"=>$points,
                "is_register"=>"yes",
                "review_date"=>$currentDateTime,
                "event"=>1
            );
            
            $userFunctions->createQueue($cleverTap, 'clevertap');
            return array(
                'success' => 'true',
                'operation_type' => $operation_type,
                'point'=>$points
            );
        } catch (\Exception $e) {
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->rollback();
            throw new \Exception("Something apparently went wrong. Review Not submitted.", 400);
        }
    }

    public function get($id) {
        $userReviewModel = new UserReview ();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'umr' => 'user_menu_reviews'
            ),
            'on' => 'umr.user_review_id = user_reviews.id',
            'columns' => array(
                'menu_id',
                'image_name',
                'liked'
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'm' => 'menus'
            ),
            'on' => 'm.id = umr.menu_id',
            'columns' => array(
                'item_name'
            ),
            'type' => 'left'
        );
       
        $joins[]=array(
            'name'=>array(
                'r'=>'restaurants'
            ),
            'on' => 'r.id=user_reviews.restaurant_id',
            'columns'=>array('rest_code'),
            'type'=>'left'
        );

        $options = array(
            'columns' => array(
                'noice_level' => 'noise_level',
                'service' => 'services',
                'taste_test',
                'come_back',
                'rating',
                'order_again',
                'rating',
                'review_desc',
                'review_for',
                'restaurant_id',
                'user_id',
                'status',
                'order_id',
                'created_on'
            ),
            'where' => array(
                'user_reviews.id' => $id
            ),
            'joins' => $joins
        );
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userReviewDetail = $userReviewModel->find($options)->toArray();
        $refinedReview = array();
        if (!empty($userReviewDetail)) {
            foreach ($userReviewDetail as $key => $value) {
                if (!isset($refinedReview['menu'])) {
                    $refinedReview = $userReviewDetail[$key];
                    if (isset($refinedReview['created_on'])) {
                        $refinedReview['created_on'] = StaticOptions::getFormattedDateTime($refinedReview['created_on'], 'Y-m-d H:i:s', 'd M, Y');
                    }
                    if (isset($refinedReview['picture'])) {
                        $refinedReview['picture'] = WEB_URL . USER_IMAGE_UPLOAD . strtolower($value['rest_code']) . DS . 'reviews' . DS . $userReviewDetail [$key] ['picture'];
                    }
                    unset($refinedReview['menu_id']);
                    unset($refinedReview['image_name']);
                    unset($refinedReview['item_name']);
                    unset($refinedReview['liked']);
                    $refinedReview['menu'] = array();
                }
                $refinedReview['menu'] [] = array(
                    'item_id' => $userReviewDetail [$key] ['menu_id'],
                    'item' => $userReviewDetail [$key] ['item_name'],
                    'image' => ($userReviewDetail [$key] ['image_name'] != null) ? WEB_URL . USER_IMAGE_UPLOAD . strtolower($value['rest_code']) . DS . 'reviews' . DS . $userReviewDetail [$key] ['image_name'] : $userReviewDetail [$key] ['image_name'],
                    'loved_it' => $userReviewDetail [$key] ['liked']
                );
            }
        }
        return $refinedReview;
    }

}
