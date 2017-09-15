<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\FeedComment;
use MCommons\StaticOptions;

class FeedCommentController extends AbstractRestfulController {
	public function create($data) {		
		try {
			if (! empty ( $data )) {				
                    $session = $this->getUserSession ();
                    $isLoggedIn = $session->isLoggedIn ();
                    $userFunctions = new \User\UserFunctions();
                    $userNotificationModel = new \User\Model\UserNotification();
                    if ($isLoggedIn) {
                        $data ['user_id'] = $session->getUserId ();
                    } else {
                        throw new \Exception ( "User unavailable", 400 );
                    }

                    if (! isset ( $data ['comment'] )) {
                        throw new \Exception ( "Comment is required", 400 );
                    }
                    if (! isset ( $data ['feed_id'] )) {
                        throw new \Exception ( "Feed detail is required", 400 );
                    }
                    $feedComment = new FeedComment();
                    unset($data['token']);
                    $locationData = $session->getUserDetail ( 'selected_location', array () );
                    $currentDateTime = $userFunctions->userCityTimeZone($locationData);                     
                    $data['created_on'] = $currentDateTime;
                    $data['comment']=  json_encode($data ['comment']);
                    if($feedComment->insert($data)){
                        $userFeed = new \User\Model\ActivityFeed();
                        $userFeed->getDbTable()->setArrayObjectPrototype('ArrayObject');
                        $optionsFeed = array('column'=>array('user_id'),'where'=>array('id'=>$data['feed_id']));
                        $feedDetail = current($userFeed->find($optionsFeed)->toArray());
                        $fId=$data['feed_id'];
                        unset($data['feed_id']);
                        $user = new \User\Model\User();
                        $commonFucntions = new \MCommons\CommonFunctions();
                        $user->getDbTable()->setArrayObjectPrototype('ArrayObject');
                        $options = array('column'=>array(),'where'=>array('id'=>$data ['user_id']));
                        $userDetail = $user->find($options)->toArray();
                        $data['first_name'] = $userDetail[0]['first_name'];
                        $data['last_name']= $userDetail[0]['last_name'];
                        $data['display_pic_url']=$commonFucntions->checkProfileImageUrl(array(
                                    'display_pic_url' => $userDetail[0]['display_pic_url'],
                                    'id' => $data ['user_id']
                                    ))['display_pic_url']; 
                if(trim($feedDetail ['user_id']) != trim($data ['user_id'])){        
                $notificationMsg =  ucfirst( $userDetail[0]['first_name']).' commented on your food activity';
                $channel = "mymunchado_" . $feedDetail ['user_id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "friend_iden_Id" => $feedDetail ['user_id'],
                    "user_id" => $data ['user_id'],
                    "feed_id"=>$fId,
                    "type" => 'feed',
                    "restaurantId" => 0,
                    "username"=> ucfirst( $userDetail[0]['first_name']),
                    "ftype"=>1,
                    'curDate' => $currentDateTime,
                    
                );
                $notificationJsonArray = array("ftype"=>1,'user_id'=>$data ['user_id'],"username"=> ucfirst( $userDetail[0]['first_name']),"feed_id"=>$fId);
                //$response = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                //$pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                }    
                      return $data;
                    }else{
                        throw new \Exception ( 'Invalid details', 400 );
                    }
                } else {
					throw new \Exception ( 'Invalid details', 400 );
				}			
		} catch ( \Exception $ex ) {
			return $this->sendError ( array (
					'error' => $ex->getMessage () 
			), $ex->getCode () );
		}
	}
	public function get($id) {
		$session = $this->getUserSession ();
		$isLoggedIn = $session->isLoggedIn ();
		if ($isLoggedIn) {
			$user_id = $session->getUserId ();
		} else {
			throw new \Exception ( "User unavailable", 400 );
		}
		$joins = array();
       
        $joins[] = array(
            'name' => array(
                'aft' => 'activity_feed_type'
            ),
            'on' => 'aft.id = activity_feed.feed_type_id',
            'columns' => array(
                'feed_type'                         
            ),
            'type' => 'left'
        );
        $joins[]=array(
            'name' => array(
                'fc' => 'feed_comment'
            ),
            'on' => 'fc.feed_id = activity_feed.id',
            'columns' => array(
                'comment', 
                'created_on'
            ),
            'type' => 'left'
        );
        $joins[] = array(
            'name' => array(
                'u' => 'users'
            ),
            'on' => 'u.id = fc.user_id',
            'columns' => array(
                'comment_user_id'=>'id',
                'first_name',
                'last_name',
                'display_pic_url',
            ),
            'type' => 'left'
        );
         $joins[] = array(
            'name' => array(
                'fu' => 'users'
            ),
            'on' => 'fu.id = activity_feed.user_id',
            'columns' => array(
                'fdisplay_pic_url'=>'display_pic_url',
            ),
            'type' => 'left'
        );
             
		$options = array(
            'columns' => array(
                'id',
                'feed', 
                'user_id',
                'added_date_time',
            ),
            'where' => array(
                'activity_feed.id' => $id,
              ),
            'joins' => $joins
        );
        $feed = new \User\Model\ActivityFeed();
        $feed->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = array();
        if ($feed->find($options)->toArray()) {
            $response = $feed->find($options)->toArray();
        }
        $feedComment = array();
        if(!empty($response)){
            $commonFucntions = new \MCommons\CommonFunctions();
            $i=0;
            foreach($response as $key => $val){
                $feedComment['feed']['id']=$val['id'];                
                $feedComment['feed']['user_id']=$val['user_id'];
                $feedComment['feed']['added_date_time'] = $val['added_date_time'];
                $feedComment['feed']['display_pic_url'] = $commonFucntions->checkProfileImageUrl(array(
                                    'display_pic_url' => $val['fdisplay_pic_url'],
                                    'id' => $val ['user_id']
                                    ))['display_pic_url'];
                $feedComment['feed']['feed_type']=$val['feed_type'];
                $feedGet=explode('{',$val['feed']);
                $feedGetForReview=explode('{',$val['feed']);
                $feedGet=explode(',',$feedGet[1]);
                $checkMess='';
                $checkReviewMessage='';
                $checkTipMessage='';
                $checkCaptionMessage='';
                foreach($feedGet as $val2){
                   $feedGet=explode(':',$val2); 
                   if(in_array('"checkinmessage"', $feedGet)){
                   $checkMess=rtrim($feedGet[1], '"');  
                   $checkMess=ltrim($checkMess, '"');  
                   }
                   if(in_array('"tip"', $feedGet)){
                   $checkTipMessage=rtrim($feedGet[1], '"');  
                   $checkTipMessage=ltrim($checkTipMessage, '"');    
                   }
                   if(in_array('"caption"', $feedGet)){
                   $checkCaptionMessage=rtrim($feedGet[1], '"');  
                   $checkCaptionMessage=ltrim($checkCaptionMessage, '"');   
                   }
                   if(in_array('"review"', $feedGet)){
                       $feedGetReview=explode(',',$feedGetForReview[2]); 
                       foreach($feedGetReview as $reviewVal){
                           list($k,$v)=explode(':',$reviewVal); 
                           if($k=='"review_desc"'){
                               $checkReviewMessage=rtrim($v, '"');  
                               $checkReviewMessage=ltrim($checkReviewMessage, '"');
                               $checkReviewMessage=rtrim($checkReviewMessage, '}');
                           }
                       }
                   }
                }
                $feedComment['feed']['feedinfo'] = json_decode($val['feed'],true);
                if(key_exists('checkinmessage', $feedComment['feed']['feedinfo'])){
                    unset($feedComment['feed']['feedinfo']['checkinmessage']);
                }
                if(isset($feedComment['feed']['feedinfo']['review']) && count($feedComment['feed']['feedinfo']['review'])>0){
                    unset($feedComment['feed']['feedinfo']['review']['review_desc']);
                    $feedComment['feed']['feedinfo']['review']['review_desc']=$checkReviewMessage;
                }
                if($checkTipMessage!=''){
                $feedComment['feed']['feedinfo']['tip']=$checkTipMessage;    
                }
                if($checkCaptionMessage!=''){
                $feedComment['feed']['feedinfo']['caption']=$checkCaptionMessage;    
                }
                $feedComment['feed']['feedinfo']['checkinmessage']=$checkMess;
                if(isset($val ['user_id']) && !empty($val ['comment_user_id'])){
                $feedComment['user'][$i]['first_name']=$val['first_name'];
                $feedComment['user'][$i]['last_name']=$val['last_name'];
                $feedComment['user'][$i]['display_pic_url']=$commonFucntions->checkProfileImageUrl(array(
                                    'display_pic_url' => $val['display_pic_url'],
                                    'id' => $val ['comment_user_id']
                                    ))['display_pic_url']; 
                $feedComment['user'][$i]['comment'] = $val['comment'];
                $feedComment['user'][$i]['created_on'] = $val['created_on'];
                }else{
                    $feedComment['user'] = array();
                }
                $i++;
            }
            return $feedComment;
        }else{
            return $feedComment;
        }
		
	}	
}