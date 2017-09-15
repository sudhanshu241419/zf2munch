<?php

namespace Bookmark\Controller;

use MCommons\Controller\AbstractRestfulController;
use Bookmark\Model\FeedBookmark;
use MCommons\StaticOptions;
use User\UserFunctions;


class FeedBookmarkController extends AbstractRestfulController {

    public function create($data) {
        $bookmarkModel = new FeedBookmark();
        $session = $this->getUserSession();
        $userNotificationModel = new \User\Model\UserNotification();
        $bookmarkModel->user_id = $session->getUserId();
        $bookmarkModel->feed_id = $data ['feed_id'];        
        $bookmarkModel->type = $data ['type'];
        $userFunctions = new UserFunctions();
        $locationData = $session->getUserDetail ( 'selected_location', array () );
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $bookmarkModel->created_on = $currentDate;

        if (!$bookmarkModel->user_id) {
            throw new \Exception("Invalid user", 400);
        }        

        if (!$bookmarkModel->feed_id) {
            throw new \Exception("Invalid menu id", 400);
        }
        
        if($bookmarkModel->type !="li"){
            throw new \Exception("Invalid feed bookmark type", 400);
        }

        if (!$bookmarkModel->type) {
            throw new \Exception("Invalid feed bookmark type", 400);
        }
                 
         $userModel = new \User\Model\User();
         $userDetailOption = array('columns' => array('first_name','last_name'), 'where' => array('id' => $session->getUserId()));
         $userDetail = $userModel->getUser($userDetailOption);
        
        $bookmarkModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array('columns' => array('user_id','feed_id', 'id'),
            'where' => array(
                'feed_id' => $bookmarkModel->feed_id,
                'user_id' => $bookmarkModel->user_id,
                'type' => $bookmarkModel->type
            )
        );
        $isAlreadyBookedmark = $bookmarkModel->find($options)->toArray();
        

        if (!empty($isAlreadyBookedmark)) {
            $response = array();
            $response['feed_id'] = $bookmarkModel->feed_id;
            $response['user_id'] = $isAlreadyBookedmark[0]['user_id'];
            $response['type'] = $bookmarkModel->type;
            $response['first_name'] = $userDetail['first_name'];
            $response['last_name'] = $userDetail['last_name'];
            $bookmarkModel->id = $isAlreadyBookedmark[0]['id'];
            $rowEffected = $bookmarkModel->delete();           
            $response ['user_like_it'] = false;            
        } else {
            $bookmark = array();
            $bookmark['feed_id'] = $bookmarkModel->feed_id;
            $bookmark['user_id']= $bookmarkModel->user_id;
            $bookmark['created_on']= $bookmarkModel->created_on;
            $bookmark['type'] = $bookmarkModel->type;
            $inserted = $bookmarkModel->insert($bookmark);            
          
            if ($inserted) {             
                $response = array();
                $response['feed_id'] = $bookmarkModel->feed_id;
                $response['user_id'] = (string)$bookmarkModel->user_id;
                $response['type'] = $bookmarkModel->type;
                $response['first_name'] = $userDetail['first_name'];
                $response['last_name'] = $userDetail['last_name'];                
                $response ['user_like_it'] = true;
                
            } else {
                throw new \Exception("Unable to save feed bookmark", 400);
            }
        }
        $userFeed = new \User\Model\ActivityFeed();
        $userFeed->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $optionsFeed = array('column'=>array('user_id'),'where'=>array('id'=>$bookmarkModel->feed_id));
        $feedDetail = current($userFeed->find($optionsFeed)->toArray());
        if(trim($feedDetail ['user_id']) != trim($session->getUserId())){
        $notificationMsg =  ucfirst( $userDetail['first_name']).' loved your food activity';
                $channel = "mymunchado_" .trim($feedDetail ['user_id']);
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" =>trim($session->getUserId()),
                    "friend_iden_Id"=>trim($feedDetail ['user_id']),
                    "user_id" => $session->getUserId(),
                    "type" => 'feed',
                    "feed_id"=>$bookmarkModel->feed_id,
                    "username"=> ucfirst($userDetail['first_name']),
                    "restaurantId" => 0,
                    'curDate' => $currentDate,
                    "ftype"=>0
                    
                );
                
                $notificationJsonArray = array("ftype"=>0,'user_id'=>$session->getUserId(),"username"=> ucfirst($userDetail['first_name']),"feed_id"=>$bookmarkModel->feed_id);
                //$userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
               // $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
        }        
        $bookmarkCount = $bookmarkModel->getFeedBookmarkCountOfType($bookmarkModel->feed_id, $bookmarkModel->type);
        $totalBookMarkCountOfType = $bookmarkCount[0];
        $response['feed_like_count'] = (int) $totalBookMarkCountOfType['total_count'];
        return $response;
    }

}
