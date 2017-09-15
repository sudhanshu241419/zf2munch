<?php
namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\OwnerResponse;
use User\Model\UserReview;
use User\Model\User;
use User\UserFunctions;
use Restaurant\Model\Restaurant;
use MCommons\StaticOptions;

class WebOwnersResponseController extends AbstractRestfulController{
    public function create($data) {
        if (! isset($data['review_id']) || empty($data['review_id'])) {
            throw new \Exception('review id dose not exists', 400);
        }
        if (! isset($data['response']) || empty($data['response'])) {
            throw new \Exception('response dose not exists', 400);
        }
        $ownerResponseModel = new OwnerResponse();
        $userReviewModel = new UserReview();
        $userModel = new User();
        $userFunction = new UserFunctions();
        $restaurantModel = new Restaurant();
        $ownerResponseModel->review_id = $data['review_id'];
        $ownerResponseModel->response = $data['response'];
        $ownerResponseModel->response_date = date("Y-m-d h:i:s");
        $result = $ownerResponseModel->addResponse();
        $userReviewModel->id = $data['review_id'];
        $userReviewModel->replied = 1;
        $res = $userReviewModel->updateReview();
        $config = $this->getServiceLocator ()->get ( 'Config' );
        $webUrl = PROTOCOL.$config ['constants'] ['web_url'];
        if (! empty($result)) {
            $response = array();
            $response['current_date'] = date("d M,Y", strtotime($result['response_date']));
            $response['success'] = true;
        }
        $options = array (
        		'columns' => array (
        				'user_id',
        		        'restaurant_id'
        		),
        		'where' => array('id'=>$data['review_id'])
        );
        $userReviewModel->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
        $data = $userReviewModel->find ( $options )->current();
        $sendMail = $userModel->checkUserForMail ( $data ['user_id'], 'review' );
        $options = array (
        		'columns' => array (
        				'email',
        		        'first_name'        
        		),
        		'where' => array('id'=>$data['user_id'])
        );
        $userData = $userModel->getUserDetail($options);
        $options = array (
        		'columns' => array (
        				'restaurant_name',
        		),
        		'where' => array('id'=>$data['restaurant_id'])
        );
        $restaurantDetails = $restaurantModel->findByRestaurantId($options);
        
        /**
          * Push To Pubnub For User
        */
            $userNotificationModel = new \User\Model\UserNotification();
                        
            $notificationMsg = $restaurantDetails->restaurant_name.' read and replied to your review. Eeep!';
            $channel = "mymunchado_" . $data['user_id'];
            $notificationArray = array(
                "msg" => $notificationMsg,
                "channel" => $channel,
                "userId" => $data['user_id'],
                "type" => 'reviews',
                "restaurantId" => $data['restaurant_id'],
                'curDate' => StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $data['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT),
                'restaurant_name'=>ucfirst($restaurantDetails->restaurant_name),
                'review_id'=>$ownerResponseModel->review_id
            );
            $notificationJsonArray = array('restaurant_id' => $data['restaurant_id'],'restaurant_name'=>ucfirst($restaurantDetails->restaurant_name),'review_id'=>$ownerResponseModel->review_id,'first_name'=>$userData ['first_name']);
            $response1 = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
            
            
            $notificationMsg = 'Your response has been received.';
            $channel = "dashboard_".$data['restaurant_id'];
            $notificationArray = array(
                "msg" => $notificationMsg,
                "channel" => $channel,
                "userId" => $data['user_id'],
                "type" => 'reviews',
                "restaurantId" => $data['restaurant_id'],
                'curDate' => StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $data['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT),
                'restaurant_name'=>ucfirst($restaurantDetails->restaurant_name)
            );
            $notificationJsonArray = array('restaurant_id' => $data['restaurant_id'],'restaurant_name'=>ucfirst($restaurantDetails->restaurant_name),'review_id'=>$ownerResponseModel->review_id,'first_name'=>$userData ['first_name']);
            $response1 = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
            
        
        if($sendMail==true){
            $sendModifyReservationMail = array (
            		'receiver' => array (
            				$userData ['email']
            		),
            		'variables' => array (
            				'name' => ucfirst($userData ['first_name']),
            				'restaurantName' => $restaurantDetails->restaurant_name,
            				'web_url' => $webUrl,
            		        'restaurantComment'=> $ownerResponseModel->response,
                            'restaurant_id'=>$data['restaurant_id'],
                            'urlRestName' => str_replace(" ", "-", strtolower(trim($restaurantDetails->restaurant_name))),
            				
            		),
            			
            		'subject' => $restaurantDetails->restaurant_name.' Responded to Your Review',
            		'template' => 'owners-response',
                    'layout'=>"email-layout/default_new"
            );
           
            $userFunction->sendMails ( $sendModifyReservationMail );
        }
        return $response;
    }
}