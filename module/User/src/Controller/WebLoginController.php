<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;
use MCommons\StaticOptions;
use Facebook as FB;
use TwitterOAuth;
use User\Form\LoginForm;
use User\FormFilter\LoginFormFilter;
use User\FormErrorFunctions;
use Google_Client;
use User\UserFunctions;
use User\Model\UserAccount;
use User\Model\UserNotification;
use Zend\Json\Json;

class WebLoginController extends AbstractRestfulController {

    public $googleclient;
    public $googleplus;
    private $referalId;
    public $referralCode;
    public $open_page_type;
    public $refId;
    public $loyalityCode;
    public $ak;
    public $host_name;
    
    public static $config = array(
        'adapter' => 'Zend\Http\Client\Adapter\Curl',
        'curloptions' => array(
            CURLOPT_FOLLOWLOCATION => true
        )
    );
    
    public function initGoogle() {
        $config = $this->getServiceLocator()->get('Config');
        $this->googleclient = new Google_Client ();
        $this->googleclient->setApplicationName('Munchado');
        $this->googleclient->setScopes(array(
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/plus.me'
        ));
        $this->googleclient->setClientId($config['constants']['google+']['client_id']);
        $this->googleclient->setClientSecret($config['constants']['google+']['client_secret']);
        $this->googleclient->setRedirectUri(PROTOCOL . $config['constants']['google+']['redirect_uri']);
        $this->googleclient->setDeveloperKey($config['constants']['google+']['developer_key']);
        $this->googleclient->setApprovalPrompt('auto');
        $this->googleplus = new \Google_Service_Plus($this->googleclient);
        //$this->googleplus = new Google_PlusService($this->googleclient);
    }

    public function create($data) {
        $userDetail = array();
        $userloginModel = new User ();
        $userFunctions = new UserFunctions();
        $session = $this->getUserSession();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $loyalityCode = (isset($data['loyality_code']) && !empty($data['loyality_code'])) ? $data['loyality_code'] : false;
        $referralCode = (isset($data['referral_code']) && !empty($data['referral_code'])) ? $data['referral_code'] : false;
        $form = new LoginForm ();
        $formfilter = new LoginFormFilter ();
        $form->setData(array(
            'email' => $data ['email'],
            'password' => $data ['password']
        ));

        $form->setInputFilter($formfilter->getInputFilter());
        if ($form->isValid()) {
            $data = $form->getData();
            $userloginModel->email = $data ['email'];
            $userloginModel->password = md5(trim($data ['password']));
            $userDetail = $userloginModel->getUserDetail(array(
                'columns' => array(
                    'id',
                    'email',
                    'password',
                    'status',
                    'created_at',
                    'first_name'
                ),
                'where' => array(
                    'email' => $userloginModel->email,
                //'user_source' => 'ws'
                )
            ));
            //pr($userDetail,true);
            if (!$userDetail) {
                throw new \Exception('Something Went Wrong!');
            }
            if ($userDetail ['password'] != $userloginModel->password) {
                throw new \Exception('Invalid Password');
            }
            if ($userDetail ['status'] != 1) {
                throw new \Exception("Not allowed to login, contact to administrator.", 500);
            }
            $response = array(
                "id" => $userDetail ['id'],
                "first_name" => isset($userDetail ['first_name']) ? $userDetail ['first_name'] : '',
                "last_name" => isset($userDetail ['last_name']) ? $userDetail ['last_name'] : '',
                "email" => $userDetail ['email']
            );

            //$data['loyality_code']= "U5902100";
            //$data['referral_code'] = "36a16a";

            if ($loyalityCode && $referralCode) {
                $referralDineMore = array(
                    "loyality_code" => $loyalityCode,
                    "referral_code" => $referralCode,
                    "user_id" => $userDetail ['id'],
                    "email" => $userDetail ['email'],
                    "first_name" => $userDetail ['first_name']
                );

                $userFunctions->existUserJoinDineMoreByReferral($referralDineMore, $userDetail ['id']);
            }

            $userloginModel->id = $userDetail ['id'];
            $data = array(
                'last_login' => $currentDate
            );
            $userloginModel->update($data);

            $session->setUserId($userDetail ['id']);
            $data = array(
                'email' => $userDetail ['email'],
                'created_at' => $userDetail ['created_at'],
                'user_source' => 'ws'
            );
            $session->setUserDetail($data);
            $session->save();
            $userFunctions = new UserFunctions();
            ########### Associate user through deeplink ##############
            $open_page_type = (isset($data['open_page_type']) && !empty($data['open_page_type'])) ? $data['open_page_type'] : "";
            $refId = (isset($data['refId']) && !empty($data['refId'])) ? $data['refId'] : "";
            $userId = $userloginModel->id;
            $userEmail = $userloginModel->email;
            if (!empty($open_page_type)) {
                $userFunctions->associateInvitation($open_page_type, $refId, $userId, $userEmail);
            }
            ##########################################
            return $response;
        } else {
            $functions = new FormErrorFunctions ();
            $error = $functions->getLoginFormError($form->getMessages());
            throw new \Exception($error);
        }
    }

    public function get($param = '') {
        $this->referalId = $this->getQueryParams('referalid');
        $this->referralCode = $this->getQueryParams('referral_code', false);
        $this->loyalityCode = $this->getQueryParams('loyality_code', false);
        $this->open_page_type = $this->getQueryParams('open_page_type', false);
        $this->refId = $this->getQueryParams('refId', false);
        $this->host_name = $this->getQueryParams('host_name',PROTOCOL.SITE_URL);

        if ($param != '') {
            if ($param == 'fb') {
                $this->fbLogin();
            } elseif ($param == 'fbauthenticate') {
                return $this->fbAuthenticate();
            } elseif ($param == 'twitter') {
                $this->twitterLogin();
            } elseif ($param == 'twitterauthenticate') {
                return $this->twitterAuthenticate();
            } elseif ($param == 'google') {
                $this->initGoogle();
                return $this->googleLogin();
            } elseif ($param == 'googleauthenticate') {
                $this->initGoogle();
                return $this->googleAuthenticate();
            }
        }
    }

