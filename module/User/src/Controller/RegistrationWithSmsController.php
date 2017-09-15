<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;

class RegistrationWithSmsController extends AbstractRestfulController {
    public $dineAndMoreCode = false;
    public $currentDate;
    
    public function getList() {
        $regStr = array('REG', 'REGS', 'ENROLL');
        $str = $this->getQueryParams('text', false);        
        $fromNumber = ltrim($this->getQueryParams('from', false), 1);
        $email = $this->getQueryParams('email',false);
        $isOffer = explode(" ", $str);
        $requestFor = strtoupper(substr($str,0,2));
        $type = $this->getQueryParams("type",false);
        if($type==="offer" && !in_array(strtoupper($isOffer[0]),$regStr) && $requestFor!="RE" && $requestFor!="EN"){
            return $this->smsOffer($str, $fromNumber, $email);
        }
        
        $toNumber = $this->getQueryParams('to', false);
        $app_id = $this->getQueryParams('api_id', false);
        $timestamp = $this->getQueryParams('timestamp', false);
        $userSmsData['user_mob_no'] = $fromNumber; //send $phoneNo without 1 Always;
        $resquedata = array(
            'app_id' => $app_id,
            'user_id' => '',
            'from_number' => $fromNumber,
            'to_number' => $toNumber,
            'message' => '',
            'date' => $timestamp,
            'loyalityCode' => '',
            'restaurant_id' => '',
            'restaurant_name' => '',
        );

        $userFunction = new \User\UserFunctions();
        if ($userFunction->parseLoyaltyRegistrationSms($str)) {
            if($userFunction->loyaltyReg === "ENROLL"){
                $data['type']="ENROLL";
                $data['dinecode'] = $userFunction->loyaltyCode;
                $data['emails'] = $userFunction->email;               
                $this->create($data);
                return array("success"=>true);
            }
            $restaurantName = $userFunction->restaurant_name;
            $restaurantId = $userFunction->restaurantId;
            $loyalityCode = $userFunction->loyaltyCode;
            if ($userFunction->userRegistrationWithSmsWeb()) {
                if ($userFunction->isRegisterUser) {
                    return $userFunction->smsForRegisterUser($resquedata, $userSmsData);
                } else {
                    return $userFunction->smsForNewUser($resquedata, $userSmsData);
                }
            }
        }
        
        //Send sms to user in the case of failuar if user already register with particular restaurant
        if($userFunction->isRegisterWithRestaurant==1){
            $commonFunctions = new \MCommons\CommonFunctions();
            $modifiedRestName = $commonFunctions->modifyRestaurantName($userFunction->restaurant_name);
            $userSmsData['message'] = sprintf(SMS_ERROR_REGISTER_ALREADY_WITH_RESTAURANT, $modifiedRestName);
            $resquedata['message'] = $userSmsData['message'];
            return $userFunction->smsFaluar($resquedata, $userSmsData);
        } 
        
        //Send sms to user in the case of failuar
        $userSmsData['message'] = SMS_ERROR_CODE."REG YourEmail YourCode";
        $resquedata['message'] = $userSmsData['message'];
        return $userFunction->smsFaluar($resquedata, $userSmsData);
    }
    
    public function create($data){
        $response = array('success'=>false);
        $this->dineAndMoreCode = (isset($data['dinecode']) && !empty($data['dinecode']))?$data['dinecode']:false;
        $userEmailsString = (isset($data['emails']) && !empty($data['emails']))?$data['emails']:false;
        $type = (isset($data['type']) && $data['type'] === "ENROLL")?true:false;
        if($this->dineAndMoreCode){
            $emails = explode(",", $userEmailsString);
            if($emails){
                $response = $this->serverRequestToUserRegistration($emails,$type);
            }
        }              
               
       
        return $response;
    }
    
    private function serverRequestToUserRegistration($emails,$type){
        $userFunctions = new \User\UserFunctions();
        $commonFunctions = new \MCommons\CommonFunctions();
        $userFunctions->loyaltyCode=$this->dineAndMoreCode;        
        $registerUserWithServer = '';
        $alReadyRegisterWithMunch = '';
        $successRegistration = '';
        $notValidEmail = '';
        $success = true;
        $enrollFailuarMessage = "One or more of the emails you provided were not valid. Please check that you entered the email(s) correctly.";
        foreach($emails as $key =>$email){
             if($commonFunctions->validateEmail($email)){
                 $userFunctions->email = trim($email);
                 $registrationSuccess = $userFunctions->serverRequestToUserRegistration();
                 
                 if(!$registrationSuccess){
                     if($type){
                        $this->sendSmsToServers($enrollFailuarMessage);
                        
                        break; 
                     }
                     $success = false;
                 }
        
                if($registrationSuccess && $userFunctions->isRegisterWithRestaurant == 1){  //User register with restaurant server
                    $registerUserWithServer .= $email.", ";                      
                }
        
                if($registrationSuccess && $userFunctions->isRegisterWithRestaurant == 0){
                    $alReadyRegisterWithMunch  .= $email.", ";
                }                        
            
                if($registrationSuccess && $userFunctions->requestByServerForNewUserRegistration==1){
                    $successRegistration .= $email.", ";
                }
             }else{
                if($type){                    
                    $this->sendSmsToServers($enrollFailuarMessage);                    
                    break;
                }
                $notValidEmail .=$email.", ";
             }
             
        }
        if($type){
            $this->sendEnrollSmsToServers("Thank you for enrolling your customer. You can track their status in your profile on Server.MunchAdo.com.");
        }else{
            $this->sendSmsToServers($registerUserWithServer,$successRegistration);
        }
        return array(
            'success'=>$success,
            'existuser_registerwithserver'=>!empty($registerUserWithServer)?substr($registerUserWithServer,0,-2):$registerUserWithServer,
            'existuser'=>!empty($alReadyRegisterWithMunch)?substr($alReadyRegisterWithMunch,0,-2):$alReadyRegisterWithMunch,
            'success_registration_user_server'=>!empty($successRegistration)?substr($successRegistration,0,-2):$successRegistration,
            'notValidEmail'=>!empty($notValidEmail)?substr($notValidEmail,0,-2):$notValidEmail
        );
    }
    
