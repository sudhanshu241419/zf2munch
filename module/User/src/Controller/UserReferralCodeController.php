<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;
use User\Model\UserOrder;

class UserReferralCodeController extends AbstractRestfulController {
    public function getList(){
        $userFunctions = new \User\UserFunctions();
        $userId = $this->getUserSession()->getUserId();
        $restaurantId = $this->getQueryParams('restid',false);
       
       
        $dineCodeMunch = '';
        if($restaurantId){
            $userFunctions->userId = $userId;
            $userFunctions->restaurantId = $restaurantId;
            if(!$userFunctions->isRegisterWithRestaurant($userId)){
                $restaurantDetails = $userFunctions->restaurantTaged($restaurantId);            
                $firstLetterRestaurantName = substr($restaurantDetails->restaurant_name, 0, 1);
                $dineCodeMunch = $firstLetterRestaurantName.$restaurantId."00";
            }
        }
        $referral_data = array();
        $referral_data['referral_code'] = '';
        $referral_data['previous_order']= false;
        
        if($userId){
            $referral_data['referral_code'] = $this->getUserReferralCode($userId);
            $referral_data['previous_order'] = $this->hasOrderPlaced($userId);
        }
        //$referral_data['dine_code_munch'] = $dineCodeMunch;
        $referral_data['dineMore_code'] = $dineCodeMunch;
        
        return $referral_data;
    }
    
    private function getUserReferralCode($user_id) {
        $user_model = new User();
        return $user_model->getUserReferralCode($user_id, 'mob');
    }
    
    private function hasOrderPlaced($user_id){
        $uo = new UserOrder();
        $count = $uo->getTotalPlacedOrder($user_id);
        if($count > 0){
            return true;
        }
        return false;
    }
}
