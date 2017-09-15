<?php

namespace User\Controller;
use MCommons\Controller\AbstractRestfulController;
use User\Model\UserTip;
use MCommons\StaticOptions;
use Restaurant\Model\Restaurant;

class UserTipController extends AbstractRestfulController {
	
	public function create($data) {
		$response = array ();			
		$session = $this->getUserSession ();
		$user_id = $session->getUserId ();
        $userFunctions = new \User\UserFunctions();
        $userNotificationModel = new \User\Model\UserNotification();
        if(!isset($data['restaurant_id']) || empty($data['restaurant_id'])){
            throw new \Exception ( "Restaurant id is required", 400 );        
        }
        if(!isset($data['tip'])){
            throw new \Exception ( "Tip is required", 400 );
        }
       
        $currentDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $data['restaurant_id']
                ))->format('Y-m-d H:i:s');
        if($user_id){
            $restaurantModel = new Restaurant();
            $restaurantDetailOption = array('columns' => array('rest_code','restaurant_name'), 'where' => array('id' => $data ['restaurant_id']));
            $restDetail = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray();
            
            $userModel = new \User\Model\User();
            $userDetailOption = array('columns' => array('first_name','last_name'), 'where' => array('id' => $session->getUserId()));
            $userDetail = $userModel->getUser($userDetailOption);
            $userName = (isset($userDetail['last_name']) && !empty($userDetail['last_name']))?$userDetail['first_name']." ".$userDetail['last_name']:$userDetail['first_name'];
            unset($data['token']);
            $data1['created_at'] = $currentDateTime;
            $data1['status'] = 2;
            $data1['user_id'] = $user_id;
            $data1['review_id'] = isset($data['review_id'])?$data['review_id']:0;
            $data1['restaurant_id'] = isset($data['restaurant_id'])?$data['restaurant_id']:0;
            $data1['tip'] = $data['tip'];
            
            $userTip = new UserTip();
            $insertedId = $userTip->insert($data1);
            if($insertedId){
                #   Add activity feed data   # 
                $commonFunctiion = new \MCommons\CommonFunctions();             
                $replacementData = array('restaurant_name'=>$restDetail['restaurant_name']);
                $otherReplacementData = array();
                $data['tip']=  str_replace(',', '%2C', $data['tip']);
                $feed = array(   
                    'restaurant_id'=>$data ['restaurant_id'],
                    'restaurant_name'=>$restDetail['restaurant_name'],
                    'user_name'=>ucfirst($userName), 
                    'tip'=>$data['tip'],
                    'img'=>array()
                     );
               // $activityFeed = $commonFunctiion->addActivityFeed($feed, 10, $replacementData, $otherReplacementData);
                
               $notificationMsg='You’re a good tipper…right? Review your tip for '.$restDetail['restaurant_name'].' and think about it.';
               $channel = "mymunchado_" .$user_id;
               $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $user_id,
                    "type" => 'tip',    
                    "restaurantId" => $data['restaurant_id'],        
                    'curDate' => $currentDateTime,
                    'restaurant_name'=>$restDetail['restaurant_name'],
                    'isfriend'=>0,
                    'username'=>ucfirst($userName),
                   'first_name'=>ucfirst($userDetail['first_name'])
                );
                $notificationJsonArray = array('first_name'=>ucfirst($userDetail['first_name']),'isfriend'=>0,'username'=>ucfirst($userName),"user_id" => $user_id,"restaurant_id" => $data['restaurant_id'],'restaurant_name'=>$restDetail['restaurant_name']);
                //$pub = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);   
                //$pubnub = StaticOptions::pubnubPushNotification($notificationArray); 
                             

                //$points = $userFunctions->getAllocatedPoints ( 'leaveTip' );
                //$message = 'Just a tip? Psht, here is just 3 points. Write a review and get another 5 points.';
                //$userFunctions->givePoints ( $points, $user_id, $message);             

               ###############################
               
               # Assign muncher #
                //$userFunctions->userAvatar('tip');
               ##################
                $points = $userFunctions->getAllocatedPoints("leaveTip");
                $totalPoints = (int) $userFunctions->userTotalPoint($user_id);
                return array('success'=>true,'user_points'=>$totalPoints,'point'=>(int)$points['points']);
        }else{
            throw new \Exception ( "User is not exist", 400 );
        }
        
	}
}
}