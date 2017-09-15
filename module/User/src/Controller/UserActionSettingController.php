<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserActionSettings;

class UserActionSettingController extends AbstractRestfulController {

    public function create($data) {
        if (empty($data)) {
            throw new \Exception("Invalid Parameters", 400);
        } else {
            $userActionSettings = new UserActionSettings();
            $userFunctions = new \User\UserFunctions();
            $session = $this->getUserSession();
            $isLoggedIn = $session->isLoggedIn();
            
            if (!$isLoggedIn) {             
                throw new \Exception("Invalid User", 400); 
            }
                        
            $locationData = $session->getUserDetail ( 'selected_location', array () );
            $currentDate = $userFunctions->userCityTimeZone($locationData);
            $userActionSettings->user_id = $session->getUserId(); 
            $userActionSettings->order = isset($data['order'])?intval($data['order']):intval(0);           
            $userActionSettings->reservation = isset($data ['reservation']) ? intval($data ['reservation']) : intval(0);
            $userActionSettings->bookmarks = isset($data ['bookmarks']) ? intval($data ['bookmarks']) : intval(0);
            $userActionSettings->checkin = isset($data ['checkin']) ? intval($data ['checkin']) : intval(0);
            $userActionSettings->muncher_unlocked = isset($data ['muncher_unlocked']) ? intval($data ['muncher_unlocked']) : intval(0);
            $userActionSettings->upload_photo = isset($data['upload_photo']) ? intval($data ['upload_photo']) : intval(0);
            $userActionSettings->reviews = isset($data ['reviews']) ? intval($data ['reviews']) : intval(0); 
            $userActionSettings->tips = isset($data ['tips']) ? intval($data ['tips']) : intval(0);
            $userActionSettings->email_sent = isset($data ['email_sent']) ? intval($data ['email_sent']) : intval(0);
            $userActionSettings->notification_sent = isset($data ['notification_sent']) ? intval($data ['notification_sent']) : intval(0);
            $userActionSettings->sms_sent = isset($data ['sms_sent']) ? intval($data ['sms_sent']) : intval(0);
            $userActionSettings->created_at = $currentDate;
            $userActionSettings->updated_at = $currentDate; 
            
            $feedPrivacy = array(
                '1'=>$userActionSettings->order,
                '4'=>$userActionSettings->reservation,
                '7'=>$userActionSettings->reservation,
                '6'=>$userActionSettings->reservation,
                '9'=>$userActionSettings->reviews,
                '10'=>$userActionSettings->tips,
                '11'=>$userActionSettings->upload_photo,
                '12'=>$userActionSettings->bookmarks,
                '13'=>$userActionSettings->reviews,
                '16'=>$userActionSettings->bookmarks,
                '17'=>$userActionSettings->bookmarks,
                '20'=>$userActionSettings->reservation,
                '21'=>$userActionSettings->muncher_unlocked,
                '22'=>$userActionSettings->checkin,
                '24'=>$userActionSettings->checkin,
                '25'=>$userActionSettings->checkin,
                '26'=>$userActionSettings->checkin,
                '34'=>$userActionSettings->checkin,
                '35'=>$userActionSettings->checkin,
                '36'=>$userActionSettings->checkin,
                '37'=>$userActionSettings->muncher_unlocked,
                '38'=>$userActionSettings->muncher_unlocked,
                '39'=>$userActionSettings->muncher_unlocked,
                '40'=>$userActionSettings->muncher_unlocked,
                '41'=>$userActionSettings->muncher_unlocked,
                '42'=>$userActionSettings->muncher_unlocked,
                '43'=>$userActionSettings->muncher_unlocked,
                '44'=>$userActionSettings->muncher_unlocked,
                '45'=>$userActionSettings->muncher_unlocked,
                '51'=>$userActionSettings->bookmarks,
                '52'=>$userActionSettings->checkin);
            $existActionSetting = $userActionSettings->select(array('where'=>array('user_id'=>$userActionSettings->user_id)));
            $actionSettingId = false;          
            if (!empty($existActionSetting)) {
                $actionSettingId = $existActionSetting[0]['id'];                
            }
            try { 
                ###### UPDATE FEED ACCORDING TO PRIVACY SETTING ######
                $activityFeed = new \User\Model\ActivityFeed();
                foreach ($feedPrivacy as $key => $val) {
                    $data = array('privacy_status' => $val);
                    $where = array('feed_type_id' => $key, 'user_id' => $userActionSettings->user_id);
                    $activityFeed->updatePrivacyStatus($data, $where);
                }
                ######################################################

                if($actionSettingId){
                    $userActionSettings->id = intval($actionSettingId);
                   
                    $response = $userActionSettings->update();
                }else{
                    $response = $userActionSettings->insert();
                }
               
                }catch (\Exception $ex) {
                return $this->sendError(array(
                            'error' => $ex->getMessage()
                                ), $ex->getCode());
            }
            
            if($response){
                return $response;
            }else{
                throw new \Exception("Invalid detail", 400); 
            }
            
        }
    }

    function getList() {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $userActionSettings = new UserActionSettings();
        if(!$isLoggedIn){
           throw new \Exception('User unavailable', 400); 
        }
        $user_id = $session->getUserId();
        $response = $userActionSettings->select(array('where'=>array('user_id'=>$user_id)));
        if(isset($response[0])){     
            foreach($response[0] as $key => $val){
                if($key === 'created_at' || $key === 'updated_at'){
                    //$responseData[$key]=$val;
                }else{
                     $responseData[$key]=intval($val);
                }
            }
            
        }else{
            $responseData['id'] = intval(0);
            $responseData['user_id'] = intval($user_id);
            $responseData['order'] = intval(0);
            $responseData['reservation'] = intval(0);
            $responseData['bookmarks'] = intval(0);
            $responseData['checkin'] = intval(0);
            $responseData['muncher_unlocked'] = intval(0);
            $responseData['upload_photo'] = intval(0);
            $responseData['reviews'] = intval(0);
            $responseData['tips'] = intval(0);   
            $responseData['email_sent'] = intval(1);
            $responseData['sms_sent'] = intval(1);
            $responseData['notification_sent'] = intval(1);
            
        }
        return $responseData;
    }

   

}
