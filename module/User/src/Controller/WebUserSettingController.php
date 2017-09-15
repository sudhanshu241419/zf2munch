<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserSetting;
use User\Model\User;

class WebUserSettingController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $session = $this->getUserSession();
        $user_id = $session->getUserId();
        $userModel = new User ();
        $settingDb=  new \User\Model\UserActionSettings();
        $existActionSetting = current($settingDb->select(array('where'=>array('user_id'=>trim($user_id)))));
        
        $options = array(
            'columns' => array(
                'newsletter_subscribtion',
                'email'
            ),
            'where' => array(
                'id' => $user_id
            )
        );
        $userModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $userModel->find($options)->toArray();
        if($existActionSetting){
        $response[0]['email_sent']=$existActionSetting['email_sent'];
        $response[0]['notification_sent']=$existActionSetting['notification_sent'];
        $response[0]['sms_sent']=$existActionSetting['sms_sent'];
        }else{
        $response[0]['email_sent']=1;
        $response[0]['notification_sent']=1;
        $response[0]['sms_sent']=1;  
        }
        if ($response) {
            $response = $response[0];
        }
        return $response;
    }

    public function update($id, $data) {
        $session = $this->getUserSession();
        $userModel = new User();
        $userActionSettings=  new \User\Model\UserActionSettings();
        $userFunctions = new \User\UserFunctions();
        $locationData = $session->getUserDetail ( 'selected_location', array () );
        $currentDate = $userFunctions->userCityTimeZone($locationData);
            $userActionSettings ->user_id = $session->getUserId(); 
            $userActionSettings->order = 1;           
            $userActionSettings->reservation = 1;
            $userActionSettings->bookmarks = 1;
            $userActionSettings->checkin = 1;
            $userActionSettings->muncher_unlocked = 1;
            $userActionSettings->upload_photo = 1;
            $userActionSettings->reviews = 1; 
            $userActionSettings->tips =1;
            $userActionSettings->email_sent = isset($data ['email_sent']) ? intval($data ['email_sent']) : intval(0);
            $userActionSettings->notification_sent = isset($data ['notification_sent']) ? intval($data ['notification_sent']) : intval(0);
            $userActionSettings->sms_sent = isset($data ['sms_sent']) ? intval($data ['sms_sent']) : intval(0);
            $userActionSettings->created_at = $currentDate;
            $userActionSettings->updated_at = $currentDate;
            
            $existActionSetting = $userActionSettings->select(array('where'=>array('user_id'=>$userActionSettings->user_id)));
            $actionSettingId = false;          
            if (!empty($existActionSetting)) {
                $actionSettingId = $existActionSetting[0]['id'];                
            }
            if($actionSettingId){
                    $userActionSettings->id = intval($actionSettingId);
                    $response = $userActionSettings->update();
                }else{
                    $response = $userActionSettings->insert();
                }
               
        if($session->getUserId()){
            $userModel->id = $session->getUserId();
            
            $news_subscription=array('newsletter_subscribtion'=>$data['newsletter_subscribtion']);
                $userModel->update($news_subscription);
                $news_subscription['email']=$data['email'];
                $news_subscription['notification_sent']=$userActionSettings->notification_sent;
                $news_subscription['sms_sent']=$userActionSettings->sms_sent;
                $news_subscription['email_sent']=$userActionSettings->email_sent;
                return $news_subscription;
            
        } else {
            throw new \Exception('Something Apparently Went Wrong');
        }
       
    }

}