    private function fbLogin() {
        $userdetails = $this->getUserSession();
        $userdetails->setUserDetail('referral_code', $this->referralCode);
        $userdetails->setUserDetail('loyality_code', $this->loyalityCode);
        $userdetails->setUserDetail('open_page_type', $this->open_page_type);
        $userdetails->setUserDetail('refId', $this->refId);
        $userdetails->setUserDetail('host_name',$this->host_name);
        $userdetails->save();
        $config = $this->getServiceLocator()->get('Config');

        $userdetails = $this->getUserSession();
        $ruri = base64_encode($this->getQueryParams("return_url"));
        
        ################ Facebook new SDK #############
        session_start();
        $fb = new \Facebook\Facebook([
            'app_id' => $config['constants']['facebook']['app_key'],
            'app_secret' => $config['constants']['facebook']['app_secret'],
            'default_graph_version' => $config['constants']['facebook']['default_graph_version'],
        ]);

        $helper = $fb->getRedirectLoginHelper();

        $permissions = ['email']; // Optional permissions
        $loginUrl = $helper->getLoginUrl($this->getBaseUrl() . '/wapi/user/login/fbauthenticate?token=' . $userdetails->token . '&ruri=' . $ruri, $permissions);
        //pr($loginUrl,1);
        $response = $this->redirect()->toUrl($loginUrl);
        ###############################################

        $response->sendHeaders();
    }

