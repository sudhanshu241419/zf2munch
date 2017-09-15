<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReview;
use MCommons\StaticOptions;
use User\Model\UserMenuReview;
use User\Model\UserOrder;
use User\Model\UserReservation;
use MCommons\CommonFunctions;
use User\Model\User;
use User\Model\DbTable\UserReviewTable;
use User\UserFunctions;
use Restaurant\Model\RestaurantAccounts;
use Restaurant\Model\Restaurant;
use User\Model\UserTip;
use User\Model\UserReviewImage;
use Zend\Http\PhpEnvironment\Request;

class ReviewController extends AbstractRestfulController {

    public $formatCount = array();

    // Review Type array for temporary basis we need to move it to some common place
    public function get($review_id = 0) {
        $config = $this->getServiceLocator()->get('Config');
        $userId = $this->getUserSession()->getUserId();
        $friendId = $this->getQueryParams('friendid', false);
        if ($friendId) {
            $userId = $friendId;
        }
        if (!$review_id)
            throw new \Exception("Invalid Parameters", 400);

        $userReviewModel = new UserReview ();
        $commonFucntions = new CommonFunctions ();
        $userFunctions = new UserFunctions();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'u' => 'users'
            ),
            'on' => 'u.id = user_reviews.user_id',
            'columns' => array(
                'first_name',
                'display_pic_url',
                'created_at',
                'shipping_address',
                'city_id'
            ),
            'type' => 'left'
        );

        $joins[] = array(
            'name' => array(
                'r' => 'restaurants'
            ),
            'on' => 'r.id=user_reviews.restaurant_id',
            'columns' => array('rest_code','restaurant_name','inactive','closed'),
            'type' => 'left'
        );
        $joins[] = array(
            'name' => array(
                'owner' => 'owner_response'
            ),
            'on' => 'owner.review_id=user_reviews.id',
            'columns' => array('owner_response_id'=>'id','response','response_date'),
            'type' => 'left'
        );
        
        $options = array(
            'columns' => array(
                'review_id' => 'id',
                'restaurant_id',
                'user_id',
                'review_for',
                'on_time',
                'fresh_prepared',
                'as_specifications',
                'temp_food',
                'taste_test',
                'services',
                'noise_level',
                'rating',
                'order_again',
                'come_back',
                'review_desc',                
                'sentiment',                
                'date' => 'created_on',
                'order_id',
                'status'
            ),
            'where' => array(
                'user_reviews.id' => $review_id,
                'user_reviews.status' => array(0,1,2)

            ),
            'joins' => $joins
        );
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $userReviewModel->find($options)->toArray();
         if (!empty($response)) {

           $ownerResponse = array();
           $ownerResponseMessage ="";
           $responseDate = "";           
           $i = 0;
           foreach($response as $key => $or){
               
              if(isset($or['owner_response_id'])){
                $ownerResponse[$i]['owner_response_id'] = $or['owner_response_id'];
                $ownerResponse[$i]['owner_response'] = $or['response'];
                $ownerResponse[$i]['restaurant_responded_on'] = $or['response_date'];                 
                $ownerResponseMessage = $or['response'];
                $responseDate = $or['response_date'];
               $i++;
              }
           }
           
                        
            $reviewDetail = $response[0];
            $data = $commonFucntions->getUserHistoryForMob(array($reviewDetail['user_id']));
        } else {
            throw new \Exception("Review not exist", 400);
        }
        unset($data ['joined_on']);
        $review = array();

        if (is_array($data)) {
            array_walk($data, array(
                $this,
                'mapper'
            ));
        }
        if (!$reviewDetail){
            throw new \Exception("Records not found", 400);
        }

        $reviewDetail['stats'] = array();

        $reviewDetail['stats']['first_name'] = $reviewDetail['first_name'];
        $reviewDetail['stats']['shipping_address'] = $reviewDetail['shipping_address'];
        unset($reviewDetail['first_name']);
        unset($reviewDetail['shipping_address']);
        $reviewDetail['stats']['total_beenthere'] = isset($this->formatCount ['beenthere'] [$reviewDetail ['user_id']] ['total_beenthere']) ? $this->formatCount ['beenthere'] [$reviewDetail ['user_id']] ['total_beenthere'] : 0;
        $reviewDetail['stats']['total_tryit'] = isset($this->formatCount ['totalOrder'] [$reviewDetail ['user_id']] ['total_order']) ? $this->formatCount ['totalOrder'] [$reviewDetail ['user_id']] ['total_order'] : 0;
        $reviewDetail['stats']['total_reservations'] = isset($this->formatCount ['reserve'] [$reviewDetail ['user_id']] ['total_reservation']) ? $this->formatCount ['reserve'] [$reviewDetail ['user_id']] ['total_reservation'] : 0;
        $reviewDetail['stats']['total_reviews'] = isset($this->formatCount ['review'] [$reviewDetail ['user_id']] ['total_reviews']) ? $this->formatCount ['review'] [$reviewDetail ['user_id']] ['total_reviews'] : 0;

