<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReview;
use User\Model\UserMenuReview;
use User\Model\DbTable\UserReviewTable;
use MCommons\StaticOptions;
use User\UserFunctions;
use User\Model\UserOrder;
use Restaurant\Model\RestaurantAccounts;
use User\Model\User;
use Restaurant\Model\Restaurant;
use User\Model\UserNotification;

class WebOrderReviewController extends AbstractRestfulController {

    public function create($data) {
        if (!isset($data ['rating']) || $data ['rating'] == null) {
            throw new \Exception('Select Rating');
        }
        if (!isset($data ['restaurant_id']) || $data ['restaurant_id'] == null) {
            throw new \Exception('Please send Restaurant Id');
        }
        if (!isset($data ['review_for']) || $data ['review_for'] == null) {
            throw new \Exception('Please tell what are you reviewing');
        }
        $userId = $this->getUserSession()->getUserId();
        if (!$userId) {
            throw new \Exception('User Not Logged in');
        }
        try {
            $dbtable = new UserReviewTable ();
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->beginTransaction();
            $userReviewModel = new UserReview ();
            $userNotificationModel = new UserNotification();
            $restaurantModel = new Restaurant();
            $selectedLocation = $this->getUserSession()->getUserDetail ( 'selected_location', array () );   
            $cityModel = new \Home\Model\City();//18848
            $cityId = isset($selectedLocation ['city_id'])?$selectedLocation ['city_id']:18848;
            $cityDetails = $cityModel->cityDetails($cityId);
            $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
                        'state_code' => $cityDetails [0] ['state_code']
                    ));
       
             $currentDateTime = $cityDateTime->format('Y-m-d H:i:s');
        