    private function fbAuthenticate() {
        $userExists = array();
        $userFunctions = new UserFunctions();
        $userNotificationModel = new UserNotification ();
        $userSession = $this->getUserSession();
        $locationData = $userSession->getUserDetail('selected_location');
        $referral_code = $userSession->getUserDetail('referral_code');
        $loyalityCode = !empty($userSession->getUserDetail('loyality_code')) ? $userSession->getUserDetail('loyality_code') : false;
        $open_page_type = $userSession->getUserDetail('open_page_type');
        $refId = $userSession->getUserDetail('refId');
        $host_name = $userSession->getUserDetail('host_name');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $code = $this->getQueryParams('code');
        $config = $this->getServiceLocator()->get('Config');

        session_start();
        $fb = new \Facebook\Facebook([
            'app_id' => $config['constants']['facebook']['app_key'],
            'app_secret' => $config['constants']['facebook']['app_secret'],
            'default_graph_version' => $config['constants']['facebook']['default_graph_version'],
        ]);

        $helper = $fb->getRedirectLoginHelper();

        try {
            $accessToken = $helper->getAccessToken();
            //pr($accessToken,1);
        } catch (FB\Exceptions\FacebookResponseException $e) {
            //echo 'Graph returned an error: ' . $e->getMessage();
            echo '<script type="text/javascript">window.self.close();</script>';
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            //echo 'Facebook SDK returned an error: ' . $e->getMessage();
            echo '<script type="text/javascript">window.self.close();</script>';
            exit;
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {
//              header('HTTP/1.0 401 Unauthorized');
//              echo "Error: " . $helper->getError() . "\n";
//              echo "Error Code: " . $helper->getErrorCode() . "\n";
//              echo "Error Reason: " . $helper->getErrorReason() . "\n";
//              echo "Error Description: " . $helper->getErrorDescription() . "\n";
                echo '<script type="text/javascript">window.self.close();</script>';
            } else {
                header('HTTP/1.0 400 Bad Request');
                echo 'Bad request';
            }
            exit;
        }
        $accToken = $accessToken->getValue();

        try {
            $response = $fb->get('/me', $accToken);
        } catch (FB\Exceptions\FacebookResponseException $e) {
            //echo 'Graph returned an error: ' . $e->getMessage();
            echo '<script type="text/javascript">window.self.close();</script>';
            exit;
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            //echo 'Facebook SDK returned an error: ' . $e->getMessage();
            echo '<script type="text/javascript">window.self.close();</script>';
            exit;
        }
        //pr($response);
        $me = $response->getGraphUser();
        $request = $response->getRequest();               
        $fbUser = $this->getUserFacebookDetails($me,$config);                
        $name = $me->getName();
        $nameDetails = explode(" ", $name);
        $userDetails['pictureL'] = \Facebook\FacebookClient::BASE_GRAPH_URL . "/" . $me->getId() . "/picture?type=large";
        $userDetails['pictureN'] = \Facebook\FacebookClient::BASE_GRAPH_URL . "/" . $me->getId() . "/picture?type=normal";
        $userDetails['pictureS'] = \Facebook\FacebookClient::BASE_GRAPH_URL . "/" . $me->getId() . "/picture?type=small";
        $userDetails['id'] = $me->getId();
        $userDetails['name'] = $me->getName();
        $userDetails['first_name'] = isset($fbUser['first_name'])?$fbUser['first_name']:$nameDetails['0'];//$me->getFirstName();
        $userDetails['last_name'] = isset($fbUser['last_name'])?$fbUser['last_name']:$nameDetails['1'];//$me->getLastName();
        $userDetails['link'] = isset($fbUser['link'])?$fbUser['link']:"";
        $userDetails['picture'] = isset($fbUser['picture']['data']['url'])?$fbUser['picture']['data']['url']:"";
        $userDetails['email'] = isset($fbUser['email'])?$fbUser['email']:"";
        $userDetails['access_token'] = $request->getAccessToken();

        //pr($userDetails, 1);
        $firstName = '';
        $userModel = new User ();
        $userAccountModel = new UserAccount();
        $joins = array();
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

        if (isset($userDetails ['email']) && !empty($userDetails['email'])) {
            $fname = explode("@", $userDetails ['email']);
            $firstName = $fname[0];
            $options = array(
                'columns' => array(
                    '*'
                ),
                'where' => array('users.email' => $userDetails ['email']),
                'joins' => $joins,
            );
        } else {
            $options = array(
                'columns' => array(
                    '*'
                ),
                'where' => array('ua.session_token' => $userDetails ['id']),
                'joins' => $joins,
            );
        }
        if (isset($userDetails ['first_name']) && !empty($userDetails ['first_name'])) {
            $firstName = $userDetails ['first_name'];
        }
        $userExists = $userModel->getUserDetail($options);
        $userAccountModel->user_name = $userDetails ['name'];
        $userAccountModel->first_name = $firstName;
        $userAccountModel->last_name = $userDetails ['last_name'];
        $userAccountModel->user_source = 'fb';
        $userModel->city_id = $locationData['city_id'];
        $userAccountModel->access_token = $userDetails['access_token'];
        $userAccountModel->session_token = $userDetails ['id'];
        $userModel->update_at = $currentDate;
        
                 
        if ($userExists) {
            if ($userExists ['status'] != 1) {
                $url = base64_decode($this->getQueryParams("ruri"));
                $urlArray = parse_url($url);
                $returnUrl = $urlArray['scheme'] . "://" . $urlArray['host'] . "/registration/error";
                echo '<script type="text/javascript">window.location.href="' . $returnUrl . '/userinactive"</script>';
                exit();           
            }
            $userModel->last_login = $currentDate;            
            if (strpos($userExists['display_pic_url'], 'graph.facebook.com') != -1) {
                if (isset($userDetails ['id'])) {
                    $userModel->display_pic_url = $userDetails['pictureS'];
                    $userModel->display_pic_url_normal = $userDetails['pictureN'];
                    $userModel->display_pic_url_large = $userDetails['pictureL'];
                }
            }
            $userModel->id = $userExists ['id'];
            $userAccountModel->user_id = $userExists ['id'];
            $userAccountModel->userAccountRegistration();
            $session = $this->getUserSession();
            $data = array(
                'email' => $userExists['email'],
                'user_source' => 'fb'
            );

            if ($loyalityCode && isset($referral_code) && !empty($referral_code)) {
                $referralDineMore = array(
                    "loyality_code" => $loyalityCode,
                    "referral_code" => $referral_code,
                    "user_id" => $userExists ['id'],
                    "email" => $userExists['email'],
                    "first_name" => $userDetails ['first_name']
                );

                $userFunctions->existUserJoinDineMoreByReferral($referralDineMore, $userExists ['id']);
            }

            $session->setUserDetail($data);
            $session->setUserId($userModel->id);
            $session->save();
        } else {
            if (isset($userDetails ['id'])) {
                $userModel->display_pic_url = $userDetails['pictureS'];
                $userModel->display_pic_url_normal = $userDetails['pictureN'];
                $userModel->display_pic_url_large = $userDetails['pictureL'];
            }
            if (!isset($userDetails ['email']) || $userDetails ['email'] == null) {
                $url = base64_decode($this->getQueryParams("ruri"));
                $session = $this->getUserSession();
                $setfacebookTempValue = array('user_name' => $userModel->user_name,
                    'first_name' => $userAccountModel->first_name,
                    'last_name' => $userAccountModel->last_name,
                    'access_token' => $userAccountModel->access_token,
                    'display_pic_url' => $userModel->display_pic_url,
                    'display_pic_url_normal' => $userModel->display_pic_url_normal,
                    'display_pic_url_large' => $userModel->display_pic_url_large,
                    'session_token' => $userAccountModel->session_token,
                    'user_source' => $userAccountModel->user_source,
                    'registration_subscription' => '1',
                    'referralCode' => $referral_code,
                );
                $session->setUserDetail(array(
                    'twitter_data' => $setfacebookTempValue,
                ));
                $session->save();
                
                if($host_name==PROTOCOL.SITE_URL){
                $url = $url;
                }else{
                $url = $url."/registration/close/twitter";
                }
                
                echo '<script type="text/javascript">window.location.href="' . $url . '"</script>';
                exit();
            } else {
                if (isset($userDetails ['id'])) {
                    $userModel->display_pic_url = $userDetails['pictureS'];
                    $userModel->display_pic_url_normal = $userDetails['pictureN'];
                    $userModel->display_pic_url_large = $userDetails['pictureL'];
                }
                $userModel->status = '1';
                $userModel->created_at = $currentDate;
                $userModel->update_at = $currentDate;
                $userModel->last_login = $currentDate;
                $userModel->registration_subscription = '1';
                $userModel->newsletter_subscribtion = '1';
                $userModel->email = $userDetails ['email'];
                $userModel->user_name = $userDetails ['name'];
                $userModel->first_name = $firstName;
                $userModel->last_name = $userDetails ['last_name'];
                $responseRegistration = $userModel->userRegistration();
                $userAccountModel->user_id = $responseRegistration['id'];
                $userAccountModel->userAccountRegistration();
            }
            $session = $this->getUserSession();
            $data = array(
                'email' => $userModel->email,
                'user_source' => 'fb'
            );
            $session->setUserDetail($data);
            $session->setUserId($userModel->id);
            $session->save();
        }

        $url = base64_decode($this->getQueryParams("ruri"));
        ######## Send Mail to user for registration #########
        if (!$userExists) {
            ############### Assign Promocode ##################
            $assignPromocode = $userFunctions->assignPromocodeOnFirstRegistration($userModel->id, $userDetails ['name'], $userDetails ['email']);
            ###################################################
//            $domain = $userFunctions->checkDomain($userModel->email);
//            if($domain === 'edu'){
//                $points = $userFunctions->getAllocatedPoints('eduRegister');
//                $message = 'All life is a game. Here are 400 points to get you ahead of the game. Don\'t worry, it\'s not cheating.';
//                $userFunctions->givePoints($points, $userModel->id, $message); 
//            }else{
            $points = $userFunctions->getAllocatedPoints('socialRegister');
            $message = 'All life is a game. Here are 100 points to get you ahead of the game. Don\'t worry, it\'s not cheating.';
            $userFunctions->givePoints($points, $userModel->id, $message);
//            }
            
            ############## salesmanago Event ##################
            $salesData = [];
            $salesData['name'] = $userModel->first_name;

            $salesData['email'] = $userModel->email;
            $salesData['dine_more'] = "No";
            $salesData['owner_email'] = 'no-reply@munchado.com';
            $salesData['tags'] = array("Registration_form");


            //23/12/2016 changes
            $salesData['restaurant_name'] = ($loyalityCode) ? $userFunctions->restaurant_name : '';
            $salesData ['restaurant_id'] = ($loyalityCode) ? $userFunctions->restaurantId : '';
            $salesData['tags'] = ($loyalityCode) ? array("Registration_form", "Dine_and_More", $userFunctions->restaurant_name) : array("Registration_form");
            $salesData['password'] = '';
            $salesData['user_source'] = "ws";
            //end 23/12/2016 changes                         

            $salesData['contact_ext_event_type'] = "OTHER";
            $salesData['redeempoint'] = 0;
            $salesData['point'] = 100;
            $salesData['totalpoint'] = (int) $userFunctions->userTotalPoint($userModel->id) + 100;
            $salesData['identifier'] = "register";
            $salesData['user_source'] = $userAccountModel->user_source;
            //$userFunctions->createQueue($salesData, 'Salesmanago');
            ###################################################            
           
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

            $feed_name = $userModel->first_name . ' ' . $userModel->last_name;
            $feed = array(
                'user_id' => $userModel->id,
                'user_email' => $userModel->email,
                'user_name' => ucfirst($feed_name)
            );
            $replacementData = array('message' => 'test');
            $otherReplacementData = array('user_name' => ucfirst($feed_name));

            $commonFunction = new \MCommons\CommonFunctions();
            $activityFeed = $commonFunction->addActivityFeed($feed, 53, $replacementData, $otherReplacementData);

            ######## Send Mail to user for registration #########
            $config = $this->getServiceLocator()->get('Config');
            $webUrl = PROTOCOL . $config['constants']['web_url'];
//            if ($domain === 'edu') {
//                $template = 'edu_subscriber';
//                $layout = 'default_new';
//                $subject = 'Welcome Friend!';
//                $variables = array('hostname' => $webUrl);
//                $mailData = array('recievers' => $userDetails ['email'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
//                $userFunctions->emailSubscription($mailData);
//            } else {
            $sender = NOTIFICATION_SENDER_EMAIL;
            
            if ((!$loyalityCode || strtoupper($loyalityCode) === MUNCHADO_DINE_MORE_CODE) && ($host_name==PROTOCOL.SITE_URL)) {
                $template = 'user-registration';
                $layout = 'email-layout/default_register';
                $variables = array(
                    'username' => $userDetails ['name'],
                    'hostname' => $webUrl,
                        //'promocodeAmount'=>$assignPromocode['discount'],
                        //'endDate'=>$assignPromocode['endDate']
                );
                $data = array('recievers' => $userDetails ['email'], 'layout' => $layout, 'template' => $template, 'variables' => $variables);
                $userFunctions->sendRegistrationEmail($data);
                $host_name = PROTOCOL.SITE_URL;
            }elseif(PROTOCOL.SITE_URL != $host_name){
                $template = 'registration_from_micro_site';
                $layout = 'email-layout/ma_default';
                $variables = array(
                    'username' => $userDetails ['name'], 
                    'hostname' => $host_name,
                    'restaurant_name'=>isset($data['restaurant_name'])?$data['restaurant_name']:"",
                    'restaurant_logo'=>isset($data['restaurant_logo'])?$data['restaurant_logo']:"",
                    'restaurant_address'=>isset($data['restaurant_address'])?$data['restaurant_address']:"",
                    'facebook_url'=>isset($data['facebook_url'])?$data['facebook_url']:"",
                    'twitter_url'=>isset($data['twitter_url'])?$data['twitter_url']:"",
                    'instagram_url'=>isset($data['instagram_url'])?$data['instagram_url']:"",
                    'restaurant_id'=>isset($data['restaurant_id'])?$data['restaurant_id']:"",                                       
                    );
                $mailData = array('recievers' => $userDetails ['email'], 'layout' => $layout, 'template' => $template, 'variables' => $variables);
                $userFunctions->sendRegistrationEmail($mailData);
                
            }

            $userFunctions->createSettings($userModel->id);
            if (isset($this->referalId) && $this->referalId != null) {
                $userFunctions->invitationAccepted($this->referalId, $userDetails ['email'], true);
            }

            ######### Intigration of user reffer invitation ############
            if ($referral_code && !empty($referral_code)) {
                $userFunctions->saveReferredUserInviterData($userModel->id, $referral_code);                           
            }
            ############################################################
            ############## clevertap profile uploadt ##################
            $clevertapData = array(
                    "user_id"=>$userModel->id,
                    "name"=>($userModel->last_name)?$userModel->first_name." ".$userModel->last_name:$userModel->first_name,
                    "email"=>$userModel->email,
                    "currentDate"=>$currentDate,
                    "source"=> 'fb',
                    "loyalitycode"=>($loyalityCode)?$loyalityCode:false,
                    "restname"=>($loyalityCode) ? $userFunctions->restaurant_name : '',
                    "restid"=>($loyalityCode) ? $userFunctions->restaurantId : '',
                    "eventname"=>($loyalityCode)?"dine_and_more":"general",
                    "host_name"=> $host_name
                );
            if(isset($data['referral_code']) && !empty($data['referral_code'])){
                 $clevertapData['refferralPoint'] = $userFunctions->referralPoint;       
            }
            //$userFunctions->clevertapUploadProfile($clevertapData);  
            $userFunctions->clevertapRegistrationEvent($clevertapData);
            ###################################################
            if (isset($userDetails ['email'])) {
                $userFunctions->invitationReservationNewUser($userDetails ['email'], $userModel->id, true);
            }
            //StaticOptions::sendMail ( $sender, $sendername, $recievers, $template, $layout, $variables, $subject );
            if($host_name==PROTOCOL.SITE_URL){
                $url = $userFunctions->getRegistrationCloseUrl($url);
            }else{
                $url = $url."/registration/close";
            }
            echo '<script type="text/javascript">window.location.href="' . $url . '"</script>';
            exit();
        }
        ########### Associate user through deeplink ##############
        $open_page_type = (isset($open_page_type) && !empty($open_page_type)) ? $open_page_type : "";
        $refId = (isset($refId) && !empty($refId)) ? $refId : "";
        $userId = $userModel->id;
        $userEmail = $userDetails ['email'];
        if (!empty($open_page_type)) {
            $userFunctions->associateInvitation($open_page_type, $refId, $userId, $userEmail);
        }
        if($host_name==PROTOCOL.SITE_URL){
                $url = $url;
            }else{
                $url = $url."/registration/login";
            }
        ##########################################
        #######End of send mail ########  
        echo '<script type="text/javascript">window.location.href="' . $url . '"</script>';
        exit();
    }
    
