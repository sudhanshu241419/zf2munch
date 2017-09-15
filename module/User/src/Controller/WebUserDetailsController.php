<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;
use MCommons\StaticOptions;
use User\UserFunctions;
use MCommons\CommonFunctions;
use Bookmark\Model\FoodBookmark;
use Bookmark\Model\RestaurantBookmark;
use User\Model\UserNotification;
use User\Model\UserOrder;

// use MCommons\GeoLocation;
class WebUserDetailsController extends AbstractRestfulController {
    public $restaurantName;
    public $restaurantId;
    public $password;
    public function create($data) {
        $serverData = (isset($data['host_name']) && !empty($data['host_name']))?$data['host_name']:PROTOCOL.SITE_URL;
        $userDetail = array();
        $userloginModel = new User ();
        $useraccountModel = new \User\Model\UserAccount ();
        $userNotificationModel = new UserNotification ();
        $userFunctions = new UserFunctions();
        $session = $this->getUserSession();
        $captchaValue = $this->getUserSession()->getUserDetail('captcha-value');
        $referalid = (isset($data['referalid']) && !empty($data['referalid'])) ? $data['referalid'] : false;
        $loyalityCode = (isset($data['loyality_code']) && !empty($data['loyality_code'])) ? $data['loyality_code'] : "";
        if ($data['captcha'] == 'campaign' || $data['captcha'] == 'es') {
            
        } else if (!isset($data['captcha']) || $data['captcha'] != $captchaValue) {
            throw new \Exception('Captcha mismatch');
        }

        $open_page_type = (isset($data['open_page_type']) && !empty($data['open_page_type'])) ? $data['open_page_type'] : "";
        $refId = (isset($data['refId']) && !empty($data['refId'])) ? $data['refId'] : "";
        $locationData = $session->getUserDetail('selected_location');
        $cityId = isset($locationData ['city_id']) ? $locationData ['city_id'] : 18848;
        $userloginModel->city_id = $cityId;
        $currentDate = $userFunctions->userCityTimeZone($locationData);

        $useraccountModel->first_name = isset($data ['first_name']) ? $data ['first_name'] : false;
        $useraccountModel->last_name = (isset($data ['last_name'])) ? $data ['last_name'] : '';
        $userloginModel->email = isset($data ['email']) ? $data ['email'] : false;
        $domain = $userFunctions->checkDomain($userloginModel->email);
        if (isset($data ['password']) && $data ['password'] != null) {
            $userloginModel->password = md5($data ['password']);
            $this->password = $data['password'];
        }
        $userloginModel->newsletter_subscribtion = '1';
        $userloginModel->created_at = $currentDate;
        $userloginModel->update_at = $currentDate;
        $userloginModel->last_login = $currentDate;
        $userloginModel->order_msg_status = 0;
        $userloginModel->status = 1;
        $useraccountModel->user_source = isset($data['user_source']) ? $data['user_source'] : 'ws';
        $userloginModel->display_pic_url = 'noimage.jpg';
        $userloginModel->display_pic_url_normal = 'noimage.jpg';
        $userloginModel->display_pic_url_large = 'noimage.jpg';
        $userloginModel->bp_status = 0;
        $userloginModel->registration_subscription = '1';
        $accept_status = false;
        if (!$useraccountModel->first_name) {
            throw new \Exception("First name can not be empty.", 400);
        }

        if (!$userloginModel->email) {
            throw new \Exception("Email can not be empty.", 400);
        }

        if (!$userloginModel->password) {
            throw new \Exception("Password can not be empty.", 400);
        }

        if (!isset($data ['accept_toc']) || $data ['accept_toc'] != 1) {
            throw new \Exception("Required to accept term & condition.", 400);
        }
        ############## Loyality Program Registration code validation #############
        if ($loyalityCode) {
            if (!$userFunctions->parseLoyaltyCode($loyalityCode)) {
                throw new \Exception("Sorry we could not detect a valid code. Re-enter and try again.", 400);
                //return false;
            }
        }
        ##########################################################################
        $options = array(
            'where' => array(
                'email' => $userloginModel->email
            )
        );

        $userDetail = $userloginModel->getUserDetail($options);
        
        if (!empty($userDetail)) {
            throw new \Exception("Email is already registered.", 400);
        } else {
            $userloginModel->first_name = (isset($data ['first_name'])) ? $data ['first_name'] : '';
            $userloginModel->last_name = (isset($data ['last_name'])) ? $data ['last_name'] : '';
            $responseRegistration = $userloginModel->userRegistration();
            $session->setUserId($userloginModel->id);
            $dataSource = array(
                'user_source' => $useraccountModel->user_source
            );
            $session->setUserDetail($dataSource);
            $session->save();
            $useraccountModel->user_id = $responseRegistration['id'];
            $useraccountModel->userAccountRegistration();
            // give points to the user
            if (!$responseRegistration) {
                throw new \Exception("Registration failed.", 400);
            }

            $registrationSuccess = true;

            ######### Intigration of user reffer invitation ############
            if (isset($data['referral_code']) && !empty($data['referral_code'])) {
                $userFunctions->saveReferredUserInviterData($userloginModel->id, $data['referral_code']);
            }
            ############################################################

            $points = $userFunctions->getAllocatedPoints('normalRegister');            
            $message = "All life is a game. Here are 100 points to get you ahead of the game. Don't worry, it's not cheating.";
            $points['type'] = "normalRegister";
            $userFunctions->givePoints($points, $userloginModel->id, $message);


            ############### Assign Promocode ##################
            $userFunctions->assignPromocodeOnFirstRegistration($userloginModel->id, $userloginModel->first_name, $userloginModel->email);
            ###################################################

            if ($data['captcha'] == 'campaign' && ($useraccountModel->user_source != "sms" || $useraccountModel->user_source != "enrl")) {
                $userOrder = new UserOrder();
                $userOrder->email = $userloginModel->email;
                $data = array('user_id' => $userloginModel->id);
                $userOrder->updateMistryMealOrder($data);
            }

            $response = array_intersect_key($responseRegistration, array_flip(array(
                'id',
                'first_name',
                'last_name',
                'email'
            )));

            $config = $this->getServiceLocator()->get('Config');
            $webUrl = PROTOCOL . $config['constants']['web_url'];
            
            ########## Notification to user on first Registration ########
            $notificationMsg = 'Welcome to Munch Ado! From now on, we’ll be helping you get from hangry to satisfied.';
            $channel = "mymunchado_" . $userloginModel->id;
            $notificationArray = array(
                "msg" => $notificationMsg,
                "channel" => $channel,
                "userId" => $userloginModel->id,
                "type" => 'registration',
                "restaurantId" => '0',
                'curDate' => $currentDate
            );
            $userNotificationModel->createPubNubNotification($notificationArray);
            StaticOptions::pubnubPushNotification($notificationArray);

            ############## Loyality Program Registration #############   
            $point = 100;
            if ($loyalityCode) {
                $userFunctions->registerRestaurantServer();
                $template = "Welcome_To_Restaurant_Dine_More_Rewards_New_User_Password"; //500N
                $userFunctions->first_name = $useraccountModel->first_name;
                $userFunctions->email = $userloginModel->email;
                $userFunctions->userId = $userloginModel->id;
                $userFunctions->dineAndMoreAwards("awardsregistration"); 
                $userFunctions->loyaltyCode = $loyalityCode;
                $userFunctions->mailSmsRegistrationPassword($template);
                $point = 200;
               
            }
            
            ############## salesmanago Event ##################
            $this->restaurantName = ($userFunctions->restaurant_name)?$userFunctions->restaurant_name:$this->restaurantName;
            $this->restaurantId = ($userFunctions->restaurantId)?$userFunctions->restaurantId:$this->restaurantId;
            $salesData = [];
            $salesData['name'] = $userloginModel->first_name;
            $salesData['email'] = $userloginModel->email;
            $salesData['dine_more'] = ($userFunctions->loyaltyCode || $useraccountModel->user_source === "sms" || $useraccountModel->user_source === 'enrl') ? "Yes" : "No";
            $salesData['owner_email'] = 'no-reply@munchado.com';
            $salesData['restaurant_name'] = ($userFunctions->loyaltyCode || $useraccountModel->user_source === "sms" || $useraccountModel->user_source === 'enrl')?$this->restaurantName:"";
            $salesData ['restaurant_id'] = ($userFunctions->loyaltyCode || $useraccountModel->user_source === "sms"  || $useraccountModel->user_source === 'enrl')?$this->restaurantId:"";
            $salesData['tags'] = ($userFunctions->loyaltyCode || $useraccountModel->user_source === "sms"  || $useraccountModel->user_source === 'enrl')?array("Registration_form","Dine_and_More",$this->restaurantName):array("Registration_form");
            $salesData['contact_ext_event_type'] = "OTHER";
            $salesData['identifier'] = "register";
            $salesData['redeempoint'] = 0;
            if($useraccountModel->user_source === "sms"){
                $salesData['password']=(isset($this->password) && $this->password!==NULL)?$this->password:"";
            }else{
                $salesData['password'] ='';
            }
            if ($loyalityCode || $useraccountModel->user_source === "sms" || $useraccountModel->user_source === 'enrl') {                
                $salesData['point'] = 200;
                $salesData['totalpoint'] = 200;                
            } else {
                $salesData['point'] = 100;
                $salesData['totalpoint'] = (int) $userFunctions->userTotalPoint($userloginModel->id);
            }            
            $salesData['user_source']=$useraccountModel->user_source;
            
            //$userFunctions->createQueue($salesData,'Salesmanago');
            ###################################################
           
            
            
            
            
            ############## clevertap profile upload ##################
            if($useraccountModel->user_source=='ws' && $loyalityCode){
                    $restname = $userFunctions->restaurant_name;
                    $restid = $userFunctions->restaurantId;
                }else{
                    $restname = $this->restaurantName;
                    $restid = $this->restaurantId;
                }
            
            $clevertapData = array(
                "user_id"=>$userloginModel->id,
                "name"=>($userloginModel->last_name)?$userloginModel->first_name." ".$userloginModel->last_name:$userloginModel->first_name,
                "email"=>$userloginModel->email,
                "currentDate"=>$currentDate,
                "source"=> $useraccountModel->user_source,
                "loyalitycode"=>($loyalityCode)?$loyalityCode:false,
                "restname"=>$restname,
                "restid"=>$restid,
                "eventname"=>($loyalityCode)?"dine_and_more":"general",
                "host_name"=>(isset($data['host_name']) && !empty($data['host_name']))?$data['host_name']:PROTOCOL.SITE_URL                
            );
            if(isset($data['referral_code']) && !empty($data['referral_code'])){
                $clevertapData['refferralPoint'] = $userFunctions->referralPoint;                   
            }
            
            if($useraccountModel->user_source!="sms"){
                //$userFunctions->clevertapUploadProfile($clevertapData);
                $userFunctions->clevertapRegistrationEvent($clevertapData);
            }
            ##########################################################           
            
            $feed_name=$userloginModel->first_name.' '.$userloginModel->last_name;
            $feed = array(      
                      'user_id'=>$userloginModel->id,
                      'user_email'=>$userloginModel->email,
                      'user_name'=>ucfirst($feed_name)
                   );
            $replacementData = array('message'=>'test');
            $otherReplacementData = array('user_name'=>ucfirst($feed_name));
            
            $commonFunction = new \MCommons\CommonFunctions();   
            $activityFeed = $commonFunction->addActivityFeed($feed, 53, $replacementData, $otherReplacementData);
            
            if($useraccountModel->user_source != "sms"){
                if((!$loyalityCode || strtoupper($loyalityCode) === MUNCHADO_DINE_MORE_CODE) && ($serverData==PROTOCOL.SITE_URL)){
                    $template = 'user-registration';
                    $layout = 'email-layout/default_register';
                    $variables = array('username' => $userloginModel->first_name, 'hostname' => $webUrl);
                    $mailData = array('recievers' => $userloginModel->email, 'layout' => $layout, 'template' => $template, 'variables' => $variables);
                    $userFunctions->sendRegistrationEmail($mailData);
                }elseif(isset($data['host_name']) && PROTOCOL.SITE_URL != $data['host_name']){
                    $userFunctions->first_name = $useraccountModel->first_name;
                    $userFunctions->email = $userloginModel->email;
                    $userFunctions->userId = $userloginModel->id;                        
                    $template = 'registration_from_micro_site_with_dine_more_code';  
                    
                    $restaurantName = isset($data['restaurant_name'])?$data['restaurant_name']:"";
                    $layout = 'email-layout/ma_default';
                    $subject = "Welcome to ".$restaurantName."!";
                    $variables = array(
                        'username' => $userloginModel->first_name, 
                        'hostname' => $data['host_name'],
                        'restaurant_name'=>isset($data['restaurant_name'])?$data['restaurant_name']:"",
                        'restaurant_logo'=>isset($data['restaurant_logo'])?$data['restaurant_logo']:"",
                        'restaurant_address'=>isset($data['restaurant_address'])?$data['restaurant_address']:"",
                        'facebook_url'=>isset($data['facebook_url'])?$data['facebook_url']:"",
                        'twitter_url'=>isset($data['twitter_url'])?$data['twitter_url']:"",
                        'instagram_url'=>isset($data['instagram_url'])?$data['instagram_url']:"",
                        'restaurant_id'=>isset($data['restaurant_id'])?$data['restaurant_id']:"",
                        'loyalityCode'=>$loyalityCode,
                        'password'=>$data ['password']
                        );
                    $mailData = array('recievers' => $userloginModel->email, 'layout' => $layout, 'template' => $template, 'subject'=>$subject, 'variables' => $variables);
                    $userFunctions->sendRegistrationEmail($mailData);
                }
            }          
            
            if ($referalid) {
                $accept_status = $userFunctions->invitationAccepted($referalid, $userloginModel->email, true);
            }
            if (isset($userloginModel->email)) {
                $userFunctions->invitationReservationNewUser($userloginModel->email, $userloginModel->id, true);
            }
            $userFunctions->createSettings($userloginModel->id);
            ########### Associate user through deeplink ##############
            $userId = $userloginModel->id;
            $userEmail = $userloginModel->email;
            if (!empty($open_page_type)) {
                $userFunctions->associateInvitation($open_page_type, $refId, $userId, $userEmail);
            }
            
            return array('success' => $registrationSuccess,'user_id'=>$userId,'total_point'=>$point,'first_name'=>$userloginModel->first_name,'email'=>$userloginModel->email);
        }
    }

