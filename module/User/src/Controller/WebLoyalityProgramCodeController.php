<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class WebLoyalityProgramCodeController extends AbstractRestfulController {

    public function create($data) {
        $hostName = (isset($data['host_name']) && !empty($data['host_name']))?$data['host_name']:PROTOCOL.SITE_URL;
        $loyalityCode = $data['loyality_code'];
        $referralCode = (isset($data['referral_code'])&&!empty($data['referral_code']))?$data['referral_code']:false;
        //$existReg = isset($data['existreg'])?$data['existreg']:false;
        $restaurant_id =isset($data['restaurant_id'])?$data['restaurant_id']:'';
        $session = $this->getUserSession();
        $user = $session->getUserId();
        $userFunctions = new \User\UserFunctions();
        $userFunctions->userId = $user;
        $userFunctions->existReg = $userFunctions->isRegisterWithAnyRestaurant();
        $userFunctions->host_name = $hostName;
        $userFunctions->restaurant_logo = isset($data['restaurant_logo'])?$data['restaurant_logo']:"";
        $userFunctions->restaurant_address = isset($data['restaurant_address'])?$data['restaurant_address']:"";
        $userFunctions->facebook_url = isset($data['facebook_url'])?$data['facebook_url']:"";
        $userFunctions->twitter_url = isset($data['twitter_url'])?$data['twitter_url']:"";
        $userFunctions->instagram_url = isset($data['instagram_url'])?$data['instagram_url']:"";        
        
        $varParseLoyaltyCode=$userFunctions->parseLoyaltyCode($loyalityCode,$restaurant_id);
        if ($varParseLoyaltyCode) {
            if(!$userFunctions->checkExistingCodeWithUser()){
               return array("success" => false,"message"=>'You are already a member of '.$userFunctions->restaurant_name.' Dine & More program and have access to their exclusive specials!','restaurant_name' => $userFunctions->restaurant_name,
            'restaurant_id' => $userFunctions->restaurantId);
            }
            if($userFunctions->registerRestaurantServer()){                  
                $userFunctions->dineAndMoreAwardsLogin("awardsregistration",$userFunctions->existReg);
                if($referralCode){
                    $userReferrals = new \User\Model\UserReferrals();
                    $userReferrals->user_id = $user;
                    $options = array('restaurant_id'=>$userFunctions->restaurantId);
                    $userReferrals->updateReferralRestaurant($options);
                }               
                ############## salesmanago Event ##################
                $userModel = new \User\Model\User();
                $userData = $userModel->getUserDetail(
                        array('column' => array('first_name','email'),
                        'where' => array(
                            'id' => $userFunctions->userId
                        )
                ));
                
                $userSession = $this->getUserSession();
                $locationData = $userSession->getUserDetail('selected_location');
                $currentDate = $userFunctions->userCityTimeZone($locationData);
                
                $salesData = [];
                $salesmanago = new \Salesmanago();
                $story = $salesmanago->restaurantStory($userFunctions->restaurantId);
                $restaurantFunctions = new \Restaurant\RestaurantDetailsFunctions();
                $restuarantAddress = $restaurantFunctions->restaurantAddress($userFunctions->restaurantId);
                $salesData['owner_email'] = 'no-reply@munchado.com';
                $salesData['email'] = $userData['email'];    
                //$salesData['point']=100;
                //$salesData['totalpoint']=(int)$userFunctions->userTotalPoint($user);
                //$salesData['redeemed_point']=$userFunctions->redeemPoint;
                $salesData['dine_more'] = "Yes";
                //$salesData['identifier']="earned";       
                //$userFunctions->createQueue($salesData,'Salesmanago');
                //if($userFunctions->isRegisterWithAnyRestaurant() && $userFunctions->totalRegisterServer==1){                    
                    $salesData['tags'] = array("Dine_and_More",$userFunctions->restaurant_name);
                    $salesData['restaurant_name'] = $userFunctions->restaurant_name; 
                    $salesData['restaurant_id'] = $userFunctions->restaurantId;
                    $salesData['story'] = $story;
                    //$salesData['email'] = $userData['email']; 
                    //$salesData['point']=(int)$userFunctions->userTotalPoint($user);
                    //$salesData['redeemed_point']=$userFunctions->redeemPoint;
                    $salesData['identifier']="custome"; 
                //$userFunctions->createQueue($salesData,'Salesmanago');
                //}else{
                    $salesData['value']=100;
                    $salesData['description'] = "Dine_and_More";
                    //$salesData['restaurant_name'] = $userFunctions->restaurant_name; 
                    //$salesData['restaurant_id'] = $userFunctions->restaurantId; 
                    //$salesData['email'] = $userData['email'];
                    $salesData['contact_ext_event_type'] = "OTHER";                
                    $salesData['identifier']="event";              
                    $salesData['location'] = $restuarantAddress;
                    $salesData['story'] = $story;
                    //$salesData['redeemed_point']=$userFunctions->redeemPoint;
                    //$salesData['tags'] = array("Dine_and_More",$userFunctions->restaurant_name);                    
                //}
                //$userFunctions->createQueue($salesData,'Salesmanago');
               
               ##########################################
                $restDetails = $userFunctions->getRestOrderFeatures($userFunctions->restaurantId);
                $cleverTap = [];
                $cleverTap['event'] = 1;
                $cleverTap['user_id']=$userFunctions->userId;
                $cleverTap['identity'] = $userData['email'];
                $cleverTap['registration_date'] = $currentDate;
                $cleverTap['restaurant_name'] = $userFunctions->restaurant_name;
                $cleverTap['restaurant_id'] = $userFunctions->restaurantId;
                $cleverTap['is_register'] = "yes";
                $cleverTap['delivery_enabled'] = $restDetails['delivery'];
                $cleverTap['takeout_enabled'] = $restDetails['takeout'];
                $cleverTap['reservation_enabled'] = $restDetails['reservations'];                
                $cleverTap['restaurant_story'] = $this->restaurantStory($userFunctions->restaurantId);
                $cleverTap['earned_points'] = "100";
                $cleverTap["eventname"]="dine_and_more";
                $userFunctions->createQueue($cleverTap, 'clevertap');
               ###################################################
                return array("success" => true,"message"=>'','restaurant_name' => $userFunctions->restaurant_name,'restaurant_id' => $userFunctions->restaurantId);
            }
        } 
         return array("success" => false,"message"=>"Sorry we could not detect a valid code. Re-enter and try again.",'restaurant_name' => '','restaurant_id' => '',);
       
    }
    
    public function restaurantStory($restaurantId) {
        $storyModel = new \Restaurant\Model\Story();
        $options = array('columns' => array('id', 'atmosphere', 'neighborhood', 'restaurant_history', 'chef_story', 'cuisine'), 'where' => array("restaurant_id" => $restaurantId), 'limit' => 1);
        $story = $storyModel->findStory($options)->toArray();
        if (!empty($story[0]['restaurant_history'])) {
            $restaurantStory = $story[0]['restaurant_history'];
        } elseif (!empty($story[0]['cuisine'])) {
            $restaurantStory = $story[0]['cuisine'];
        } elseif (!empty($story[0]['neighborhood'])) {
            $restaurantStory = $story[0]['neighborhood'];
        } elseif (!empty($story[0]['chef_story'])) {
            $restaurantStory = $story[0]['chef_story'];
        } elseif (!empty($story[0]['atmosphere'])) {
            $restaurantStory = $story[0]['atmosphere'];
        } else {
            $restaurantStory = "";
        }
        return $restaurantStory;
    }

}