    public function getUserFacebookDetails($me,$config){
        
        $uri = $config ['facebook'] ['facebook_url'] . $me->getId() . "?access_token=" . $config['constants']['facebook']['access_token']."&fields=&fields=picture,email,first_name,last_name";
        $client = new \Zend\Http\Client($uri, self::$config);
        $req = $client->getRequest();
        $response = $client->send($req)->getBody();
        if (empty($response)) {
            return array();
        }
        return Json::decode($response, Json::TYPE_ARRAY);
    }
    
    private function twitterLogin() {
        $userdetails = $this->getUserSession();
        $twitteroauth = new TwitterOAuth(TWITTER_APP_KEY, TWITTER_SECRET_KEY);
        $turi = $this->getQueryParams("return_url");
        $request_token = $twitteroauth->getRequestToken($this->getBaseUrl() . '/wapi/user/login/twitterauthenticate?token=' . $userdetails->token);
        $userdetails->setUserDetail('twitter_auth_token', $request_token ['oauth_token']);
        $userdetails->setUserDetail('twitter_auth_secret_token', $request_token ['oauth_token_secret']);
        $userdetails->setUserDetail('twitter_return_url', $turi);
        $userdetails->setUserDetail('referral_code', $this->referralCode);
        $userdetails->setUserDetail('loyality_code', $this->loyalityCode);
        $userdetails->setUserDetail('open_page_type', $this->open_page_type);
        $userdetails->setUserDetail('refId', $this->refId);
        $userdetails->setUserDetail('host_name',$this->host_name);
        $userdetails->save();
        $url = $twitteroauth->getAuthorizeURL($request_token ['oauth_token']);
        $response = $this->redirect()->toUrl($url);
        $response->sendHeaders();
    }