    public function getList() {
        $user_details = array();
        $user_model = new User ();
        $userDineAndMoreRestaurant['user_dine_restaurant'] = array();
        $session = $this->getUserSession();
        $selected_location = $session->getUserDetail('selected_location', false);
        $uSource = "MunchAdo";        
        
        $sessionData = $session->getUserDetail();
        if ($selected_location) {
            $user_details ['selected_location'] = $selected_location;
        } else {
            $user_details ['selected_location'] ['city_id'] = "";
            $user_details ['selected_location'] ['nbd_cities'] = "";
            $user_details ['selected_location'] ['state_id'] = "";
            $user_details ['selected_location'] ['city_name'] = "";
            $user_details ['selected_location'] ['state_name'] = "";
            $user_details ['selected_location'] ['state_code'] = "";
            $user_details ['selected_location'] ['country_name'] = "";
            $user_details ['selected_location'] ['country_code'] = "";
        }
        $user_details ['is_logged_in'] = $session->isLoggedIn() ? true : false;
        $user_data = array();
        $user_data['activity_bookmarks'] = false;
        if (!$user_details ['is_logged_in']) {
            $user_details ['name'] = "";
            $user_details ['email'] = "";
            $user_details ['last_login'] = "";
            $user_details ['first_name'] = "";
            $user_details ['last_name'] = "";
            $user_details ['phone'] = "";
            $user_details ['profile_pic_url'] = "";
            $user_data['activity_bookmarks'] = false;
            $user_data['dine_more'] = false;
        } else {
            if ($session->isLoggedIn()) {
                $user_details ['id'] = $session->getUserId();
                $restaurantServer = new \User\Model\RestaurantServer();
                $userDineRestaurant = $restaurantServer->userDineAndMoreRestaurant($user_details ['id']);                
                $commonFunctions = new CommonFunctions();                
                $commonFunctions->replaceParticulerKeyValueInArray($userDineRestaurant);
                $userDineAndMoreRestaurant['user_dine_restaurant'] = $userDineRestaurant;
                $joins = [];
                $joins [] = array(
                    'name' => array(
                        'ua' => 'user_account'
                    ),
                    'on' => 'users.id = ua.user_id',
                    'columns' => array(
                        'user_source',
                        'access_token',
                        'session_token'
                    ),
                    'type' => 'inner'
                    );
                $user_data = $user_model->getUserDetail(['columns' => array(
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'display_pic_url',
                        'display_pic_url_normal',
                        'display_pic_url_large',
                        'last_login',
                        'user_source',
                        'tutorial',
                        'phone',
                        'bp_status'
                    ),
                    'where' => array(
                        'users.id' => $user_details ['id']
                    ),
                    'joins'=>$joins
                ]);
                $userPoints = new \User\Model\UserPoint();
                $totalPoints = $userPoints->countUserPoints($user_details ['id']);
                $redeemPoint = $totalPoints[0]['redeemed_points'];
                $balancePoints = strval($totalPoints[0]['points'] - $redeemPoint);
                $foodBookmarkModel = new FoodBookmark();
                $restaurantBookmarked = $foodBookmarkModel->checkIfUserBookmarked();
                $restaurantBookmarkModel = new RestaurantBookmark();
                $userBookmarked = $restaurantBookmarkModel->checkIfUserBookmarked();
                if ($user_data) {
                    $commonFunctions = new CommonFunctions();
                    $restServer = $commonFunctions->isUserRegisterWithRestServer();
                    if($user_data['user_source']=="tw"){
                        $uSource = "Twitter";
                    }elseif($user_data['user_source']=="gp"){
                        $uSource="Google+";
                    }elseif($user_data['user_source']=="fb"){
                        $uSource = "Facebook";
                    }
                    $user_data['dine_more'] = ($restServer[0]['id'] > 0) ? true : false;
                    $user_data = $commonFunctions->checkProfileImageUrl($user_data);
                    $user_data = $user_data->getArrayCopy();
                    $user_data['points'] = $balancePoints;
                    $user_data['user_source'] = $uSource;
                    $user_data['tutorial'] = ($user_data['tutorial'] != null) ? unserialize($user_data['tutorial']) : '';
                    $user_data['activity_bookmarks'] = ($restaurantBookmarked || $userBookmarked) ? true : false;
                    ########### Associate user through deeplink ##############
                    $open_page_type = (isset($data['open_page_type']) && !empty($data['open_page_type'])) ? $data['open_page_type'] : "";
                    $refId = (isset($data['refId']) && !empty($data['refId'])) ? $data['refId'] : "";
                    $userId = $user_data['id'];
                    $userEmail = $user_data['email'];
                    if (!empty($open_page_type)) {
                        $userFunctions->associateInvitation($open_page_type, $refId, $userId, $userEmail);
                    }
                    ##########################################
                } else {
                    return array('error' => 'Invalid user');
                }
            }
        }
        $locationData = array();
        $selectedGeoLocData ['latitude'] = $session->getUserDetail('latitude', '');
        $selectedGeoLocData ['longitude'] = $session->getUserDetail('longitude', '');
        $selectedGeoLocData ['country'] = $session->getUserDetail('country', '');
        $selectedGeoLocData ['state'] = $session->getUserDetail('state', '');
        $selectedGeoLocData ['state_code'] = $session->getUserDetail('state_code', '');
        $selectedGeoLocData ['city'] = $session->getUserDetail('city', '');
        $selectedGeoLocData ['city_id'] = $session->getUserDetail('city_id', '');
        $selectedGeoLocData ['city_code'] = $session->getUserDetail('city_code', '');
        $selectedGeoLocData ['address'] = $session->getUserDetail('address', '');
        $social = array();
        $social_details = $this->getServiceLocator()->get('config');
        $social['social']['facebook'] = array('app_key' => isset($social_details['constants']['facebook']['app_key']) ? $social_details['constants']['facebook']['app_key'] : "");
        $social['social']['twitter'] = array('app_key' => isset($social_details['constants']['twitter']['key']) ? $social_details['constants']['twitter']['key'] : "");
        $social['social']['google_plus'] = array('app_key' => isset($social_details['constants']['google+']['app_id']) ? $social_details['constants']['google+']['app_id'] : "");
        $userPreviousAddress['previous_address'] = array();

        //added for referral program
        $referral_data = array();
        if ($session->isLoggedIn()) {
            $previousAdderss = $this->getServiceLocator()->get("User\Controller\WebUserAddressController");
            $addressList = $previousAdderss->getList();
            $userPreviousAddress['previous_address'] = $this->makeUserAddress($addressList, $selected_location);

            $referral_data['referral_code'] = $this->getUserReferralCode($user_details ['id']);
            $referral_data['previous_order'] = $this->hasOrderPlaced($user_details ['id']);
        }
        
        return array_merge($sessionData, $user_details, $user_data, $locationData, $social, $userPreviousAddress, $referral_data,$userDineAndMoreRestaurant);
    }

