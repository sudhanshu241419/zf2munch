<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class WebReferralDineAndMoreController extends AbstractRestfulController {

    public function create($data) {  
        if($this->getUserSession()->isLoggedIn()){
            $userId = $this->getUserSession()->getUserId();
            $userFunctions = new \User\UserFunctions();
            $user = new \User\Model\User();            
            $options = array("columns"=>array("first_name","email"),"where"=>array("id"=>$userId));
            $userDetail = $user->getAUser($options)[0];
            if(isset($data['loyality_code']) && !empty($data['loyality_code'])){
                $referralCode = (isset($data['referral_code']) && !empty($data['referral_code']))?$data['referral_code']:false;
                $referralDineMore = array(
                    "loyality_code" =>$data['loyality_code'],
                    "referral_code" =>$referralCode,
                    "user_id" =>    $userId,
                    "email" =>  $userDetail ['email'],
                    "first_name"=> $userDetail ['first_name']
                );

                $userFunctions->existUserJoinDineMoreByReferral($referralDineMore,$userId);

                return array("success"=>true);
            }
        }else{
            return array("success"=>false);
        }
    }
}