    private function twitterAuthenticate() {
        $serverData = $this->getRequest()->getServer()->toArray();
        $userFunctions = new UserFunctions ();
        $session = $this->getUserSession();
        $locationData = $session->getUserDetail('selected_location');
        $referral_code = $session->getUserDetail('referral_code');
        $loyalityCode = !empty($session->getUserDetail('loyality_code')) ? $session->getUserDetail('loyality_code') : false;
        $open_page_type = $session->getUserDetail('open_page_type');
        $refId = $session->getUserDetail('refId');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $userNotificationModel = new UserNotification ();
        $userdetails = $this->getUserSession();
        $oauth_verifier = $this->getQueryParams('oauth_verifier');
        $redirect_url = $this->getUserSession()->getUserDetail('twitter_return_url');
        $twitter_auth_token = $userdetails->getUserDetail('twitter_auth_token');
        $twitter_auth_secret_token = $userdetails->getUserDetail('twitter_auth_secret_token');
        $host_name = $session->getUserDetail('host_name');
        if (!empty($oauth_verifier) && !empty($twitter_auth_token) && !empty($twitter_auth_secret_token)) {
            $twitteroauth = new TwitterOAuth(TWITTER_APP_KEY, TWITTER_SECRET_KEY, $twitter_auth_token, $twitter_auth_secret_token);
            $access_token = $twitteroauth->getAccessToken($oauth_verifier);
            $user_info = $twitteroauth->get('account/verify_credentials');
            $userModel = new User ();
            $userAccountModel = new UserAccount();
            $options = array(
                'where' => array(
                    'session_token' => $user_info->id
                )
            );
            $userExists = $userAccountModel->find($options)->current();
            if ($userExists) {
                $userExists = $userExists->toArray();
                $userModel->exchangeArray($userExists);
                $userAccountModel->exchangeArray($userExists);
                $userModel->update_at = $currentDate;
                $userModel->last_login = $currentDate;
                $userModel->id = $userExists ['user_id'];
                $userEmail = $userModel->getUserEmail($userExists ['user_id']);
                if ($userEmail ['status'] != 1) {                   
                    $returnUrl = $redirect_url . "/registration/error/userinactive";
                    echo '<script type="text/javascript">window.location.href="' . $returnUrl . '"</script>';
                    exit();           
                }
                $userModel->email = $userEmail['email'];
                $userAccountModel->user_id = $userExists ['user_id'];
            } else {
                $userModel->status = '1';
                $userModel->created_at = $currentDate;
            }
            $userModel->user_name = $user_info->screen_name;
            if ($user_info->name != null) {
                $name = explode(' ', $user_info->name);
                $userAccountModel->first_name = isset($name [0]) ? $name [0] : '';
                $userAccountModel->last_name = isset($name [1]) ? $name [1] : '';
            }
            $userAccountModel->access_token = $access_token ['oauth_token'];
            $userModel->display_pic_url = $user_info->profile_image_url_https;
            $userModel->display_pic_url_normal = $user_info->profile_image_url_https;
            $userModel->display_pic_url_large = $this->getImageUrl($user_info->profile_image_url_https, 'large');
            $userAccountModel->session_token = $user_info->id;
            $userAccountModel->user_source = 'tw';
            $userModel->registration_subscription = '1';
            $session->setUserId($userModel->id);
            $setTwitterTempValue = array('user_name' => $user_info->screen_name,
                'first_name' => $userAccountModel->first_name,
                'last_name' => $userAccountModel->last_name,
                'access_token' => $userAccountModel->access_token,
                'display_pic_url' => $userModel->display_pic_url,
                'display_pic_url_normal' => $userModel->display_pic_url_normal,
                'display_pic_url_large' => $userModel->display_pic_url_large,
                'session_token' => $userAccountModel->session_token,
                'user_source' => $userAccountModel->user_source,
                'registration_subscription' => '1',
                'referralCode' => $referral_code,
            );
            //pr($setTwitterTempValue,true);
            //$session->setTwitterUserDetail($setTwitterTempValue);
            $session->setUserDetail(array(
                'twitter_data' => $setTwitterTempValue,
                'user_source' => 'tw',
            ));
            $session->save();
            //$userModelTwitter = $this->getUserSession()->getTwitterUserDetail();
            if (!$userExists) {
                $userFunctions->createSettings($userModel->id);
            }

            if ($userExists) {
                ########### Associate user through deeplink ##############
                $open_page_type = (isset($open_page_type) && !empty($open_page_type)) ? $open_page_type : "";
                $refId = (isset($refId) && !empty($refId)) ? $refId : "";
                $userId = $userModel->id;
                $userEmail = $userModel->email;
                if (!empty($open_page_type)) {
                    $userFunctions->associateInvitation($open_page_type, $refId, $userId, $userEmail);
                }
                ##########################################

                if ($loyalityCode && isset($referral_code) && !empty($referral_code)) {
                    $referralDineMore = array(
                        "loyality_code" => $loyalityCode,
                        "referral_code" => $referral_code,
                        "user_id" => $userModel->id,
                        "email" => $userModel->email,
                        "first_name" => $userAccountModel->first_name
                    );

                    $userFunctions->existUserJoinDineMoreByReferral($referralDineMore, $userModel->id);
                }
            }
            if($host_name==PROTOCOL.SITE_URL){
                $redirect_url = $redirect_url;
            }else{
                $redirect_url = $redirect_url."/registration/close/twitter";
            }
            echo '<script type="text/javascript">window.location.href="' . $redirect_url . '"</script>';
            exit();
        } else {
            echo '<script type="text/javascript">window.self.close();</script>';
            exit();
        }
    }

