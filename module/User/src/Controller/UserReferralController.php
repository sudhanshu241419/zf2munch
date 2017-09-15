<?php

namespace User\Controller;
use User\Model\UserReferrals;
use User\Model\UserOrder;
use User\Model\User;
use User\UserFunctions;

use MCommons\Controller\AbstractRestfulController;

class UserReferralController extends AbstractRestfulController {
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
                $this->loyalityCode = $this->getQueryParams('dmcode',false);
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
        $data['user_id'] = $this->getUserSession()->getUserId();
        $uDetails = $this->getUserDetails($data['user_id']); 
        $userDetails['email']=$uDetails['email'];
        $userDetails['first_name']=$uDetails['first_name'];  
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
        $data = array();
        $user = new \User\Model\User();
        $data = $user->getReferralCodeDetails($ref_code);
        if (!$data) {           
            throw new \Exception ( "Referral records not found", 400 );           
        }
        if($this->loyalityCode){
            $userFunctions = new UserFunctions();
            if($userFunctions->parseLoyaltyCode($this->loyalityCode)){
                if(ucfirst($this->loyalityCode) == MUNCHADO_DINE_MORE_CODE){
                    $data['restaurant_id'] = 0;
                    $data['restaurant_name']=$userFunctions->restaurant_name;
                    $data['loyality_code'] = "";                               
                    $data['earn_point'] = (int)0;
                }else{
                    $data['restaurant_id'] = $userFunctions->restaurantId;
                    $data['restaurant_name']=$userFunctions->restaurant_name;
                    $data['loyality_code'] = $this->loyalityCode;
                    $points = $userFunctions->getAllocatedPoints("dinemorereferralinviter");                
                    $data['earn_point'] = (int)$points['points'];
                }
            }else{
                $data['restaurant_id'] = "";
                $data['restaurant_name']="";
                $data['loyality_code'] = "";
            }
        }
        return $data;      
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
        $response = array();
        $response['status'] = 'OK';
        $emails = isset($data['data'])? $data['data'] : '';// , seperated list of emails   
        $mail_ids = explode(',', $emails);
        $this->loyalityCode = isset($data['loyality_code'])?$data['loyality_code']:false;
        $this->link = isset($data['referal_link'])?$data['referal_link']:false;
        $recepients = array();//store non-existing email ids
        $user_model = new User();
        foreach($mail_ids as $mail_id){
            $email = strtolower(trim($mail_id));
            if($email != $userDetails['email']){
                $recepients[] = $email;
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
        } else {
            $response['result'] = false;
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
        
        if(ucfirst($this->loyalityCode) == MUNCHADO_DINE_MORE_CODE){
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
            $emailData['receiver'] = $receiver;
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
    
    public function getUserDetails($userId){
        $userModel = new User();
        $options = array(
            'columns' => array('first_name','email','referral_code'),
            'where' => array('users.id' => $userId),
          );
        $userDetails = $userModel->getUserDetail($options);
        return $userDetails;
    }

}
