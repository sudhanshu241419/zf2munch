<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;

class LoyalityProgramCodeController extends AbstractRestfulController {

    public function create($data) {
        $loyalityCode = $data['loyality_code'];
        //$existReg = isset($data['existreg'])?$data['existreg']:false;
        $referralCode = (isset($data['referral_code'])&&!empty($data['referral_code']))?$data['referral_code']:false;
        $restaurant_id = isset($data['restaurant_id'])?$data['restaurant_id']:'';
        $user = StaticOptions::getUserSession()->getUserId();
        $userFunctions = new \User\UserFunctions();
        $userFunctions->userId = $user;        
        $userFunctions->existReg = $userFunctions->isRegisterWithAnyRestaurant();
        if ($userFunctions->parseLoyaltyCode($loyalityCode,$restaurant_id)) {
            if($user>0){
            if(!$userFunctions->checkExistingCodeWithUser()){
                return array("success" => false,"message"=>'You have already used code. Please try again.');
            }
            if($userFunctions->registerRestaurantServer()){
               $userFunctions->dineAndMoreAwardsLogin("awardsregistration",$userFunctions->existReg,$restaurant_id);
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
                
                ############# Feed Me ################
                $commonFunction = new \MCommons\CommonFunctions();
                $feed = array(
                    'user_id' => $userFunctions->userId,
                    'user_name' => ucfirst($userData['first_name']),
                    'restaurant_name'=>ucfirst($userFunctions->restaurant_name),
                    'restaurant_id'=>$userFunctions->restaurantId   
                );
                $replacementData = array('restaurant_name'=>ucfirst($userFunctions->restaurant_name));
                $otherReplacementData = array('restaurant_name'=>ucfirst($userFunctions->restaurant_name),'user_name' => ucfirst($userData['first_name']));
                $commonFunction->addActivityFeed($feed, 68, $replacementData, $otherReplacementData);
                $salesmanago = new \Salesmanago();
                $story = $salesmanago->restaurantStory($userFunctions->restaurantId);
                $userSession = $this->getUserSession();
                $locationData = $userSession->getUserDetail('selected_location');
                $currentDate = $userFunctions->userCityTimeZone($locationData);
                $salesData = [];
                $restaurantFunctions = new \Restaurant\RestaurantDetailsFunctions();
                $restuarantAddress = $restaurantFunctions->restaurantAddress($userFunctions->restaurantId);
                
                $salesData['owner_email'] = 'no-reply@munchado.com';
                $salesData['email'] = $userData['email'];    
               // $salesData['point']=100;
               // $salesData['totalpoint']=(int)$userFunctions->userTotalPoint($user);
               // $salesData['redeemed_point']=$userFunctions->redeemPoint;
                $salesData['dine_more'] = "Yes";
               // $salesData['identifier']="earned";       
               // $userFunctions->createQueue($salesData,'Salesmanago');
                //if($userFunctions->isRegisterWithAnyRestaurant() && $userFunctions->totalRegisterServer==1){
                    $salesData['tags'] = array("Dine_and_More",$userFunctions->restaurant_name);
                    $salesData['restaurant_name'] = $userFunctions->restaurant_name; 
                    $salesData['restaurant_id'] = $userFunctions->restaurantId; 
                    $salesData['story'] = $story;
                   // $salesData['email'] = $userData['email']; 
                   // $salesData['point']=(int)$userFunctions->userTotalPoint($user);
                   // $salesData['redeemed_point']=$userFunctions->redeemPoint;
                    $salesData['identifier']="custome"; 
               // $userFunctions->createQueue($salesData,'Salesmanago');
               // }else{
                    $salesData['value']=100;
                    $salesData['description'] = "Dine_and_More";
                    //$salesData['restaurant_name'] = $userFunctions->restaurant_name; 
                    //$salesData['restaurant_id'] = $userFunctions->restaurantId;                
                    $salesData['contact_ext_event_type'] = "OTHER";                
                    $salesData['identifier']="event";              
                    $salesData['location'] = $restuarantAddress;
                   // }
                //$userFunctions->createQueue($salesData,'Salesmanago');
                ###netcore#####
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
                $cleverTap['restaurant_story'] = "";
                $cleverTap['earned_points'] = "100";
                $cleverTap["eventname"]="dine_and_more";
                $userFunctions->createQueue($cleverTap, 'clevertap');
                ###################################################
                return array("success" => true,"message"=>'');
            }
            }else{
                return array("success" => true,"message"=>'');
            }
        } 
        return array("success" => false,"message"=>"Sorry we could not detect a valid code. Re-enter and try again.");
       
    }

}