    private function getImageUrl($url = null, $size = null) {
        if ($url != null && $size == 'large') {
            $search = array_pop(explode('/', $url));
            $replace = str_replace('_normal', '', $search);
            $url = str_replace($search, $replace, $url);
            return $url;
        }
    }

    private function googleLogin() {
        $session = $this->getUserSession();
        $state = base64_encode($session->token . ":::" . $this->getQueryParams("return_url"));
        $this->googleclient->setState($state);
        $authUrl = $this->googleclient->createAuthUrl();
        $response = $this->redirect()->toUrl($authUrl);
        //Raju khatak assign value in session
        $s = new \Zend\Session\Container('base');
        $s->offsetSet('referral_code', $this->referralCode);
        $s->offsetSet('loyality_code', $this->loyalityCode);
        $s->offsetSet('open_page_type', $this->open_page_type);
        $s->offsetSet('refId', $this->refId);

        //sudhanshu assign value in session
        $session->setUserDetail('referral_code', $this->referralCode);
        $session->setUserDetail('loyality_code', $this->loyalityCode);
        $session->setUserDetail('open_page_type', $this->open_page_type);
        $session->setUserDetail('refId', $this->refId);
        $session->setUserDetail('host_name', $this->host_name);

        $response->sendHeaders();
    }

