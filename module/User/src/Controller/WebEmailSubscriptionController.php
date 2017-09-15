<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\EmailSubscription;
use MCommons\StaticOptions;
use User\UserFunctions;

class WebEmailSubscriptionController extends AbstractRestfulController {

    public function create($data) {
        $response = array();

        if (!isset($data['email']) || empty($data['email'])) {
            throw new \Exception("Email is not valid", 400);
        }
        
        if (!isset($data['zip']) || empty($data['zip'])) {
            throw new \Exception("Required zip code", 400);
        }
        
        if (!isset($data['source']) || empty($data['source'])) {
            throw new \Exception("Required source", 400);
        }
        unset($data['token']);
        
        $requestData['email'] = $data['email'];
        $requestData['zip'] = $data['zip'];
        $requestData['source'] = strtolower($data['source']);        
        
        $emailSubscription = new EmailSubscription();        
        $emailSubscription->getDbTable()->setArrayObjectPrototype('ArrayObject');

        $userModel = new \User\Model\User();
        $source = '';
        $userFunction = new UserFunctions();
        $locationData = $this->getUserSession ()->getUserDetail ( 'selected_location', array () );
        $currentDate = $userFunction->userCityTimeZone($locationData);
        
        $requestData['created_on'] = $currentDate;
        
        if((strtolower($data['source'])=='promotion')||(strtolower($data['source'])=='promotion_androidapp')){
            $requestData['zip'] = 0;
            $options = array(
                'where' => array(
                    'email' => $data['email'],                    
                    'source'=>strtolower($data['source'])
                )
            );  
            $eSubscription = $emailSubscription->find($options)->toArray();
            if($eSubscription){
                //send email for Promotion Android App
                if(strtolower($data['source'])=='promotion_androidapp')
                {
                    $template = 'android_app_notify';
                    $layout = 'default_android_app';
                    $subject = 'Munch Ado Android App Update';
                    $variables = array(
                        'email' => $data ['email'],
                        'campaignName'=>$data['source']
                    );
                    $mailData = array('recievers' => $data['email'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
                    $userFunction->emailSubscription($mailData);
                }
                return array('success' => 3);
            }else{         
                $addSubscription = $emailSubscription->insert($requestData);              
                //send email for Promotion Android App
                if(strtolower($data['source'])=='promotion_androidapp')
                {
                    $template = 'android_app_notify';
                    $layout = 'default_android_app';
                    $subject = 'Munch Ado Android App Update';
                    $variables = array(
                        'email' => $data ['email'],
                        'campaignName'=>$data['source']
                    );
                    $mailData = array('recievers' => $data['email'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
                    $userFunction->emailSubscription($mailData);
                }
                return array('success' => 6);               
            }
        }elseif(strtolower($data['source']) === 'subscribe'){
            $options = array(
                'where' => array(
                    'email' => $data['email'],
                    'zip' => $data['zip'],
                    'source'=>strtolower($data['source'])
                )
            );  
            $eSubscription = $emailSubscription->find($options)->toArray();
            if($eSubscription){
                return array('success' => 3);
            }else{      
                $addSubscription = $emailSubscription->insert($requestData);              
                return array('success' => 6);               
            }
        }elseif(strtolower($data['source']) === 'moviepass'){
            $options2 = array(
                'where' => array(
                    'email' => $data['email'],
                    'source'=>strtolower($data['source'])
                )
            );
            $eSubscription = $emailSubscription->find($options2)->toArray();
            if($eSubscription){
                return array('success' => 3);
            }else{               
               $addSubscription = $emailSubscription->insert($requestData);
               $userId = $this->getUserSession()->getUserId();              
             /**
             * Push To Pubnub For User
             */
//               if($userId){
//                $notificationMsg = 'Challenge Accepted. Day 1 of MoviePass via Munch Ado begins.';
//                $channel = "mymunchado_" . $userId;
//                $notificationArray = array(
//                    "msg" => $notificationMsg,
//                    "channel" => $channel,
//                    "userId" => $userId,
//                    "type" => 'moviepass',
//                    "restaurantId" => '',
//                    'curDate' => $currentDate
//                );
//                $userNotificationModel = new \User\Model\UserNotification();
//                $response = $userNotificationModel->createPubNubNotification($notificationArray);
//                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//               }
               return array('success' => 6);
            }
        }else{
            $options1 = array(
                'where' => array(
                    'email' => $data['email']
                )
            );
             $eSubscription = $emailSubscription->find($options1)->toArray();
             if ($eSubscription) {
                foreach($eSubscription as $key => $val){
                    $source = strtolower($val['source']);
                    if ($source === strtolower($data['source'])) {
                        return array('success' => 3);
                    }
                }           
              }
        }        
               
        $userDetail = $userModel->getUserEmailSubscriber($data['email']);        
        
        if ($eSubscription) {
            //Add subscription, send mail
            $addSubscription = $emailSubscription->insert($requestData);
            if (COUPON_SUBSCRIPTION == 1) {
                $template = 'Email-Subscription-registration';
                $layout = 'default_email_subscription_without_10';
                $subject = 'More for you from Munch Ado';
                if ($userDetail) {
                    $variables = array(
                        'username' => ucfirst($userDetail['first_name']),
                        'campaignName' => $data['source'],
                    );
                } else {
                    $variables = array(
                        'username' => 'Buddy',
                        'campaignName' => $data['source'],
                    );
                }
                $mailData = array('recievers' => $data['email'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
                $userFunction->emailSubscription($mailData);
            }
            return array('success' => 4,'source'=>$data['source']);
        } else {
            
            $addSubscription = $emailSubscription->insert($requestData);
            if (empty($userDetail)) {
                //if doesn't exist, register the user, assign promocode and send email template 1
                $firstName = explode('@', $data['email']);
                $planePassword = $this->createPassword();
                $userModel->first_name = $firstName[0];
                $userModel->last_name = (isset($data ['last_name'])) ? $data ['last_name'] : '';
                $userModel->email = $data ['email'];
                if (isset($planePassword) && $planePassword != null) {
                    $userModel->password = md5($planePassword);
                }
                $userModel->newsletter_subscribtion = (isset($data ['newsletter_subscription'])) ? $data ['newsletter_subscription'] : '';
                $userModel->created_at = $currentDate;
                $userModel->order_msg_status = '';
                $userModel->status = 1;
                $userModel->user_source = 'ws';
                $userModel->display_pic_url = 'noimage.jpg';
                $userModel->display_pic_url_normal = 'noimage.jpg';
                $userModel->display_pic_url_large = 'noimage.jpg';
                $userModel->bp_status = 0;
                $userModel->accept_toc = 1;
                $userModel->registration_subscription = '0';
                $response = $userModel->userRegistration();
                $addPromocode = false;
                if ($response && $addSubscription) {
                    ######### Give point to user for registration ##############
                    $points = $userFunction->getAllocatedPoints('normalRegister');
                    $message = 'Welcome to Munch Ado! You\'ll need points to have the most fun, here take 15. Hoard them wisely.';
                    $points['type'] = "normalRegister";
                    $userFunction->givePointsEmailSubscription($points, $userModel->id, $message);
                    ############################################################
                    if (COUPON_SUBSCRIPTION == 1) {
                        $promocodes = new \Restaurant\Model\Promocodes();
                        $pDetails['start_on'] = $currentDate;
                        $currentDate1 = new \DateTime($currentDate);
                        $currentDate1->add(new \DateInterval(PROMOCODE_ENDDATE));
                        $endDate = $currentDate1->format('Y-m-d h:i:s');
                        $pDetails['end_date'] = $endDate;
                        $pDetails['discount'] = 10;
                        $pDetails['discount_type'] = 'flat';
                        $pDetails['status'] = 1;
                        $pDetails['deal_for'] = 'order';
                        $pDetails['title'] = '$10 off on live order';
                        $pDetails['description'] = 'Enjoy free order up to $10 on delivery or take-out orders';
                        $addPromocode = $promocodes->insert($pDetails);
                        if ($addPromocode) {
                            $upDetail['promo_id'] = $promocodes->id;
                            $upDetail['user_id'] = $userModel->id;
                            $upDetail['reedemed'] = 0;
                            $upDetail['order_id'] = 0;
                            $userPromocode = new \Restaurant\Model\UserPromocodes();
                            $addUserPromocode = $userPromocode->insert($upDetail);
                            if ($addUserPromocode) {
                                //send mail template 1
                                $template = 'Email-Subscription';
                                $layout = 'default_email_subscription';
                                $subject = 'Claim Your $10 Offer';
                                $variables = array(
                                    'username' => ucfirst($firstName[0]),
                                    'email' => $data ['email'],
                                    'password' => $planePassword,
                                    'campaignName'=>$data['source']
                                );
                                $mailData = array('recievers' => $data['email'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
                                $userFunction->emailSubscription($mailData);
                            }
                        }
                    }
                }
                return array('success' => 1,'source'=>'');
            } else {
                //if exists and send email template 2

                if ($addSubscription) {
                    //send mail template 1
                    if (COUPON_SUBSCRIPTION == 1) {
                        if ($userDetail['registration_subscription'] == 0) {
                            $isExistPromocode = $this->isExistPromocode($userDetail['id'],$currentDate);
                            $promocodes = new \Restaurant\Model\Promocodes();
                            $currentDate1 = new \DateTime($currentDate);
                            $currentDate1->add(new \DateInterval('P1M'));
                            $endDate = $currentDate1->format('Y-m-d h:i:s');
                            if ($isExistPromocode) {
                                $updateDetails['discount'] = $isExistPromocode[0]['discount'] + 10;
                                $updateDetails['end_date'] = $endDate;
                                $promocodes->id = $isExistPromocode[0]['promo_id'];
                                $addPromocode = $promocodes->update($updateDetails);
                            } else {
                                $pDetails['start_on'] = $currentDate;
                                $pDetails['end_date'] = $endDate;
                                $pDetails['discount'] = 10;
                                $pDetails['discount_type'] = 'flat';
                                $pDetails['status'] = 1;
                                $pDetails['deal_for'] = 'order';
                                $pDetails['title'] = '$10 off on live order';
                                $pDetails['description'] = 'Enjoy free order up to $10 on delivery or take-out orders';
                                $addPromocode = $promocodes->insert($pDetails);
                                if ($addPromocode) {
                                    $upDetail['promo_id'] = $promocodes->id;
                                    $upDetail['user_id'] = $userDetail['id'];
                                    $upDetail['reedemed'] = 0;
                                    $upDetail['order_id'] = 0;
                                    $userPromocode = new \Restaurant\Model\UserPromocodes();
                                    $addUserPromocode = $userPromocode->insert($upDetail);
                                }
                            }
                            $joinDate = date_create($userDetail['created_at']);
                            $diff = date_diff($currentDate, $joinDate);
                            if ($diff->format("%a") < 1) {
                                $joinTime = $diff->format("%h hour(s)");
                            } else {
                                $joinTime = $diff->format("%a day(s)");
                            }

                            $template = 'Email-Subscription-more-from-munchado';
                            $layout = 'default_email_subscription';
                            $subject = 'More From Munch Ado';

                            $variables = array(
                                'username' => ucfirst($userDetail['first_name']),
                                'campaignName' => $data['source'],
                                'joinTime' => $joinTime
                            );
                            $mailData = array('recievers' => $data['email'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
                            $userFunction->emailSubscription($mailData);
                            return array('success' => 5,'source'=>'');
                        } elseif ($userDetail['registration_subscription'] == 1) {
                            $template = '05_So_They_Get_Another_Coupon';
                            $layout = 'default_email_subscription_without_10';
                            $subject = 'More for you from Munch Ado';
                            $variables = array(
                                'username' => ucfirst($userDetail['first_name']),
                                'campaignName' => $data['source'],
                            );
                            $mailData = array('recievers' => $data['email'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
                            $userFunction->emailSubscription($mailData);
                            return array('success' => 2,'source'=>'');
                        }
                    }

                    
                }
            }
        }
    }

    public function createPassword() {
        $length = 10;
        $verification_code = '';
        list ( $usec, $sec ) = explode(' ', microtime());
        mt_srand((float) $sec + ((float) $usec * 100000));
        $inputs = array_merge(range('z', 'a'), range(0, 9), range('A', 'Z'));
        for ($i = 0; $i < $length; $i ++) {
            $verification_code .= $inputs {mt_rand(0, 61)};
        }
        return $verification_code;
    }

    public function isExistPromocode($userId,$currentDate) {
        $userPromocodesDetails = array();
        $userPromocodeModel = new \Restaurant\Model\UserPromocodes();
        $userPromocodeModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'p' => 'promocodes'
            ),
            'on' => new \Zend\Db\Sql\Expression('p.id = promo_id AND p.status = 1'),
            'columns' => array(
                'user_promocode_id' => new \Zend\Db\Sql\Expression('user_promocodes.id'),
                'start_on',
                'end_date',
                'discount',
                'discount_type',
                'minimum_order_amount',
                'slots',
                'days',
                'deal_for',
                'title',
                'description'
            ),
            'type' => 'right'
        );
        $options = array(
            'columns' => array(
                'user_id',
                'promo_id'
            ),
            'where' => array(
                'reedemed' => 0,
                'user_id' => $userId
            ),
            'joins' => $joins
        );
        $userPromocodes = $userPromocodeModel->find($options)->toArray();
        if (!empty($userPromocodes)) {
            $promocodeStartTimestamp = strtotime($userPromocodes[0]['start_on']);
            $promocodeEndTimestamp = strtotime($userPromocodes[0]['end_date']);
            $currentDateTimeUnixTimeStamp = strtotime($currentDate);
            if ($currentDateTimeUnixTimeStamp <= $promocodeEndTimestamp && $currentDateTimeUnixTimeStamp >= $promocodeStartTimestamp) {
                return $userPromocodes;
            }
        } else {
            return false;
        }
    }

}
