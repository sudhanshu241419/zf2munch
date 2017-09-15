<?php

namespace User\Controller;
use User\Model\UserReferrals;
use User\Model\UserOrder;
use User\Model\User;
use User\UserFunctions;

use MCommons\Controller\AbstractRestfulController;

class WebUserReferralController extends AbstractRestfulController {
    public $loyalityCode = false;
    public $isValidLoyalityCode = false;
    public $link = false;
    public $vendorNumber = false;
    public $restaurantName = false;
    public function getList() {
        $reqtype = $this->getQueryParams('reqtype', '');
        $response = array();
        switch ($reqtype) {
            case 'refcodeinfo':
                $ref_code = $this->getQueryParams('data', '');
                $this->loyalityCode = $this->getQueryParams('loyality_code',false);
                $response = $this->getRefCodeInfo($ref_code);               
                break;
            case 'referraldata':
                $response = $this->getRefCodeData();
                break;
            default:
                $response['status'] = 'FAIL';
                $response['result'] = array('error' => 'invalid request');
                break;
        }
        return $response;
    }
    
    /**
     * 
     * @param array $data POST DATA
     * @return 
     * @throws \Exception
     */
    public function create($data) {
        $response = array();
        $userDetails = array();
        $data['user_id'] = $this->getUserSession()->getUserId();
        //$data['user_id'] = 674; //ds.yadav.iitd@gmail.com
        $uDetails = $this->getUserDetails($data['user_id']); 
        $userDetails['email']=$uDetails['email'];
        $userDetails['first_name']=$uDetails['first_name']; 
        $userDetails['last_name']=$uDetails['last_name']; 
        $userDetails['referral_code']=$uDetails['referral_code'];
        //check reqtype and take appropriate action
        $reqtype = isset($data['reqtype'])? $data['reqtype'] : '';
        switch ($reqtype) {
            case 'send_ref_mail':                              
                $response = $this->sendReferralMails($data,$userDetails);
                break;
            case 'personalize':
                $personlize_str = isset($data['data'])? $data['data'] : '';// , seperated list of emails
                $response = $this->personalizeReferralLink($personlize_str);
                break;
            default:
                break;
        }
        
        return $response;
    }
    
        
    private function getRefCodeInfo($ref_code) {
        $response = array();
        $user = new \User\Model\User();
        $data = $user->getReferralCodeDetails($ref_code);
        if($this->loyalityCode && $data){
            $userFunctions = new UserFunctions();
            if($userFunctions->parseLoyaltyCode($this->loyalityCode)){
                $data['restaurant_id'] = $userFunctions->restaurantId;
                $data['restaurant_name']=$userFunctions->restaurant_name;
                $data['loyality_code'] = $this->loyalityCode;
            }else{
                $data['restaurant_id'] = "";
                $data['restaurant_name']="";
                $data['loyality_code'] = "";
            }
        }
        if (!$data) {
            $response['status'] = 'FAIL';
            $response['result'] = array();
            $response['error'] = 'invalid code';
        } else {
            $response['status'] = 'OK';
            $response['result'] = $data;
        }
        return $response;
    }
    
    private function getRefCodeData(){
        $user_id = $this->getUserSession()->getUserId();
        $refModel = new UserReferrals();
        $data = $refModel->getReferralData($user_id);
        
        $userFunctions = new UserFunctions();
        foreach($data as $i => $user){
            $data[$i]['display_pic_url'] = $userFunctions->findImageUrlNormal($user['display_pic_url'], $user['id'] );
        }
        return array('status' => 'OK', 'result' => $data);
    }
    
    /**
     * Returns response to POST reqtype=send_ref_mail
     * @param strin $emails comma separated list of email ids
     * @return boolean if emails were successfully sent
     */
    private function sendReferralMails($data,$userDetails){        
        $response = array('result' => false, 'status' => 'OK');
        $emails = isset($data['data'])? $data['data'] : '';// , seperated list of emails          
        $mail_ids = explode(',', $emails);
        $this->loyalityCode = isset($data['loyality_code'])?$data['loyality_code']:false; 
        $this->link = isset($data['referal_link'])?$data['referal_link']:false;
        $recepients = array();//store non-existing email ids
        $existingUsers = array();
        $user_model = new User();
        $userOrder = new \User\Model\UserOrder();
        $orderCount = 0;
        foreach($mail_ids as $mail_id){
            $email = strtolower(trim($mail_id));
            if($email != $userDetails['email']){
                $friendUserDetails = $user_model->existsUserWithEmail($email);  

    //            if(!empty($friendUserDetails)){
    //                $totalOrderCount = $userOrder->getTotalOrdersWithDinein($friendUserDetails[0]['id'],'I');
    //                $orderCount = $totalOrderCount[0]['total_order'];
    //                
    //            }

//                if(!empty($friendUserDetails)){ //if($orderCount > 0){
//                    $existingUsers[] = $email;
//                }elseif($userDetails['referral_code'] && empty($friendUserDetails)){
                    $recepients[] = $email;
//                }else{
//                    $existingUsers[] = $email;
//                }  
            }
        }
        if(!empty($recepients)){ 
            $response['recipients'] = $recepients;
            if($this->loyalityCode){
                $userFunctions = new UserFunctions();
                if(!$userFunctions->parseLoyaltyCode($this->loyalityCode)){           
                    return array("success" => false,"message"=>"Sorry we could not detect a valid code. Re-enter and try again.",'restaurant_name' => '','restaurant_id' => '',);
                }
                $this->vendorNumber = isset($userFunctions->vendorNumber)?$userFunctions->vendorNumber:false;
                $this->restaurantName = isset($userFunctions->restaurant_name)?$userFunctions->restaurant_name:false;
            } 
            
            $response['result'] = $this->sendRefMailsTo($recepients,$userDetails);
            
        }
        
        if(!empty($existingUsers)){
            $response['recipients'] = $existingUsers;
            $response['result'] = $this->sendFriendshipMails($existingUsers,$userDetails);
        }
        
        return $response;
    }
    
