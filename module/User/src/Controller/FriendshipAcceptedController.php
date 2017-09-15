<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\User;
use User\Model\UserFriendsInvitation;

class FriendshipAcceptedController extends AbstractRestfulController {
    public $referralUserId=false;
    public $loginUserEmail=false;
    public $loginUserId=false;
    public $currentDate = false;
    public $invitationInsertId = false;
    public function get($id) {
        $userFunctions = new UserFunctions();
        $status = $userFunctions->invitationAccepted($id);        
        return array('result'=>$status);
    }
    
    public function create($data){
        $session = $this->getUserSession();
        $userFunctions = new UserFunctions();
        $success = array("success"=>true);
       
        $referralCode = (isset($data['referralCode']) && !empty($data['referralCode']))?$data['referralCode']:false;
        
        if($session->isLoggedIn() && $referralCode){
           $user = new User();
           $referralDetails = $user->getReferralCodeDetails($referralCode);
           $this->referralUserId = $referralDetails['id'];
           $this->loginUserEmail = (isset($data['email'])&&!empty($data['email']))?$data['email']:$session->getUserDetail('email');
           $this->loginUserId = $session->getUserId();
           $userFriendInvitations = new UserFriendsInvitation();
           $isExistInvitation = $userFriendInvitations->getReffInvitatioExist($this->referralUserId,$this->loginUserEmail);
          
           
           if($isExistInvitation && $isExistInvitation[0]['invitation_status']== 1){
               return $success;          
           }elseif($isExistInvitation && $isExistInvitation[0]['invitation_status']== 0){
               $userFunctions->invitationAccepted($isExistInvitation[0]['id']);
               return $success;
           }else{
              
               $locationData = $session->getUserDetail('selected_location');  
               $this->currentDate = $userFunctions->userCityTimeZone($locationData);
               $date = strtotime($this->currentDate);
               $expiredOn = date('Y-m-d H:i:s',strtotime("+7 day", $date));
               
               $insertdata = array(
                'user_id' => $this->referralUserId,
                'email' => $this->loginUserEmail,
                'source' => 'munch',
                'created_on' => $this->currentDate,
                'token' => $this->getUserSession()->token,
                'expired_on' => $expiredOn,
                'status' => 1,
                'invitation_status'=>0
              );
            $this->invitationInsertId = $userFriendInvitations->createReffUserInvitation($insertdata);
            $userFunctions->invitationAccepted($this->invitationInsertId);
            
          }
          return $success;
        }
    }
}