//        if ($reviewDetail ['menu_id'] != null && !empty($reviewDetail ['menu_id'])) {
//            $reviewDetail['menu_review_images'] [] = array(
//                'item_id' => $reviewDetail ['menu_id'],
//                'item' => $reviewDetail ['menu_name'],
//                'image' => ($reviewDetail ['image_name']) ? WEB_URL . USER_IMAGE_UPLOAD . strtolower($reviewDetail['rest_code']) . DS . 'reviews' . DS . $reviewDetail ['image_name'] : ""
//            );
//        } else {
//            $reviewDetail ['menu_review_images'] = array();
//        }
//        unset($reviewDetail['menu_id'], $reviewDetail['menu_name'], $reviewDetail['image_name']);
        $reviewDetail['review_for'] = $config['constants']['review_for'][$reviewDetail['review_for']];
        $reviewDetail['come_back'] = ($reviewDetail['come_back']==0)?"2":$reviewDetail['come_back'];
        $reviewDetail['date'] = !$reviewDetail ['date'] ? '' : $reviewDetail ['date'];
        $reviewDetail['on_time'] = ($reviewDetail['on_time']==0)?"2":$reviewDetail['on_time'];
        $reviewDetail['fresh_prepared'] = ($reviewDetail['fresh_prepared']==0)?"2":$reviewDetail['fresh_prepared'];
        $reviewDetail['as_specifications'] = ($reviewDetail['as_specifications']==0)?"2":$reviewDetail['as_specifications'];
        $reviewDetail['temp_food'] = ($reviewDetail['temp_food']==0)?"1":$reviewDetail['temp_food'];
        $reviewDetail['taste_test'] = ($reviewDetail['taste_test']==0)?"1":$reviewDetail['taste_test'];
        $reviewDetail['services'] = ($reviewDetail['services']==0)?"1":$reviewDetail['services'];
        $reviewDetail['noise_level'] = ($reviewDetail['noise_level']==0)?"1":$reviewDetail['noise_level'];
        $reviewDetail['order_again'] = ($reviewDetail['order_again']==0)?"2":$reviewDetail['order_again'];
        $reviewDetail['order_id'] = ($reviewDetail['order_id']==0)?NULL:$reviewDetail['order_id'];
        
        if ($reviewDetail['inactive'] == 1 || $reviewDetail['closed'] == 1) {
             $reviewDetail['is_restaurant_exist'] = 0;
        } else {
             $reviewDetail['is_restaurant_exist'] = 1;
        }
          // pr($reviewDetail,true);    
        $reviewDetail['stats']['joined_on']=!$reviewDetail ['created_at']?'':$commonFucntions->datetostring($reviewDetail ['created_at']);
        $myLastEarnedMuncher = $userFunctions->getMyLastEarnedMuncher();
        if($myLastEarnedMuncher){
            $reviewDetail['stats']['badge']=$myLastEarnedMuncher['title'];
        }else{
            $reviewDetail['stats']['badge']=null; 
        }
        $cityModel = new \Home\Model\City();
        $cityDetails = $cityModel->cityDetails($reviewDetail['city_id']);
        if($cityDetails){
            $reviewDetail['stats']['city']=$cityDetails[0]['city_name'];
        }else{
            $reviewDetail['stats']['city']='';
        }
         $data = $commonFucntions->checkProfileImageUrl(array(
                'display_pic_url' => $reviewDetail ['display_pic_url'],
                'id' => $reviewDetail ['user_id']
            ));
        $reviewDetail['stats']['display_pic_url']=(isset($data['display_pic_url']) && !empty($data['display_pic_url']))?$data['display_pic_url']:WEB_URL . 'img' . DS . 'noimage.jpg';
        $reviewDetail['owner_response']= $ownerResponseMessage;
        $reviewDetail['restaurant_responded_on']= $responseDate;
        $reviewDetail['all_owner_response'] = $ownerResponse;
        unset($reviewDetail ['response'],$reviewDetail ['response_date'],$reviewDetail ['created_at'],$reviewDetail['rest_code'], $reviewDetail['display_pic_url']);
        unset($reviewDetail['inactive'], $reviewDetail['closed']);
        
        ###### Review Find UseFull ######        
        $findUsefullCount = $userFunctions->isReviewUsefullCount($review_id);        
        if(!empty($findUsefullCount) && $findUsefullCount!=null && $findUsefullCount['total_usefull_count']!=0){
            $reviewDetail['review_find_useful']['count'] = $findUsefullCount['total_usefull_count'];
        }else{
            $reviewDetail['review_find_useful']['count'] = "0";
        }
        $findUsefullForUser = $userFunctions->isReviewUsefullForUser($review_id,$userId);
        if(!empty($findUsefullForUser) && $findUsefullForUser!=null && $findUsefullForUser['total_usefull_count']!=0){
            $reviewDetail['review_find_useful']['find_useful'] = $findUsefullForUser['feedback'];
        }else{
            $reviewDetail['review_find_useful']['find_useful'] = "2";
        }
        ####################################
        $options='';
        $options = array('columns'=>array('image_url'),'where'=>array('user_review_id'=>$review_id));
        $userReviewImage = new UserReviewImage();
        $userReviewImage->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $reviewImages = $userReviewImage->find($options)->toArray();
        if(count($reviewImages) > 0){
            foreach($reviewImages as $key => $val){
                $reviewDetail['review_images'][] = $val['image_url'];
            }
        }else{
             $reviewDetail['review_images'] = array();
        }
        ########## Review Menu Detail ############
        $menuReview = new \User\Model\UserMenuReview();
        $menuReviewJoin = array();
       
        $menuReviewJoin [] = array(
            'name' => array(
                'm' => 'menus'
            ),
            'on' => 'm.id = user_menu_reviews.menu_id',
            'columns' => array(
                'item_name',               
            ),
            'type' => 'left'
        );
                
        $menuReviewOption = array(
            'columns' => array(
               'is_liked'=>'liked',
               'menu_id'
            ),
            'where' => array(
                'user_review_id' => $review_id,
            ),
            'joins' => $menuReviewJoin
        );
         $menuReview->getDbTable()->setArrayObjectPrototype('ArrayObject');
         $reviewedMenu = $menuReview->find($menuReviewOption)->toArray();
        if ($reviewedMenu) {
            foreach($reviewedMenu as $key => $val){
                $reviewedMenu[$key]['is_liked']=intval($val['is_liked']);
            }
            $items = array('items'=>$reviewedMenu);
        }else{
            $items = array('items'=>null);
        }
        ##########################################
        $reviewDetail = array_merge($reviewDetail,$items);
        
        return $reviewDetail;
    }

    public function create($data) {
        if(isset($data['review_id'])){
            return $this->editReview($data);
        }else{
            return $this->addReview($data);
        }
    }
    
    
    public function addReview($data){
        $config = $this->getServiceLocator()->get('Config');
        $userId = $this->getUserSession()->getUserId();
        $userNotificationModel = new \User\Model\UserNotification();
        $userFunctions = new UserFunctions();
        if (empty($data)) {
            throw new \Exception("Invalid Parameters", 400);
        }
        if (!$userId) {
            throw new \Exception('User Not Logged in');
        }
        if (!isset($data ['rating']) || $data ['rating'] == null) {
            throw new \Exception('Select Rating');
        }
        if (!isset($data ['restaurant_id']) || $data ['restaurant_id'] == null) {
            throw new \Exception('Please send Restaurant Id');
        }
        if (!isset($data ['review_for']) || $data ['review_for'] == null) {
            throw new \Exception('Please tell what are you reviewing');
        }
        
        $reviewFor = $data ['review_for'];
        
        $selectedLocation = $this->getUserSession()->getUserDetail ( 'selected_location', array () );   
        $cityModel = new \Home\Model\City();//18848
        $cityId = isset($selectedLocation ['city_id'])?$selectedLocation ['city_id']:18848;
        $cityDetails = $cityModel->cityDetails($cityId);
        $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
                        'state_code' => $cityDetails [0] ['state_code']
                    ));
       
        $currentDateTime = $cityDateTime->format('Y-m-d H:i:s');
        
        $userModel = new \User\Model\User();
        $userDetailOption = array('columns' => array('first_name','last_name','email'), 'where' => array('id' => $userId));
        $userDetail = $userModel->getUser($userDetailOption);
        $userName = (isset($userDetail['last_name']) && !empty($userDetail['last_name']))?$userDetail['first_name']." ".$userDetail['last_name']:$userDetail['first_name'];
        try {

            $dbtable = new UserReviewTable ();
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->beginTransaction();

            $userReviewModel = new UserReview ();                       
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
            $value ['restaurant_id'] = trim($data['restaurant_id']);
            $value ['user_id'] = $userId;
            $value ['status'] = 0;
            $value['services'] = isset($data ['services']) ? $data ['services'] : 0;
            $value['noise_level'] = isset($data ['noise_level']) ? $data ['noise_level'] : 0;
            $value['come_back'] = isset($data ['come_back']) ? $data ['come_back'] : 0;
            
            if ($data ['review_for'] == 3) {
                $value ['order_id'] = isset($data ['reservation_id']) ? $data ['reservation_id'] : 0;
            } else {
                $value ['order_id'] = isset($data ['order_id']) ? $data ['order_id'] : 0;
            }
            $value ['created_on'] = $currentDateTime;
            
            $review_id = $userReviewModel->insert($value);
            
            $userReviewImage = new UserReviewImage();
            $restaurantModel = new Restaurant();
            $restaurantDetailOption = array('columns' => array('rest_code','restaurant_name'), 'where' => array('id' => $data ['restaurant_id']));
            $restDetail = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray();
            $imgForFeed= array();
            $files = '';
            $request = new Request ();
            $files = $request->getFiles ();
            
            if (isset($files) && !empty($files) && count($files)>0) {
                $response = StaticOptions::uploadUserImages($files, APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . strtolower($restDetail['rest_code']) . DS . 'reviews' . DS);
                if (!empty($response)) {
                    foreach ($response as $key => $val) {
                        $data1['user_review_id'] = $review_id;
                        $data1['created_at'] = $currentDateTime;
                        $data1['image_status'] = 0;
                        $data1['image_url'] = $val['path'];
                        $arr_img_path = explode('/', $val['path']);
                        $length = count($arr_img_path);
                        $data1['image'] = $arr_img_path [$length - 1]; //                        
                        $addImageResponse = $userReviewImage->insert($data1);
                        $imgForFeed[] = $val;
                    }
                }
            }          
            
            if(isset($data['menu']) && is_string($data['menu'])&& !empty($data['menu'])){
                $data['menu'] = json_decode($data['menu'],true);
            }

            if (isset($data ['menu']) && !empty($data ['menu'])) {
                $image = array();
                $item_ids = array();
                $loved_it = array();
                $userMenuReview = new UserMenuReview ();
                $singleMenu = array();
                foreach ($data ['menu'] as $single) {
                    $singleMenu ['image_name'] = '';
                    $singleMenu ['menu_id'] = $single ['item_id'];
                    $singleMenu ['liked'] = $single ['loved_it'];
                    $singleMenu ['user_review_id'] = $review_id;
                    $userMenuReview->insert($singleMenu);
                }
            }
            if ($data ['review_for'] == 3 && $value ['order_id'] != 0) {
                $userReservationModel = new UserReservation();
                $data = array(
                    'is_reviewed' => 1,
                    'review_id' => $review_id
                );
                $userReservationModel->id = $value ['order_id'];
                $userReservationModel->update($data);
                $reviewtype = "reservation";
            } elseif (($data ['review_for'] == 1 || $data ['review_for'] == 2) && $value ['order_id'] != 0) {
                // update is_review field
                $userOrderModel = new UserOrder ();
                $data = array(
                    'is_reviewed' => 1,
                    'review_id' => $review_id
                );
                $reviewtype = "order";
                $userOrderModel->id = $value ['order_id'];
                $userOrderModel->update($data);
            }
            
            #   Add activity feed data   # 
               $commonFunctiion = new \MCommons\CommonFunctions();               
               $replacementData = array('restaurant_name'=>$restDetail['restaurant_name']);
               $otherReplacementData = array();
               $value['on_time'] = (string)$value['on_time']; 
               $value['as_specifications'] = (string)$value['as_specifications'];                   
                $value['taste_test'] = (string)$value['taste_test'];
                $value['temp_food'] = (string)$value['temp_food'];
                $value['rating'] = (string)$value['rating'];
                $value['sentiment'] = (string)$value['sentiment'];
                $value['review_for'] = (string)$config['constants']['review_for'][$value['review_for']];
                $value['user_id'] = (string)$value['user_id'];
                $value['status'] = (string)$value['status'];
                $value['services'] = (string)$value['services'];
                $value['noise_level'] = (string)$value['noise_level'];
                $value['order_id'] = (string)$value['order_id'];
                $value['fresh_prepared'] = (string)$value['fresh_prepared'];
                $value['order_again'] = (string)$value['order_again'];
                $value['come_back'] = (string)$value['come_back'];
                
               $feed = array(      
                      'restaurant_id'=>$value['restaurant_id'],
                      'restaurant_name'=>$restDetail['restaurant_name'],
                      'user_name'=>ucfirst($userName),                        
                      'img'=>$imgForFeed,
                      'review'=>$value,
                   );
              
               $activityFeed = $commonFunctiion->addActivityFeed($feed, 9, $replacementData, $otherReplacementData);
               $notificationMsg='We’re reading your review of '.$restDetail['restaurant_name'].' with bated breath.';
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
                   'restaurant_name'=>$restDetail['restaurant_name'],
                   'is_friend'=>0
                );
                
                $notificationJsonArray = array('is_friend'=>0,'user_id'=>$userId,'review_id'=>$review_id,'first_name'=>ucfirst($userDetail['first_name']),'restaurant_exist'=>1,'restaurant_id' => $value['restaurant_id'],'restaurant_name'=>$restDetail['restaurant_name']);
                $pub = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);        
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                
            ###############################
               
             # Assign muncher #
                //$userFunctions->userAvatar('review');
             ##################

            /**
             * Send Mail TO Restaurant Owner
             */
            //$mail = $this->sendReviewMailToOwner($value ['restaurant_id'], $userId);
               
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->commit();
            ####################### Assign points user for registration #######################
