<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;
use User\UserFunctions;
use MCommons\StaticOptions;
use User\Model\UserAccount;
use User\Model\UserNotification;

class WebUpdateEmailController extends AbstractRestfulController {

    public function update($id, $data) {
        $serverData = $this->getRequest()->getServer()->toArray();
        $userFunctions = new UserFunctions();
        $regLoyalityCode = false;
        $userModelTwitter = $this->getUserSession()->getUserDetail('twitter_data');
        $userUserD = $this->getUserSession()->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($userUserD);
        $userNotificationModel = new UserNotification ();
        $hostName = (isset($data['host_name']) && !empty($data['host_name']))?$data['host_name']:PROTOCOL.SITE_URL;
        
        $restaurant_name = (isset($data['restaurant_name']) && !empty($data['restaurant_name']))?$data['restaurant_name']:"";
        $restaurant_logo = (isset($data['restaurant_logo']) && !empty($data['restaurant_logo']))?$data['restaurant_logo']:"";
        $restaurant_address = (isset($data['restaurant_address']) && !empty($data['restaurant_address']))?$data['restaurant_address']:"";
        $facebook_url= (isset($data['facebook_url']) && !empty($data['facebook_url']))?$data['facebook_url']:"";
        $twitter_url = (isset($data['twitter_url']) && !empty($data['twitter_url']))?$data['twitter_url']:"";
        $instagram_url = (isset($data['instagram_url'])&& !empty($data['instagram_url']))?$data['instagram_url']:"";
        $restaurant_id = (isset($data['restaurant_id'])&& !empty($data['restaurant_id']))?$data['restaurant_id']:"";
        
        if (!isset($data ['email']) || $data ['email'] == null) {
            throw new \Exception('Please provide email');
        }
        ############## Loyality Program Registration code validation #############
        //$data ['loyality_code']="B5828598";
        $referralCode = (isset($data['referral_code'])&&!empty($data['referral_code']))?$data['referral_code']:false;
        if (isset($data ['loyality_code']) && !empty($data ['loyality_code'])) {
            $regLoyalityCode = $userFunctions->parseLoyaltyCode($data ['loyality_code']);
            if (!$regLoyalityCode) {
                return array("success" => false, "message" => "Sorry we could not detect a valid code. Re-enter and try again.", 'restaurant_name' => '', 'restaurant_id' => '',);
            } else {
                if (!$userFunctions->checkExistingCodeWithUser()) {
                    return array("success" => false, "message" => 'You are already a member of ' . $userFunctions->restaurant_name . ' Dine & More program and have access to their exclusive specials!', 'restaurant_name' => $userFunctions->restaurant_name,
                        'restaurant_id' => $userFunctions->restaurantId);
                }
            }
        }
        ##########################################################

        $options = array(
            'where' => array(
                'email' => $data ['email']
            )
        );
        $userModel = new User ();
        $userAccountModel = new UserAccount();
        $userDetail = $userModel->getUserDetail($options);
        if (!empty($userDetail)) {
            throw new \Exception("Email is already registered.");
        } else {
            $referralCode = $userModelTwitter['referralCode'];
            $userAccountModel->user_name = $userModelTwitter['user_name'];
            $userModel->user_name = $userModelTwitter['user_name'];
            $userModel->first_name = $userModelTwitter['first_name'];
            $userAccountModel->first_name = $userModelTwitter['first_name'];
            $userModel->last_name = $userModelTwitter['last_name'];
            $userAccountModel->last_name = $userModelTwitter['last_name'];
            $userModel->email = $data ['email'];
            $userAccountModel->access_token = $userModelTwitter['access_token'];
            $userModel->display_pic_url = $userModelTwitter['display_pic_url'];
            $userModel->display_pic_url_normal = $userModelTwitter['display_pic_url_normal'];
            $userModel->display_pic_url_large = $userModelTwitter['display_pic_url_large'];
            $userAccountModel->session_token = $userModelTwitter['session_token'];
            $userAccountModel->user_source = $userModelTwitter['user_source'];
            $userModel->registration_subscription = 1;
            $userModel->newsletter_subscribtion = 1;
            $userModel->status = 1;
            $userModel->city_id = $userUserD['city_id'];
            $userModel->last_login = $currentDate;
            $userModel->created_at = $currentDate;
            $userModel->update_at = $currentDate;
            $userModel->userRegistration();
            $userAccountModel->user_id = $userModel->id;
            $userAccountModel->userAccountRegistration();
            $referalid = (isset($data['referalid']) && !empty($data['referalid'])) ? $data['referalid'] : false;
            $open_page_type = (isset($data['open_page_type']) && !empty($data['open_page_type'])) ? $data['open_page_type'] : "";
            $refId = (isset($data['refId']) && !empty($data['refId'])) ? $data['refId'] : "";

            $points = $userFunctions->getAllocatedPoints('socialRegister');
            $message = 'All life is a game. Here are 100 points to get you ahead of the game. Don\'t worry, it\'s not cheating.';
            $userFunctions->givePoints($points, $userModel->id, $message);
           ############## salesmanago Event ##################
            $salesData = [];
            $salesData['name'] = $userModel->first_name;
            $salesData['email'] = $userModel->email;
            $salesData['dine_more'] = ($userFunctions->loyaltyCode) ? "Yes" : "No";
            $salesData['owner_email'] = 'no-reply@munchado.com';
            $salesData['restaurant_name'] = ($userFunctions->loyaltyCode)?$userFunctions->restaurant_name:"";
            $salesData ['restaurant_id'] = ($userFunctions->loyaltyCode)?$userFunctions->restaurantId:"";
            $salesData['tags'] = ($userFunctions->loyaltyCode)?array("Registration_form","Dine_and_More",$userFunctions->restaurant_name):array("Registration_form");
            $salesData['contact_ext_event_type'] = "OTHER";
            //23/12/2016 changes
            $salesData['password'] ='';
            $salesData['user_source']="ws";
            //end 23/12/2016 changes
            
            $salesData['identifier'] = "register";
            $salesData['redeempoint'] = 0;
            if ($regLoyalityCode) {                
                $salesData['point'] = 200;
                $salesData['totalpoint'] = (int) $userFunctions->userTotalPoint($userModel->id);
            } else {
                $salesData['point'] = 100;
                $salesData['totalpoint'] = (int) $userFunctions->userTotalPoint($userModel->id);
            } 
            $salesData['user_source'] = $userAccountModel->user_source;
            //$userFunctions->createQueue($salesData, 'Salesmanago');
            ###################################################
            //$userModel->update($emailToUpdate);
            $session = $this->getUserSession();
            $data = array(
                'email' => $data ['email']
            );
            $session->setUserDetail($data);
            $session->setUserId($userModel->id);
            $session->save();
            $config = $this->getServiceLocator()->get('Config');
            $webUrl = PROTOCOL . $config['constants']['web_url'];
            ############## Loyality Program Registration #############
            if ($regLoyalityCode) {
                $userFunctions->userId = $userModel->id;
                $userFunctions->first_name = $userModel->first_name;
                $userFunctions->email = $data ['email'];
                $userFunctions->registerRestaurantServer();
                $template = "Welcome_To_Restaurant_Dine_More_Rewards_New_User_Password"; //500N
                $userFunctions->loyaltyCode = $regLoyalityCode;
                $userFunctions->mailSmsRegistrationPassword($template);
                $userFunctions->dineAndMoreAwards("awardsregistration");
            }
            ##########################################################
            ########## Notification to user on first Registration ########
            $notificationMsg = 'Welcome to Munch Ado! From now on, we’ll be helping you get from hangry to satisfied.';
            $channel = "mymunchado_" . $userModel->id;
            $notificationArray = array(
                "msg" => $notificationMsg,
                "channel" => $channel,
                "userId" => $userModel->id,
                "type" => 'registration',
                "restaurantId" => '0',
                'curDate' => $currentDate
            );
            $response = $userNotificationModel->createPubNubNotification($notificationArray);
            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);


            
             $feed_name=$userModel->first_name.' '.$userModel->last_name;
            $feed = array(      
                      'user_id'=>$userModel->id,
                      'user_email'=>$userModel->email,
                      'user_name'=>ucfirst($feed_name)
                   );
             $replacementData = array('message'=>'test');
            $otherReplacementData = array('user_name'=>ucfirst($feed_name));
            
            $commonFunction = new \MCommons\CommonFunctions();   
            $activityFeed = $commonFunction->addActivityFeed($feed, 53, $replacementData, $otherReplacementData);


            ######## Send Mail to user for registration #########
            $options = array(
                'where' => array(
                    'id' => $userModel->id
                )
            );
            $userName = $userModel->getUserDetail($options);
            ############### Assign Promocode ##################
            $assignPromocode = $userFunctions->assignPromocodeOnFirstRegistration($userModel->id, $userName['user_name'], $userName['email']);

            $message = 'Welcome Friend!';
            $sender = NOTIFICATION_SENDER_EMAIL;
            if((!$regLoyalityCode || strtoupper($regLoyalityCode) === MUNCHADO_DINE_MORE_CODE) && ($hostName==PROTOCOL.SITE_URL)){
                $template = 'user-registration';
                $layout = 'email-layout/default_register';
                $variables = array(
                    'username' => $userName['user_name'],
                    'hostname' => $webUrl,
                        //'promocodeAmount'=>$assignPromocode['discount'],
                        //'endDate'=>$assignPromocode['endDate']
                );
                $data = array('recievers' => $userName['email'], 'layout' => $layout, 'template' => $template, 'variables' => $variables);
                $userFunctions->sendRegistrationEmail($data);
            }elseif(PROTOCOL.SITE_URL != $hostName){
                    $template = 'registration_from_micro_site';
                    if($regLoyalityCode) { 
                        $userFunctions->userId = $userModel->id;
                        $userFunctions->first_name = $userModel->first_name;
                        $userFunctions->email = $data ['email'];
                        $userFunctions->registerRestaurantServer(); 
                        $userFunctions->loyaltyCode = $regLoyalityCode;                
                        $userFunctions->dineAndMoreAwards("awardsregistration");
                        $template = 'registration_from_micro_site_with_dine_more_code';  
                    }
                    $layout = 'email-layout/ma_default';
                    $variables = array(
                        'username' => $userModel->first_name, 
                        'hostname' => $hostName,
                        'restaurant_name'=>$restaurant_name,
                        'restaurant_logo'=>$restaurant_logo,
                        'restaurant_address'=>$restaurant_address,
                        'facebook_url'=>$facebook_url,
                        'twitter_url'=>$twitter_url,
                        'instagram_url'=>$instagram_url,
                        'restaurant_id'=>$restaurant_id,
                        'loyalityCode'=>$regLoyalityCode,
                        
                        );
                    $mailData = array('recievers' => $data ['email'], 'layout' => $layout, 'template' => $template, 'variables' => $variables);
                    $userFunctions->sendRegistrationEmail($mailData);
                }
            $userFunctions->createSettings($userModel->id);

            #######End of send mail ########
            if ($referalid) {
                $userFunctions->invitationAccepted($referalid, $userName['email'], true);
            }
            if (isset($userName['email'])) {
                $userFunctions->invitationReservationNewUser($userName['email'], $userModel->id, true);
            }
            ########### Associate user through deeplink ##############
            ######### Intigration of user reffer invitation ############
            if ($referralCode && !empty($referralCode)) {
                $userFunctions->saveReferredUserInviterData($userModel->id, $referralCode);                
            }
            ############################################################
            ############## Clever tap upload profile ##################
            $clevertapData = array(
                    "user_id"=>$userModel->id,
                    "name"=>($userModel->last_name)?$userModel->first_name." ".$userModel->last_name:$userModel->first_name,
                    "email"=>$userModel->email,
                    "currentDate"=>$currentDate,
                    "source"=> 'tw',
                    "loyalitycode"=>($regLoyalityCode)?$regLoyalityCode:false,
                    "restname"=>($regLoyalityCode) ? $userFunctions->restaurant_name : '',
                    "restid"=>($regLoyalityCode) ? $userFunctions->restaurantId : '',
                    "eventname"=>($regLoyalityCode)?"dine_and_more":"general",
                    "host_name"=> $hostName
                );
            if($referralCode && !empty($referralCode)){
                     $clevertapData['refferralPoint'] = $userFunctions->referralPoint;  
                }
            //$userFunctions->clevertapUploadProfile($clevertapData); 
            $userFunctions->clevertapRegistrationEvent($clevertapData);          
            ###################################################
            $userId = $userModel->id;
            $userEmail = $userModel->email;
            if (!empty($open_page_type)) {
                $userFunctions->associateInvitation($open_page_type, $refId, $userId, $userEmail);
            }
            ##########################################
            return array(
                'success' => true
            );
        }
    }

}