    /**
     * 
     * @param array $recepients email ids
     * @return boolean
     */
    private function sendRefMailsTo($recepients,$userDetails) { 
        $commonFunctions = new \MCommons\CommonFunctions();
        $referral_code = $userDetails['referral_code'];
        $restaurantName = "";
        
        if(ucfirst($this->loyalityCode)==MUNCHADO_DINE_MORE_CODE){
            $subject = "Join ".$userDetails['first_name']." at Munch Ado";
            $template = "join_at_munchado";
        }else{
            $restaurantName = $commonFunctions->modifyRestaurantName($this->restaurantName);
            $subject = "Join ".$userDetails['first_name']." in ".$restaurantName." Dine & More Rewards Program!";
            $template = "inviteemail";
        }
        $variables = array(
            'referral_code' => $referral_code,
            'title' => 'Munch Ado Referral Program',
            'loyality_code'=>$this->loyalityCode,
            'link'=>$this->link,
            'restaurantName'=>$restaurantName,
            'inviter'=>$userDetails['first_name'],
        );
        
        $emailData = array(
            'variables' => $variables,
            'subject' => $subject,
            'template' => $template,
            'layout' => 'email-layout/referral',
            'loyality_code'=>$this->loyalityCode
        );
        
        $userFunctions = new \User\UserFunctions();
        foreach ($recepients as $receiver){
            $emailData['receiver'] = array($receiver);
            $userFunctions->sendMails($emailData,$userDetails);
        }

        return true;
    }
    
    private function personalizeReferralLink($referral_ext){
        $response = array();
        $response['status'] = 'OK';
        $user_id = $this->getUserSession()->getUserId();
        if($user_id == 0){
            $response['status'] = 'FAIL';
            $response['error'] = 'invalid user';
            return $response;
        }
        
        $user = new User();
        $response['result'] = $user->setUserReferralExt($referral_ext, $user_id);
        return $response;
    }
    
    private function sendFriendshipMails($emailids,$userDetails){
        $sender = array();
        $userFunctions = new UserFunctions();
        $mailText = "";
        $session = $this->getUserSession();        
        $userEmail = $session->getUserDetail('email');
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $userId = $session->getUserId();        
                 
        if (!empty($userDetails) && $userDetails != null) {            
            $userName = (isset($userDetails['first_name']) && !empty($userDetails['first_name']))?$userDetails['first_name'].' '.$userDetails['last_name']:'';
            $userEmail = $userDetails['email'];
        }else{
            $userName = false;           
        }
       
        if(!$userName){
            $userEmail = explode("@", $userEmail);
            $userName = $userEmail[0];
        }
        
        $friendsMessage = (!empty($data['friendsMessage']))?$data['friendsMessage']:"Hey, you really need to check out MunchAdo.com. It's, hands down, the best way to order food online. Someone finally got everything right! It has a soft, smooth, sensual user interface and a off-beat sense of humor that's both charming and reassuring. Turns out, it's also a bit of a braggart!";
        $baseUrl = $this->getBaseUrl();
        foreach ($emailids as $email) {
            $invitation = $userFunctions->inviteFriends($userId, $userName, $currentDate, $email, $friendsMessage,$baseUrl,$userDetails);
        }     
        return $invitation;
    }
    
    public function getUserDetails($userId){
        $userModel = new User();
        $options = array(
            'columns' => array('first_name','email','referral_code','last_name'),
            'where' => array('users.id' => $userId),
          );
        $userDetails = $userModel->getUserDetail($options);
        return $userDetails;
    }
    public function getBaseUrl()
    {
        $uri = $this->getRequest()->getUri();
        return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    }

}