    public function makeUserAddress($address, $selected_location) {
        $addressList = array();
        $alist = '';
        if (!empty($address)) {
            $i = 0;
            foreach ($address as $key => $val) {
                if ($i < 5) {
                    if ($val['city_name'] === $selected_location['city_name'] && $val['google_addrres_type'] == 'street') {
                        if (!empty($val['street'])) {
                            $alist .= $val['street'] . ", ";
                        }
                        if (!empty($val['city'])) {
                            $alist .= $val['city'] . ", ";
                        }
                        if (!empty($val['state'])) {
                            $alist .= $val['state'] . " ";
                        }
                        if (!empty($val['zipcode'])) {
                            $alist .= $val['zipcode'] . ", ";
                        }
                        $alist = substr($alist, 0, -2);
                        $addressList[$i]['address'] = $alist . ", USA";
                        $addressList[$i]['lat'] = $val['latitude'];
                        $addressList[$i]['lng'] = $val['longitude'];
                        $addressList[$i]['addressType'] = $val['google_addrres_type'];

                        $i++;
                        $alist = '';
                    }
                }
            }
        }
        return $addressList;
    }

    private function getUserReferralCode($user_id) {
        $user_model = new User();
        return $user_model->getUserReferralCode($user_id);
    }

    private function hasOrderPlaced($user_id) {
        $uo = new UserOrder();
        $count = $uo->getTotalPlacedOrder($user_id);
        if ($count > 0) {
            return true;
        }
        return false;
    }

}
