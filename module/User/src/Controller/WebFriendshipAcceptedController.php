<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\User;

class WebFriendshipAcceptedController extends AbstractRestfulController {

    public function update($id,$data) {
        $userFunctions = new UserFunctions();
        $session = $this->getUserSession();
        $islogin = $session->isLoggedIn();
        $user = new User();
        $referralCode = (isset($data['referral_code']) && !empty($data['referral_code']))?$data['referral_code']:false;
        

        if($islogin && $referralCode){
            $options = array('columns'=>array('id'),'where'=>array('referral_code'=>$data['referral_code']));
            $userDetail = $user->getUser($options);
            $userId = $this->getUserSession()->getUserId();
            if($userId != $userDetail['id']){
                $userFunctions->saveReferredUserInviterData($userId,$data['referral_code']);
                return array('success'=>true);
            }else{
                return array('success'=>false);
            }
        }

        $id = isset($data['id'])?$data['id']:false;
        $email_id='';
        if(isset($data['is_mobile']) && $data['is_mobile']==1)
        {
         $email_id=trim($data['email_id']);   
        }

        if(!$id){ return array('success'=>false); }        
        
        $status = $userFunctions->invitationAccepted($id,'','',$email_id);
        return array('success'=>$status);
    }
    
    
     public function get($id) {
        $userFunctions = new UserFunctions();
        $session = $this->getUserSession();
        $islogin = $session->isLoggedIn();
        $status = $userFunctions->invitationAccepted($id);        
        $config = $this->getServiceLocator()->get('Config');
        $webUrl = PROTOCOL . $config ['constants'] ['web_url'];
        if ($status) {
            if ($islogin) {
                $response = $this->redirect()->toUrl($webUrl . DS . 'myfriends');
                $response->sendHeaders();
            } else {
                $response = $this->redirect()->toUrl($webUrl . DS . 'friend-invitation' . DS . $id);
                $response->sendHeaders();
            }
        } else {
            $response = $this->redirect()->toUrl($webUrl . DS . 'friend-invitation' . DS . $id);
            $response->sendHeaders();
        }
    }

}