//            $points = $userFunctions->getAllocatedPoints('i_ratereview');        
//            $message = 'Reviewed! You\'ll need points to have the most fun, here take 5. Hoard them wisely.';
//            $userFunctions->givePoints($points, $userId, $message); 
            #############################################################################
            
            ########## Dine and More Point will awarded ####################
            $userFunctions->userId = $userId;
            $userFunctions->restaurantId = $value ['restaurant_id'];
            $userFunctions->activityDate = $currentDateTime;
            $awardsPoint = $userFunctions->dineAndMoreAwards("awardsreview");
            ##################################################
            
            if(isset($awardsPoint['points'])){
                $points = (int)$awardsPoint['points'];
            }else{
                $upoints = $userFunctions->getAllocatedPoints("i_ratereview");
                $points = $upoints['points'];
            }
            
            $userPoint = $userFunctions->userTotalPoint($userId);
            ##########################NetCore##################
            if($reviewFor == 1){
                $reviewType = "delivery";
            }elseif($reviewFor==2){
                $reviewType = "takeout";
            }else{
                $reviewType = "reservation";
            }
            $cleverTap = array(
                "review_id"=>$review_id,                
                "user_id"=>$userId,
                "name"=>$userDetail['first_name'],
                "email"=>$userDetail['email'],
                "identity"=>$userDetail['email'],
                "restaurant_name"=>$restDetail['restaurant_name'],
                "restaurant_id"=>$value['restaurant_id'],
                "eventname"=>"review",  
                "review_type"=>$reviewType,
                "earned_points"=>$points,
                "is_register"=>"yes",
                "review_date"=>$currentDateTime,
                "event"=>1
            );
            
            $userFunctions->createQueue($cleverTap, 'clevertap');
            ###################################################
            return array(
                'success' => 'true','point'=> (int)$points, 'user_points'=>$userPoint            
            );
        } catch (\Exception $e) {
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->rollback();
            throw new \Exception("Something apparently went wrong. Review Not submitted.", 400);
        }
    }

    public function addUserMenuItems($review_id, $rest_id, $menus) {
        $userMenuItemsModel = new UserMenuReview ();
        $items = array();
        try {
            foreach ($menus as $menu) :
                $userMenuItemsModel->user_review_id = $review_id;
                if (isset($menu ['menu_id'])) {
                    $userMenuItemsModel->menu_id = $menu ['menu_id'];
                }
                if (isset($menu ['menu_image'])) {
                    $newName = StaticOptions::getImagePath($menu ['menu_image'], APP_PUBLIC_PATH, 'user_images' . DS . 'reviews' . DS . $rest_id . DS . 'menu' . DS);
                    if ($newName) {
                        $image = array_pop(explode('/', $newName));
                    } else {
                        $image = '';
                    }

                    $userMenuItemsModel->image_name = $image;
                }
                if (isset($menu ['liked'])) {
                    $userMenuItemsModel->liked = $menu ['liked'];
                } else {
                    throw new \Exception('Value of liked dose not exists', 400);
                }
                $items [] = $userMenuItemsModel->addItemsReview();
            endforeach
            ;
        } catch (\Exception $exp) {
            return $this->sendError(array(
                        'error' => $exp->getMessage()
                            ), $exp->getCode());
        }
        return $items;
    }

    public function mapper($value, $key) {
        foreach ($value as $single) {
            if(isset($single ['user_id'])){
                $this->formatCount [$key] [$single ['user_id']] = $single;
            }
        }
    }

    public function delete($review_id) {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception("User unavailable", 400);
        }

        if ($review_id) {
            $userReviewModel = new UserReview ();
            $userReviewModel->id = $review_id;
            $deleted = $userReviewModel->delete();

            return array(
                "deleted" =>  $deleted
            );
        } else {
            throw new \Exception('Review id is not valid');
        }
    }

    public function getList() {
        $config = $this->getServiceLocator()->get('Config');
        $userId = $this->getUserSession()->user_id;
        if (!$userId) {
            throw new \Exception('Not a valid user');
        }
        $queryParams = $this->getRequest()->getQuery()->toArray();
        $friendId = $this->getQueryParams('friendid', false);
        if ($friendId) {
            $userId = $friendId;
        }
        $userModel = new User();
        $commonFucntions = new CommonFunctions ();
        $options = array('where' => array('id' => $userId));
        $userDetail = $userModel->getUserDetail($options);
        if ($userDetail) {
            $userInfo = $userDetail->getArrayCopy();
            $userInfo = array_intersect_key($userInfo, array_flip(array(
                'id',
                'user_name',
                'first_name',
                'last_name',
                'email',
                'display_pic_url',
            )));
            $data = $commonFucntions->checkProfileImageUrl(array(
                'display_pic_url' => $userInfo['display_pic_url'],
                'id' => $userId
            ));
            $userInfo['display_pic_url'] = $data['display_pic_url'];
        } else {
            $userInfo = array();
        }

        $joins = array();
        $joins [] = array(
            'name' => 'restaurants',
            'on' => 'restaurants.id = user_reviews.restaurant_id',
            'columns' => array(
                'restaurant_name',
                'inactive',
                'closed',
                'rest_code',
                'restaurant_primary_image'=>'restaurant_image_name'
            ),
            'type' => 'left'
        );
        $joins[] = array(
            'name' => array(
                'owner' => 'owner_response'
            ),
            'on' => 'owner.review_id=user_reviews.id',
            'columns' => array('owner_response'=>'response','restaurant_responded_on'=>'response_date'),
            'type' => 'left'
        );
        $order = $this->getQueryParams('sort');
        if (!preg_match('/(restaurant_name|rating|created_on|review_for)$/', $order)) {
            $order = false;
        }
        $limit = $this->getQueryParams('limit',SHOW_PER_PAGE);
        $page = $this->getQueryParams('page',1);
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
        $options = array(
            'columns' => array(
                'review_id' => 'id',
                'review_desc',
                'restaurant_id',
                'created_at' => 'created_on',
                'rating',
                'review_for',
                'status',                
            ),
            'joins' => $joins,
            'where' => array(
                'user_id' => $userId,
                'status' => array(0,1,2)
            ),
          
        );
        $userReviewModel = new UserReview ();
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $userReviewModel->find($options)->toArray();
        
        $reviewId = array();
        
        if (!empty($response)) {
            foreach ($response as $key => $value) {
                // $response [$key] ['created_on'] = StaticOptions::getFormattedDateTime($response [$key] ['created_on'], 'Y-m-d H:i:s', 'd M, Y');
                $reviewId [$key] = $value['review_id'];
                $response [$key]['type']='user';
                $response [$key]['review_for'] = $config['constants']['review_for'][$value['review_for']];
                // $response[$key]['created_on']=!$value ['created_on']?'':$commonFucntions->datetostring($value ['created_on']);
                if ($response[$key]['inactive'] == 1 || $response[$key]['closed'] == 1) {
                    $response[$key]['is_restaurant_exist'] = 0;
                } else {
                    $response[$key]['is_restaurant_exist'] = 1;
                }
                $response[$key]['rest_code']=  strtolower($value['rest_code']);
                $response [$key]['sort_date'] = date('Y-m-d H:i',strtotime($response [$key] ['created_at']));
                unset($response[$key]['inactive'], $response[$key]['closed']);
            }
        }
        //dummy data need to dynamic
        //$responses['reviews'] = $response;
       
        $userTip = new UserTip();
        $joins1 = array();
        $joins1 [] = array(
            'name' => 'restaurants',
            'on' => 'restaurants.id = user_tips.restaurant_id',
            'columns' => array(
                'restaurant_name',
                'is_restaurant_exist' => new \Zend\Db\Sql\Expression('if(inactive = 1 or closed = 1,0,1)'),
                'restaurant_primary_image'=>'restaurant_image_name',
                'rest_code'
            ),
            'type' => 'left'
        );
        $options = array(
          'columns' => array(
              'restaurant_id',
              'tip_id' => 'id',
              'tip',
              'created_at',
              'status'
          ),
          'where' => array(
              'user_id' => $userId,             
              'status' => array(0,1,2),
          ),
          'joins'=>$joins1,
        );
        $userTip->getDbTable()->setArrayObjectPrototype('ArrayObject');
        if ($userTip->find($options)->toArray()) {
            $tips = $userTip->find($options)->toArray();
            foreach($tips as $key => $tip){
                $tips[$key]['type'] = 'tip';
                $tips[$key]['sort_date'] = date('Y-m-d H:i',strtotime($tips [$key] ['created_at']));
                $tips[$key]['is_restaurant_exist'] = ($tip['is_restaurant_exist']==1)?intval(1):intval(0);
                $tips[$key]['rest_code'] = strtolower($tip['rest_code']);
            }
        }else{
            $tips = array();
        }
        
        $final1 = array_merge($response, $tips);
        $totalReview = count($final1);
        
        foreach ($final1 as $key => $val) {
            $sortDate[$key] = strtotime($val['sort_date']);           
        }
        if (!$order || $order == 'date' || $order == 'type') {
            if ($final1) {
                array_multisort($sortDate, SORT_DESC, $final1);
            }
        } elseif ($order == 'rating') {
            uasort($final1, array(
                $this,
                'rating_compare'
            ));
        }
        
        $final = array_slice($final1, $offset, $limit);
        //pr($final,true);
        $responses['reviews'] = $final;
        $responses['user_info'] = $userInfo;
        $responses['total_reviews'] = $totalReview;
        return $responses;
    }

    public function editReview($data) {
        $id = $data['review_id']; 
        $userId = $this->getUserSession()->getUserId();
        if (empty($data)) {
            throw new \Exception("Invalid Parameters", 400);
        }
        if (!$userId) {
            throw new \Exception('User Not Logged in');
        }
        if (!isset($data ['rating']) || $data ['rating'] == null) {
            throw new \Exception('Select Rating');
        }
        if (!isset($id) || $id == null) {
            throw new \Exception('Please send Review Id');
        }
        if (!isset($data ['review_for']) || $data ['review_for'] == null) {
            throw new \Exception('Please tell what are you reviewing');
        }
        if (!isset($data ['restaurant_id']) || $data ['restaurant_id'] == null) {
            throw new \Exception('Restaurant not found');
        }
        

        try {
            $review_id = $id;
            $dbtable = new UserReviewTable ();
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->beginTransaction();
            $selectedLocation = $this->getUserSession()->getUserDetail ( 'selected_location', array () );   
            $cityModel = new \Home\Model\City();//18848
            $cityId = isset($selectedLocation ['city_id'])?$selectedLocation ['city_id']:18848;
            $cityDetails = $cityModel->cityDetails($cityId);
            $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
                            'state_code' => $cityDetails [0] ['state_code']
                        ));
            $currentDateTime = $cityDateTime->format('Y-m-d H:i:s');
            $userReviewModel = new UserReview ();
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
            $value ['user_id'] = $userId;
            $value ['status'] = 0;
            $value['services'] = isset($data ['services']) ? $data ['services'] : 0;
            $value['noise_level'] = isset($data ['noise_level']) ? $data ['noise_level'] : 0;
            $value['come_back'] = isset($data ['come_back']) ? $data ['come_back'] : 0;
            $value ['created_on'] = $currentDateTime;
            
            $userReviewModel->abstractUpdate($value, array(
                'id' => $review_id
            ));
            
            $request = new Request ();
            $files = $request->getFiles ();
            $userReviewImage = new UserReviewImage();
            $userReviewImage->user_review_id=$review_id;
            $userReviewImage->deleteImage(); 
             if (isset($files) && !empty($files) && count($files)> 0) {                 
                $restaurantModel = new Restaurant();
                $restaurantDetailOption = array('columns' => array('rest_code'), 'where' => array('id' => $data ['restaurant_id']));
                $restCode = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray(); 
                $response = StaticOptions::uploadUserImages($files, APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . strtolower($restCode['rest_code']) . DS . 'reviews' . DS);
               
                if (!empty($response)) {                    
                    foreach($response as $key => $val){                       
                        $data1['user_review_id']= $review_id;                        
                        $data1['created_at'] = $currentDateTime; 
                        $data1['image_status']=0;
                        $data1['image_url'] = $val['path'];
                        $arr_img_path = explode('/', $val['path']);
                        $length = count($arr_img_path);
                        $data1['image'] = $arr_img_path [$length - 1];                 
                        $addImageResponse = $userReviewImage->insert($data1);
                        
                    }                  
                }
            }  
            
            if (isset($data ['menu']) && !empty($data ['menu'])) {
                $image = array();
                $item_ids = array();
                $loved_it = array();
                $userMenuReview = new UserMenuReview ();
                $singleMenu = array();
                
                foreach ($data ['menu'] as $single) {
                    $singleMenu ['image_name'] = '';
                    $singleMenu ['menu_id'] = $single ['item_id'];
                    $singleMenu ['liked'] = $single ['loved_it'];
             
                    $options = array(
                        'columns'=>array('id'),
                        'where'=>array(
                            'menu_id'=>$single ['item_id'],
                            'user_review_id'=>$review_id)
                        );
                    
                    $userMenuReview->getDbTable()->setArrayObjectPrototype('ArrayObject');
                    $rows = $userMenuReview->find($options)->toArray();
                    if(!empty($rows)){
                      $userMenuReview->user_review_id = $review_id;
                      $userMenuReview->menu_id = $single ['item_id'];
                      $userMenuReview->update($singleMenu);
                    }else{
                        $singleMenu ['user_review_id'] = $review_id;
                        $userMenuReview->insert($singleMenu);
                    }
                    
                }
            }
            
            /**
             * Send Mail TO Restaurant Owner
             */
            //$mail = $this->sendReviewMailToOwner($data ['restaurant_id'], $userId);
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->commit();
            $userFunctions = new UserFunctions();
            $userPoint = $userFunctions->userTotalPoint($userId);
            
            ########## Dine and More Point will awarded ####################
            $userFunctions->userId = $userId;
            $userFunctions->restaurantId = $data ['restaurant_id'];
            $userFunctions->activityDate = $currentDateTime;
            $awardsPoint = $userFunctions->dineAndMoreAwards("awardsreview");
            ##################################################
            
            if(isset($awardsPoint['points'])){
                $points = (int)$awardsPoint['points'];
            }else{
                $upoints = $userFunctions->getAllocatedPoints("i_ratereview");
                $points = $upoints['points'];
            }
            
            return array(
                'success' => 'true','point'=>(int)$points,'user_points'=>$userPoint
                );
        } catch (\Exception $e) {
            $dbtable->getWriteGateway()->getAdapter()->getDriver()->getConnection()->rollback();
            throw new \Exception("Something apparently went wrong. Review Not submitted.", 400);
        }
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