    public function sendEnrollSmsToServers($message){
        $serverPhone = $this->serverPhone();
        $userSmsData['user_mob_no'] = $serverPhone['phone'];  
        $userSmsData['message'] = $message;
        StaticOptions::sendSmsClickaTell($userSmsData,0);
    }
    
    public function sendSmsToServers($registerUserWithServer=false, $successRegistration=false){
     $serverPhone = $this->serverPhone();
     if($serverPhone){
         if(($registerUserWithServer)||($successRegistration)){
             $userSmsData =array();
             $userSmsData['user_mob_no'] = $serverPhone['phone'];  
             $userSmsData['message'] = "Thank you, we've received your customer's email and will begin the registration process. You can track the progress on http://staff.munchado.com/";
             StaticOptions::sendSmsClickaTell($userSmsData,0);
            }
        }
    }
    
    public function serverPhone(){
        $phone_no="";
        $server = new \Servers\Model\Servers();
        $options = array('columns'=>array('phone'), 'where'=> array('code'=> $this->dineAndMoreCode));
        $phone_no = $server->getServerDetail($options);
        return $phone_no;
    }
    
    public function smsOffer($text,$userNo,$userEmail){
        $smsOffer = new \User\Model\SmsOffer();       
        $str = explode(" ", $text);       
        
        if(isset($str[0]) && !empty($str[0])){
            $message = $smsOffer->getMessage($str[0],$str[1]);
            $restaurantDetails = $this->getRestaurantDetails($str[1]);
            $logo = ($restaurantDetails['restaurant_logo_name'])? WEB_URL . 'assets/'.strtolower($restaurantDetails['rest_code'])."/".$restaurantDetails['restaurant_logo_name']:false;
            
            $userCampion = new \User\Model\UserCampions();
            $userCampion->status = 0;
            if($message){
                $success = false;               
                if($userNo){       
                    $userSmsData['user_mob_no'] = $userNo;
                    $userSmsData['message'] = $message;                   
                    $sent = StaticOptions::sendSmsClickaTell($userSmsData, 0);
                    $success = ($sent==1)?true:false;  
                    $userCampion->status = ($sent==1)?1:0;
                    $userCampion->campion_code = $str[1];
                    $userCampion->destination = $userNo;                   
                    $userCampion->created_at = $this->currentDate;                    
                    $userCampion->addCampion();
                 }
                if($userEmail){
                    $data['toemail']= $userEmail;                    
                    $data['logo'] = $logo;
                    $data['message'] = $message;
                    $data['restaurantName'] = $restaurantDetails['restaurant_name'];
                    $this->sendSmsOfferMail($data);
                    $success = true;
                    $userCampion->status = 1;
                    $userCampion->campion_code = $str[1];
                    $userCampion->destination = $userEmail;                   
                    $userCampion->created_at = $this->currentDate;                    
                    $userCampion->addCampion();
                } 
                
                
            }else{
                    $message = "Not a valid code";
                    $success = false;
                }  
            
        }else{
            $message = "Not a valid sms text";
            $success = false;
        }      
       
        return array("message" => $message,"success"=>$success);       
        
	}
    
    public function getRestaurantDetails($code){
        $restaurant = new \Restaurant\Model\Restaurant();
        $restaurantId = substr($code, 2, strlen($code) - 1);
        $this->currentDate=  StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $restaurantId
                    ))->format('Y-m-d h:i');
        $options = array("columns"=>array('restaurant_name','restaurant_logo_name','rest_code'), "where"=>array('id'=>$restaurantId));
        return $restaurant->findRestaurant($options)->toArray();        
    }
    
    public function sendSmsOfferMail($data){
        $userFunctions = new \User\UserFunctions();
        $template = "sms_offer_mail";
        $subject = 'Offer';
        $layout = 'email-layout/default_new';
        $recievers = array(
            $data['toemail']
        );
        $variables = array(
            'logo' => $data['logo'],
            'message' => $data['message'],            
            'hostname' => WEB_URL,
            'restaurantName'=>$data['restaurantName']
        );
    
    
        $emailData = array(
            'receiver' => $recievers,
            'variables' => $variables,
            'subject' => $subject,
            'template' => $template,
            'layout' => $layout
        );
        $userFunctions->sendMails($emailData);
    }
 }