            $operation_type = 'insert';
            $review_id = isset($data ['review_id']) ? $data ['review_id'] : 0;
            $value ['on_time'] = isset($data ['on_time']) ? $data ['on_time'] : 0;
            $value ['fresh_prepared'] = isset($data ['fresh_prepared']) ? $data ['fresh_prepared'] : 0;
            $value ['as_specifications'] = isset($data ['as_specifications']) ? $data ['as_specifications'] : 0;
            $value ['taste_test'] = isset($data ['taste_test']) ? $data ['taste_test'] : 1;
            $value ['temp_food'] = isset($data ['temp_food']) ? $data ['temp_food'] : 0;
            $value ['order_again'] = isset($data ['order_again']) ? $data ['order_again'] : 0;
            $value ['rating'] = $data ['rating'];
            $value ['sentiment'] = ($data ['rating'] > 2) ? 1 : 0;
            $value ['review_desc'] = isset($data ['review_desc']) ? $data ['review_desc'] : '';
            $value ['review_for'] = $data ['review_for'];
            $value ['restaurant_id'] = $data ['restaurant_id'];
            $value ['user_id'] = $userId;
            $value ['status'] = 0;
            $value ['order_id'] = isset($data ['order_id']) ? $data ['order_id'] : 0;
            $value ['created_on'] = $currentDateTime;
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
            $restaurantDetailOption = array('columns'=>array('rest_code','restaurant_name'),'where'=>array('id'=>$data ['restaurant_id']));
            $restCode = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray();
            if (!empty($data ['menu'])) {
                $image = array();
                $item_ids = array();
                $loved_it = array();
                $userMenuReview = new UserMenuReview ();
                $singleMenu = array();
                foreach ($data ['menu'] as $single) {
                    if ($single ['image'] != null) {                       
                        if(preg_match('/^(http|https):\\/\\//', $single['image'], $matches) == 1){
                            $imagePathToArray = explode('/', $single['image']);                            
                            $image = array_pop($imagePathToArray);                            
                        }elseif(preg_match('/^(data:image)\\//', $single['image'], $matches) == 1){
                            $response = StaticOptions::getImagePath($single ['image'], APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . strtolower($restCode['rest_code']). DS . 'reviews' . DS);
                            if ($response) {
                                $imagePathToArray = explode('/', $response);
                                $image = array_pop($imagePathToArray);                               
                                // $image = array_pop($imagePathToArray) . '/' . $imageName;
                            } else {
                                $image = '';
                            }
                        }else{
                           throw new \Exception('Invalid image.');
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

            if ($value ['order_id'] != 0) {
                // update is_review field
                $userOrderModel = new UserOrder ();
                $data = array(
                    'is_reviewed' => 1,
                    'review_id' => $review_id
                );
                $userOrderModel->id = $value ['order_id'];
                $userOrderModel->update($data);
            }
            $userModel = new \User\Model\User();
            $userDetailOption = array('columns' => array('first_name','last_name','email'), 'where' => array('id' => $userId));
            $userDetail = $userModel->getUser($userDetailOption);
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
            // give points for review
            /*
             * if ($userId) { $userFunctions = new UserFunctions(); $points = $userFunctions->getAllocatedPoints('i_ratereview'); $message = 'Your opinion is worth the world to us! Also, it\'s worth 5 points.'; $userFunctions->givePoints($points, $userId,$message); }
             */
            ########## Dine and More Point will awarded ####################
            $awardsPoint = [];
            if ($userId) {
                $userFunctions = new UserFunctions();
                $userFunctions->userId = $userId;
                $userFunctions->restaurantId = $value ['restaurant_id'];
                $userFunctions->activityDate = $currentDateTime;
                $userFunctions->restaurant_name = $restCode['restaurant_name'];
                $userFunctions->typeValue = $review_id;
                $userFunctions->typeKey = 'review_id';
                $awardsPoint = $userFunctions->dineAndMoreAwards("awardsreview");
            }
            
            if(isset($awardsPoint['points'])){
                $points = (int)$awardsPoint['points'];
            }else{
                $points = 5;
            }
            ##################################################

            /**
             * Send Mail TO Restaurant Owner
             */
            $mail = $this->sendReviewMailToOwner($value ['restaurant_id'], $userId);
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->commit();
            if($value ['review_for'] == "1"){
                $type = "delivery";
            }elseif($value ['review_for']=="2"){
                $type = "takeout";
            }else{
                $type = "reservation";
            }
            $cleverTap = array(
                "review_id"=>$review_id,                
                "user_id"=>$userId,
                "name"=>$userDetail['first_name'],
                "email"=>$userDetail['email'],
                "identity"=>$userDetail['email'],
                "restaurant_name"=>$restCode['restaurant_name'],
                "restaurant_id"=>$value['restaurant_id'],
                "review_type"=>$type,
                "eventname"=>"review",               
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
                'on_time',
                'fresh_prepared',
                'as_specifications',
                'taste_test',
                'temp_food',
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
                if (!isset($refinedReview ['menu'])) {
                    $refinedReview = $userReviewDetail [$key];
                    if ($refinedReview ['created_on'] != null) {
                        $refinedReview ['created_on'] = StaticOptions::getFormattedDateTime($refinedReview ['created_on'], 'Y-m-d H:i:s', 'd M, Y');
                    }
                    unset($refinedReview ['menu_id']);
                    unset($refinedReview ['image_name']);
                    unset($refinedReview ['item_name']);
                    unset($refinedReview ['liked']);
                    $refinedReview ['menu'] = array();
                }
                $refinedReview ['menu'] [] = array(
                    'item_id' => $userReviewDetail [$key] ['menu_id'],
                    'item' => $userReviewDetail [$key] ['item_name'],
                    'image' => ($userReviewDetail [$key] ['image_name'] != null) ? WEB_URL . USER_IMAGE_UPLOAD . strtolower($value['rest_code']) . DS . 'reviews' .  DS . $userReviewDetail [$key] ['image_name'] : $userReviewDetail [$key] ['image_name'],
                    'loved_it' => $userReviewDetail [$key] ['liked']
                );
            }
        }
        return $refinedReview;
    }

    public function update($id, $data) {
        
    }

    public function sendReviewMailToOwner($restaurantId, $userId) {
        $config = StaticOptions::getServiceLocator()->get('Config');
        $webUrl = $config['image_base_urls']['local-cms'];

        $user_function = new UserFunctions ();
        $restaurantAccountModel = new RestaurantAccounts ();
        $userModel = new User ();
        $resData = $restaurantAccountModel->getRestaurantAccountDetail(array(
            'columns' => array(
                'status',
                'email',
                'name'
            ),
            'where' => array(
                'restaurant_id' => $restaurantId
            )
                ));
        $userData = $userModel->getUserDetail(array(
            'column' => array(
                'first_name'
            ),
            'where' => array(
                'id' => $userId
            )
                ));
        $userName = $userData ['first_name'];
        if (isset($resData)) {
            $sendMailToRestaurant = $restaurantAccountModel->checkRestaurantForMail($restaurantId, 'reservation');
            if ($sendMailToRestaurant == true) {
                $sendCancelMailToOwnerArray = array(
                    'receiver' => array(
                        $resData ['email']
                    ),
                    'variables' => array(
                        'ownername' => $resData ['name'],
                        'username' => ucfirst($userName),
                        'dashboard_url' => $webUrl
                    ),
                    'subject' => 'Someone Posted a New Review for You on Munch Ado',
                    'template' => 'review-mail-to-owner'
                );
                $user_function->sendMailsToRestaurant($sendCancelMailToOwnerArray);
            }
        }
    }

}
