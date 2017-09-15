<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\SmsOffer;

class SmsOfferController extends AbstractRestfulController {
	public function getList() {
		$smsOffer = new SmsOffer();
        $text = $this->getQueryParams('text', false);
        $str = explode(" ", $text);
        
        $userSmsData['user_mob_no'] = ltrim($this->getQueryParams('from', false), 1);
        if(isset($str[0]) && !empty($str[0])){
            if($userSmsData['user_mob_no']){
                $message = $smsOffer->getMessage($str[0],$str[1]);
                if($message){
                    $userSmsData['message'] = $message;                   
                    $sent = \MCommons\StaticOptions::sendSmsClickaTell($userSmsData, 0);
                    $success = ($sent==1)?true:false;                                  
                }else{
                    $message = "Not a valid code";
                    $success = false;
                }
                
            }else{
                $message = "Not a valid user mobile number";
                $success = false;
            }
        }else{
            $message = "Not a valid sms text";
            $success = false;
        }      
       
        return array("message" => $message,"success"=>$success);
        
        
	}
}