    private function googleAuthenticate() {
        $serverData = $this->getRequest()->getServer()->toArray();
        $userFunctions = new UserFunctions ();
        $session = $this->getUserSession();
        $locationData = $session->getUserDetail('selected_location');
        $host_name = $session->getUserDetail('host_name');
        $s = new \Zend\Session\Container('base');
        $referral_code = $s->offsetGet('referral_code');
        $loyalityCode = !empty($s->offsetGet('loyality_code')) ? $s->offsetGet('loyality_code') : false;
        $open_page_type = $s->offsetGet('open_page_type');
        $refId = $s->offsetGet('refId');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $userNotificationModel = new UserNotification ();
        $request = $this->getRequest()->getQuery()->toArray();
        $encodedState = $request['state'];
        $decodedState = base64_decode($encodedState);
        $info = explode(":::", $decodedState);
        $redirectUrl = '';
        $firstName = '';
        if ($info && isset($info[1])) {
            $redirectUrl = $info[1];
        }
        if (isset($request ['code']) && !empty($request ['code'])) {
            $this->googleclient->authenticate($request ['code']);
            $tokens = $this->googleclient->getAccessToken();

            if ($tokens) {
                $userDetails = $this->googleplus->people->get('me');
                //$tokens = json_decode($tokens);
                $accessToken = $tokens['access_token'];
            }

            $userModel = new User ();
            $userAccountModel = new UserAccount();
            $joins = array();
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
            if (isset($userDetails ['emails'][0]['value'])) {
                $fname = explode("@", $userDetails ['emails'][0]['value']);
                $firstName = $fname[0];
                $options = array(
                    'columns' => array(
                        '*'
                    ),
                    'where' => array('users.email' => $userDetails ['emails'][0]['value']),
                    'joins' => $joins,
                );
            } else {
                $options = array(
                    'columns' => array(
                        '*'
                    ),
                    'where' => array('ua.session_token' => $userDetails ['id']),
                    'joins' => $joins,
                );
            }
            
            if (isset($userDetails ['name'] ['givenName']) && !empty($userDetails ['name'] ['givenName'])) {
                $firstName = $userDetails ['name'] ['givenName'];
            }

            $userExists = $userModel->getUserDetail($options);
            
            $userAccountModel->user_name = $userDetails ['displayName'];
            $userAccountModel->first_name = $firstName;
            $userAccountModel->last_name = $userDetails ['name'] ['familyName'];
            $userAccountModel->user_source = 'gp';
            $userAccountModel->access_token = $accessToken;
            $userAccountModel->session_token = $userDetails ['id'];
            $userModel->update_at = $currentDate;
            $userModel->email = $userDetails ['emails'] [0] ['value'];
            $domain = $userFunctions->checkDomain($userModel->email);
            if ($userExists) {
                if ($userExists ['status'] != 1) {               
                    $urlArray = parse_url($redirectUrl);
                    $returnUrl = $urlArray['scheme'] . "://" . $urlArray['host'] . "/registration/error/userinactive";
                    echo '<script type="text/javascript">window.location.href="' . $returnUrl . '"</script>';
                    exit();           
                }
                $userModel->id = $userExists ['id'];
                $userAccountModel->user_id = $userExists ['id'];
                $userModel->created_at = $userExists ['created_at'];
                $userModel->status = '1';
                $userModel->registration_subscription = '1';
                $userModel->newsletter_subscribtion = '1';
                $userModel->points = $userExists ['points'];
                $userModel->password = $userExists ['password'];
                $userModel->last_login = $currentDate;
                $userAccountModel->userAccountRegistration();
                $session = $this->getUserSession();
                $data = array(
                    'email' => $userExists['email'],
                    'user_source' => 'gp'
                );

                if ($loyalityCode && isset($referral_code) && !empty($referral_code)) {
                    $referralDineMore = array(
                        "loyality_code" => $loyalityCode,
                        "referral_code" => $referral_code,
                        "user_id" => $userExists ['id'],
                        "email" => $userExists['email'],
                        "first_name" => $userAccountModel->first_name
                    );

                    $userFunctions->existUserJoinDineMoreByReferral($referralDineMore, $userExists ['id']);
                }


                $session->setUserDetail($data);
                $session->setUserId($userModel->id);
                $session->save();
            } else {
                $userModel->update_at = $currentDate;
                $userModel->last_login = $currentDate;
                $userModel->status = '1';
                $userModel->display_pic_url = $userDetails ['image'] ['url'];
                $userModel->display_pic_url_normal = $userDetails ['image'] ['url'];
                $userModel->display_pic_url_large = $userDetails ['image'] ['url'];
                $userModel->created_at = $currentDate;
                $userModel->city_id = $locationData['city_id'];
                $userModel->registration_subscription = '1';
                $userModel->newsletter_subscribtion = '1';
                $userModel->user_name = $userDetails ['displayName'];
                $userModel->first_name = $firstName;
                $userModel->last_name = $userDetails ['name'] ['familyName'];
                $responseRegistration = $userModel->userRegistration();
                $userAccountModel->user_id = $responseRegistration['id'];
                $userAccountModel->userAccountRegistration();
                $data = array(
                    'email' => $userModel->email,
                    'user_source' => 'gp'
                );
            }
            if (!$userExists) {
//                if($domain === 'edu'){
//                    $points = $userFunctions->getAllocatedPoints('eduRegister');
//                    $message = 'All life is a game. Here are 400 points to get you ahead of the game. Don\'t worry, it\'s not cheating.';
//                    $userFunctions->givePoints($points, $userModel->id, $message); 
//                }else{
                $points = $userFunctions->getAllocatedPoints('socialRegister');
                $message = 'All life is a game. Here are 100 points to get you ahead of the game. Don\'t worry, it\'s not cheating.';
                $userFunctions->givePoints($points, $userModel->id, $message);
//                }
                ############## salesmanago Event ##################
                $salesData = [];
                $salesData['name'] = $userModel->first_name;
                $salesData['email'] = $userModel->email;
                $salesData['dine_more'] = ($loyalityCode) ? "Yes" : "No";
                ;
                $salesData['owner_email'] = 'no-reply@munchado.com';
                //today changes
                $salesData['restaurant_name'] = ($loyalityCode) ? $userFunctions->restaurant_name : '';
                $salesData ['restaurant_id'] = ($loyalityCode) ? $userFunctions->restaurantId : '';
                $salesData['tags'] = ($loyalityCode) ? array("Registration_form", "Dine_and_More", $userFunctions->restaurant_name) : array("Registration_form");
                $salesData['password'] = '';
                $salesData['user_source'] = "ws";
                //end today changes

                $salesData['contact_ext_event_type'] = "OTHER";
                $salesData['redeempoint'] = 0;
                $salesData['point'] = 100;
                $salesData['totalpoint'] = (int) $userFunctions->userTotalPoint($userAccountModel->user_id) + 100;
                $salesData['identifier'] = "register";
                $salesData['user_source'] = $userAccountModel->user_source;
                //$userFunctions->createQueue($salesData, 'Salesmanago');
                ###################################################
            }

            $session = $this->getUserSession();
            $session->setUserDetail($data);
            $session->setUserId($userModel->id);
            $session->save();
            $config = $this->getServiceLocator()->get('Config');
            $webUrl = PROTOCOL . $config['constants']['web_url'];

            if (!$userExists) {                
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

                $feed_name = $userModel->first_name . ' ' . $userModel->last_name;
                $feed = array(
                    'user_id' => $userModel->id,
                    'user_email' => $userModel->email,
                    'user_name' => ucfirst($feed_name)
                );
                $replacementData = array('message' => 'test');
                $otherReplacementData = array('user_name' => ucfirst($feed_name));

                $commonFunction = new \MCommons\CommonFunctions();
                $activityFeed = $commonFunction->addActivityFeed($feed, 53, $replacementData, $otherReplacementData);

                ######## Send Mail to user for registration #########
                ############### Assign Promocode ##################
                $assignPromocode = $userFunctions->assignPromocodeOnFirstRegistration($userModel->id, $userModel->first_name, $userModel->email);
                ###################################################
//                $domain = $userFunctions->checkDomain($userDetails ['emails'] [0] ['value']);
//                if($domain === 'edu'){
//                    $template = 'edu_subscriber';
//                    $layout = 'default_new';
//                    $subject = 'Welcome Friend!';
//                    $variables = array('username' => $userDetails ['displayName'],'hostname' => $webUrl);
//                    $mailData = array('recievers' => $userDetails ['emails'] [0] ['value'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
//                    $userFunctions->emailSubscription($mailData);
//                }else{
                
                if ((!$loyalityCode || strtoupper($loyalityCode) === MUNCHADO_DINE_MORE_CODE) &&  $host_name==PROTOCOL.SITE_URL) {
                    $template = 'user-registration';
                    $layout = 'email-layout/default_register';
                    $variables = array('username' => $userDetails ['displayName'], 'hostname' => $webUrl);
                    $data = array('recievers' => $userDetails ['emails'] [0] ['value'], 'layout' => $layout, 'template' => $template, 'variables' => $variables);
                    $userFunctions->sendRegistrationEmail($data);
                    $host_name = PROTOCOL.SITE_URL;
                }elseif(PROTOCOL.SITE_URL != $host_name){                
                    $template = 'registration_from_micro_site';
                    $layout = 'email-layout/ma_default';
                    $variables = array(
                        'username' => $userDetails ['displayName'], 
                        'hostname' => $host_name,
                        'restaurant_name'=>isset($data['restaurant_name'])?$data['restaurant_name']:"",
                        'restaurant_logo'=>isset($data['restaurant_logo'])?$data['restaurant_logo']:"",
                        'restaurant_address'=>isset($data['restaurant_address'])?$data['restaurant_address']:"",
                        'facebook_url'=>isset($data['facebook_url'])?$data['facebook_url']:"",
                        'twitter_url'=>isset($data['twitter_url'])?$data['twitter_url']:"",
                        'instagram_url'=>isset($data['instagram_url'])?$data['instagram_url']:"",
                        'restaurant_id'=>isset($data['restaurant_id'])?$data['restaurant_id']:"",                                               
                        );
                    $mailData = array('recievers' => $userDetails ['emails'] [0] ['value'], 'layout' => $layout, 'template' => $template, 'variables' => $variables);
                    $userFunctions->sendRegistrationEmail($mailData);
                    
                }

                $userFunctions->createSettings($userModel->id);

                if (isset($this->referalId) && $this->referalId != null) {
                    $userFunctions->invitationAccepted($this->referalId, $userModel->email, true);
                }

                ######### Intigration of user reffer invitation ############
                if ($referral_code && !empty($referral_code)) {
                    $userFunctions->saveReferredUserInviterData($userModel->id, $referral_code);                     
                }
                ############################################################
                 ############## clevertap profile uploadt ##################                
                $clevertapData = array(
                    "user_id"=>$userModel->id,
                    "name"=>($userModel->last_name)?$userModel->first_name." ".$userModel->last_name:$userModel->first_name,
                    "email"=>$userModel->email,
                    "currentDate"=>$currentDate,
                    "source"=> 'gp',
                    "loyalitycode"=>($loyalityCode)?$loyalityCode:false,
                    "restname"=>($loyalityCode) ? $userFunctions->restaurant_name : '',
                    "restid"=>($loyalityCode) ? $userFunctions->restaurantId : '',
                    "eventname"=>($loyalityCode)?"dine_and_more":"general",
                    "host_name"=>$host_name
                );
                if($referral_code && !empty($referral_code)){
                     $clevertapData['refferralPoint'] = $userFunctions->referralPoint;  
                }
                //$userFunctions->clevertapUploadProfile($clevertapData); 
                $userFunctions->clevertapRegistrationEvent($clevertapData);
                ###################################################

                if (isset($userDetails ['emails'] [0] ['value'])) {
                    $userFunctions->invitationReservationNewUser($userDetails ['emails'] [0] ['value'], $userModel->id, true);
                }
                if($host_name==PROTOCOL.SITE_URL){
                    $redirectUrl = $userFunctions->getRegistrationCloseUrl($redirectUrl);
                }else{
                    $redirectUrl = $redirectUrl."/registration/close";
                }
                
                echo '<script type="text/javascript">window.location.href="' . $redirectUrl . '"</script>';
                exit();
            }
            ########### Associate user through deeplink ##############
            $open_page_type = (isset($open_page_type) && !empty($open_page_type)) ? $open_page_type : "";
            $refId = (isset($refId) && !empty($refId)) ? $refId : "";
            $userId = $userModel->id;
            $userEmail = $userDetails ['emails'] [0] ['value'];
            if (!empty($open_page_type)) {
                $userFunctions->associateInvitation($open_page_type, $refId, $userId, $userEmail);
            }
            ##########################################    
            #######End of send mail ########
            if($host_name==PROTOCOL.SITE_URL){
                $redirectUrl = $redirectUrl;
            }else{
                $redirectUrl = $redirectUrl."/registration/login";
            }

            echo '<script type="text/javascript">window.location.href="' . $redirectUrl . '"</script>';
            exit();
        } else {
            echo '<script type="text/javascript">window.close();</script>';
            exit();
        }
    }

}
