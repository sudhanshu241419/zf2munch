<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserInvitation;
use User\Model\UserReservation;
use User\Model\User;
use MCommons\StaticOptions;
use User\Model\UserFriends;
use User\UserFunctions;
use User\Model\PointSourceDetails;
use User\Model\UserPoint;
use User\Model\UserNotification;
use Restaurant\Model\Restaurant;
use Restaurant\OrderFunctions;

class SendInvitationController extends AbstractRestfulController {
    private $user_name;
    private $user_email;
    private $host_name;
    private $inviter = array();
    private $reservation;
    private $data = array();
    private $reservationDate;
    private $reservationTime;
    private $currentDate;
    
    public function create($data) {
        $user_id = $this->getUserSession()->getUserId();
        try {
            $this->data = $data;
            $this->validate();
            $userInvitationModel = new UserInvitation();
            $userInvitationModel->user_id = $user_id;
            $userInvitationModel->reservation_id = $this->data['reservation_id'];
            ####### Get Reservation Details ############
            $reservation1 = $this->getReservationDetails();
            if (!$reservation1) {
                throw new \Exception('Invalid reservation.', 400);
            }
            $this->reservation = $reservation1->getArrayCopy();
            $this->reservationDate = StaticOptions::getFormattedDateTime($this->reservation['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
            $this->reservationTime = StaticOptions::getFormattedDateTime($this->reservation['time_slot'], 'Y-m-d H:i:s', 'h:i A');

            $this->currentDate = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $this->reservation['restaurant_id']
                    ))->format(StaticOptions::MYSQL_DATE_FORMAT);

            $this->user_email = $this->reservation['email'];
            $this->host_name = ucfirst($this->reservation['first_name']);
            //set senders name and email id refactor it
            $this->getInviterDetail($user_id);

            $friendsEmail = array_diff(explode(';', trim($data['friendsEmailAddress'], ';')), array($this->user_email));

            foreach ($friendsEmail as $email) {
                $this->sendInvitation($user_id, $email, $data);
            }
            return array('response' => true);
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }

    public function update($id, $data) {
        $response = array();
        $userInvitationModel = new UserInvitation();
        $userModel = new User();
        $userFriendModel = new UserFriends();
        $session = $this->getUserSession();        
        $user_function = new UserFunctions();
        $userNotificationModel = new UserNotification();
        $restaurantModel = new Restaurant();
        $reservationModel = new UserReservation();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        
        $isLoggedIn = $session->isLoggedIn();
        $commonFunctiion = new \MCommons\CommonFunctions();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $userInvitationModel->id = $id;
        $type = $data['accepted'];        
        $status = isset($config['constants']['reservation_invitation_status']) ? $config['constants']['reservation_invitation_status'] : array();
        $statusArray = array(
            $status['accepted'],
            $status['denied']
        );
        if ($type == 'true') {
            $response = $userInvitationModel->getInvitationStatusCheck($status['accepted']);
            if ($response) {
                $invite = $userInvitationModel->getUserInvitation(array(
                    'where' => array(
                        'id' => $id
                    )
                ));

                $reservationId = $invite['reservation_id'];
                $opts = array('where' => array('id' => $reservationId));
                $reservationDetail = $reservationModel->getUserReservation($opts);
                $orderId = $reservationDetail[0]['order_id'];
                $partySize = $reservationDetail[0]['party_size'];
                $date = date('Y-m-d', strtotime($reservationDetail[0]['time_slot']));
                $time = date('h:i A', strtotime($reservationDetail[0]['time_slot']));
                /* Add Point */
                $pointMsg = "5 points to you for breaking bread with a friend…or acquaintance or frenemy...";
                $pointAdded = $user_function->pointAddedInUserAccount($invite['user_id'], 17, $id, $pointMsg);
                /* End Add Point */
                $user = $userModel->getUserDetail(array(
                    'where' => array(
                        'email' => $invite['friend_email']
                    )
                ));
                if ($user) {
                    $friend_id = $user['id'];
                    $userF = $userFriendModel->getFriendStatus(array(
                        'where' => array(
                            'user_id' => $invite['user_id'],
                            'friend_id' => $friend_id
                        )
                    ));
                    if ($invite['user_id'] != $friend_id) {
                        $userFrExist = $userFriendModel->getFriendStatus(array(
                            'where' => array(
                                'user_id' => $invite['user_id'],
                                'friend_id' => $friend_id,
                                'status' => 2
                            )
                        ));
                    }
                    if ($userFrExist) {
                        $updatedata = array(
                            'user_id' => $invite['user_id'],
                            'friend_id' => $friend_id,
                        );
                        $updatedata2 = array(
                            'user_id' => $friend_id,
                            'friend_id' => $invite['user_id'],
                        );
                        $userFriendModel->updateFriends($updatedata);
                        $userFriendModel->updateFriends($updatedata2);
                    } elseif ($userF) {
                        $st = 'already friend';
                    } else {
                        $date = strtotime($currentDate);
                        $expiredOn = strtotime("+7 day", $date);
                        $expiredOn = date('Y-m-d H:i:s', $expiredOn);
                        $userFriendsInvitationModel = new \User\Model\UserFriendsInvitation();
                        $insertInvitationData = array(
                            'user_id' => $userId,
                            'email' => $invite['friend_email'],
                            'source' => 'munch',
                            'created_on' => $currentDate,
                            'token' => $this->getUserSession()->token,
                            'expired_on' => $expiredOn,
                            'status' => '1'
                        );
                        $friendOptions = array(
                            'columns' => array('id'),
                            'where' => array('user_id' => $userId, 'email' => $invite['friend_email'], 'invitation_status' => array(0, 1))
                        );
                        $getFriendExists = $userFriendsInvitationModel->find($friendOptions)->toArray();
                        if (count($getFriendExists) == 0) {
                            $insertDataTrue = $userFriendsInvitationModel->createUserInvitation($insertInvitationData);
                            if ($insertDataTrue) {
                                $insertdata = array(
                                    'user_id' => $invite['user_id'],
                                    'friend_id' => $friend_id,
                                    'created_on' => $currentDate,
                                    'invitation_id' => $insertDataTrue,
                                    'status' => 1
                                );
                                $insertdata2 = array(
                                    'user_id' => $friend_id,
                                    'friend_id' => $invite['user_id'],
                                    'created_on' => $currentDate,
                                    'invitation_id' => $insertDataTrue,
                                    'status' => 1
                                );
                                if ($invite['user_id'] != $friend_id) {
                                    $userFriendModel->createFriends($insertdata);
                                    $userFriendModel->createFriends($insertdata2);
                                }
                            }
                        } else {
                            $st = 'already friend';
                        }
                    }
                }
                $userDetails = $userModel->getUserDetail(array(
                    'where' => array(
                        'email' => $invite['friend_email']
                    )
                ));

                if ($userDetails) {
                    $userName = $userDetails['first_name'];
                }
                $restaurant = $restaurantModel->findRestaurant(array(
                    'column' => array(
                        'restaurant_name'
                    ),
                    'where' => array(
                        'id' => $invite['restaurant_id']
                    )
                ));
                if (isset($orderId) && $orderId != NULL && !empty($orderId)) {
                    $notificationMsg = ucfirst($userName) . ' has joined your pre-paid reservation at ' . $restaurant->restaurant_name . "! Freeloader";
                } else {
                    $notificationMsg = ucfirst($userName) . ' joined your reservation at ' . $restaurant->restaurant_name.' in a shared quest for food greatness.';
                }

                #   Add activity feed data   # 
                
                $inviterDetails = $userModel->getUserDetail(array(
                    'where' => array(
                        'email' => $invite['user_id']
                    )
                ));

                $eventDate = $reservationDetail[0]['time_slot'];
                $replacementData = array('friend_name' => ucfirst($userName), 'restaurant_name' => $restaurant->restaurant_name);
                $inviterName = (isset($inviterDetails['last_name']) && !empty($inviterDetails['last_name'])) ? $inviterDetails['first_name'] . " " . $inviterDetails['last_name'] : $inviterDetails['first_name'];    
                $otherReplacementData = array('friend_name' => ucfirst($userName),'user_name'=>ucfirst($inviterName),'restaurant_name' => $restaurant->restaurant_name);
                $uname = (isset($userDetails['last_name']) && !empty($userDetails['last_name'])) ? $userName . " " . $userDetails['last_name'] : $userName;
                $feed = array(
                    'user_name' => ucfirst($uname),
                    'event_date_time' => $eventDate,
                    'restaurant_name' => $restaurant->restaurant_name,
                    'restaurant_id' => $invite['restaurant_id'],
                    'no_of_people' => $partySize,
                    'reservation_date' => date("M d Y", strtotime($date)),
                    'reservation_time' => $time,
                    'img' => array(),
                    'friend_id' =>$friend_id,
                    'user_id' => $invite['user_id']
                );
                $feedMe = array(
                    'user_name' => ucfirst($uname),
                    'event_date_time' => $eventDate,
                    'restaurant_name' => $restaurant->restaurant_name,
                    'restaurant_id' => $invite['restaurant_id'],
                    'no_of_people' => $partySize,
                    'reservation_date' => date("M d Y", strtotime($date)),
                    'reservation_time' => $time,
                    'img' => array(),
                    'friend_id' => $friend_id
                );
                //$activityFeed2 = $commonFunctiion->addActivityFeed($feedMe, 6, $replacementData, $otherReplacementData); 
                
                if (isset($orderId) && $orderId != NULL && !empty($orderId)) {
                    $activityFeed = $commonFunctiion->addActivityFeed($feed, 59, $replacementData, $otherReplacementData);
                } else {
                   $activityFeed = $commonFunctiion->addActivityFeed($feed, 6, $replacementData, $otherReplacementData);
                }
                


                $replacementData = array('restaurant_name' => $restaurant->restaurant_name);
                $otherReplacementData = array();
                $feed = array(
                    'user_name' => ucfirst($userName),
                    'event_date_time' => $eventDate,
                    'restaurant_name' => $restaurant->restaurant_name,
                    'restaurant_id' => $invite['restaurant_id'],
                    'no_of_people' => $partySize,
                    'reservation_date' => date("M d Y", strtotime($date)),
                    'reservation_time' => $time,
                    'friend_id' => $friend_id,
                    'img' => array()
                );
                //$activityFeed = $commonFunctiion->addActivityFeed($feed, 20, $replacementData, $otherReplacementData);


                ###############################

                $channel = "mymunchado_" . $invite['user_id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $invite['user_id'], //$friend_id,
                    "friend_iden_Id" => $invite['user_id'],
                    "type" => 'reservation',
                    "restaurantId" => $invite['restaurant_id'],
                    'curDate' => $currentDate,
                    'username' => ucfirst($userName),
                    'no_of_people' => $partySize,
                    'date' => $date,
                    'time' => $time,
                    'friend_id' => $friend_id,
                    'reservation_id' => $reservationId,
                    'restaurant_name' => $restaurant->restaurant_name,
                    'reservation_status' => $reservationDetail[0]['status']
                );
                $notificationJsonArray = array('reservation_status' => $reservationDetail[0]['status'], 'reservation_id' => $reservationId, 'friend_id' => $friend_id, 'time' => $time, 'date' => $date, 'no_of_people' => $partySize, 'username' => ucfirst($userName), 'restaurant_id' => $invite['restaurant_id'], "user_id" => $friend_id, 'restaurant_name' => $restaurant->restaurant_name);
                $responsePubnub = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                
                 $inviterName=$userModel->getFirstName($invite['user_id']);
                $notificationMsg = ' You\'re going to ' . ucfirst($inviterName) . '’s reservation. Have fun!';
                
                $channel = "mymunchado_" . $friend_id;
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "friend_iden_Id"=>$friend_id,
                    "userId" => $invite['user_id'],
                    "type" => 'cancelreservation',
                    "restaurantId" => $invite['restaurant_id'],
                    'curDate' => $currentDate,
                    'username'=>ucfirst($userName),
                    'restaurant_name'=>$restaurantDetails->restaurant_name
                );
                $notificationJsonArray = array('username'=>ucfirst($inviterName),
                    'restaurant_name'=>$restaurantDetails->restaurant_name,'user_id'=>$invite['user_id'],'restaurant_id'=>$invite['restaurant_id']);
                $responsePubnub = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);

                ######## Send mail to user after accept reservation ##############
                $reservationDate = StaticOptions::getFormattedDateTime($reservationDetail[0]['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
                $reservationTime = StaticOptions::getFormattedDateTime($reservationDetail[0]['time_slot'], 'Y-m-d H:i:s', 'h:i A');
                $variables = array(
                    'username' => ucfirst($reservationDetail[0]['first_name']), //$recieverUserName,
                    'invitee' => ucfirst($userName),
                    'peopleNo' => $reservationDetail[0]['party_size'],
                    'restaurantName' => $reservationDetail[0]['restaurant_name'],
                    'reservationDate' => $reservationDate,
                    'reservationtime' => $reservationTime,
                );
                $layout = 'email-layout/default_new';
                if (isset($orderId) && $orderId != NULL && !empty($orderId)) {
                    $template = "Came_In_Like_A_Wrecking_Ball";
                    $subject = ucfirst($userName) . ' is So Ready for Food at '.$restaurant->restaurant_name;
                } else {
                    $template = "agreed_to_join_munchado";
                    $subject = "Good Times Ahead at ".$restaurant->restaurant_name;
                }
                $emailData = array(
                    'receiver' => $reservationDetail[0]['email'],
                    'variables' => $variables,
                    'subject' => $subject,
                    'template' => $template,
                    'layout' => $layout
                );

                ###################                        
                $user_function->sendMails($emailData);
                ##################################################################
            }
        } elseif ($type == 'false') {
            $response = $userInvitationModel->getInvitationStatusCheck($status['denied']);
            $invite = $userInvitationModel->getUserInvitation(array(
                'where' => array(
                    'id' => $id
                )
            ));

            $reservationId = $invite['reservation_id'];
            $opts = array('where' => array('id' => $reservationId));
            $reservationDetail = $reservationModel->getUserReservation($opts);
            $orderId = $reservationDetail[0]['order_id'];
            $partySize = $reservationDetail[0]['party_size'];
            $date = date('Y-m-d', strtotime($reservationDetail[0]['time_slot']));
            $time = date('h:i A', strtotime($reservationDetail[0]['time_slot']));
            $reservationUserName = isset($reservationDetail[0]['first_name']) ? $reservationDetail[0]['first_name'] : '';
            $userDetails = $userModel->getUserDetail(array(
                'where' => array(
                    'email' => $invite['friend_email']
                )
            ));
            if ($userDetails) {
                $userName = $userDetails['first_name'];

                $restaurantDetails = $restaurantModel->findRestaurant(array(
                    'columns' => array(
                        'restaurant_name'
                    ),
                    'where' => array(
                        'id' => $invite['restaurant_id']
                    )
                ));
                if (isset($orderId) && $orderId != NULL && !empty($orderId)) {
                    //$notificationMsg = ucfirst($userName) . ' declined your generous pre-paid reservation invitation to ' . $restaurantDetails->restaurant_name . '. Drats.';
                    $notificationMsg = ucfirst($userName) . ' declined your generous pre-paid reservation invitation to' . $restaurantDetails->restaurant_name . ' Drats.';
                } else {
                    $notificationMsg = ucfirst($userName) . ' declined to join you in breaking bread at ' . $restaurantDetails->restaurant_name . ' .Who needs \'em anyway?';
                }

                #   Add activity feed data   # 

                $eventDate = $reservationDetail[0]['time_slot'];

                $replacementData = array('friends' => ucfirst($userName), 'restaurant_name' => $restaurantDetails->restaurant_name);
                $otherReplacementData = array();
                $uname = (isset($userDetails['last_name']) && !empty($userDetails['last_name'])) ? $userName . " " . $userDetails['last_name'] : $userName;
                $feed = array(
                    'user_name' => ucfirst($reservationUserName),
                    'restaurant_name' => $restaurantDetails->restaurant_name,
                    'restaurant_id' => $invite['restaurant_id'],
                    'event_date_time' => $eventDate,
                    'no_of_people' => $partySize,
                    'reservation_date' => date("M d Y", strtotime($date)),
                    'reservation_time' => $time,
                    'img' => array(),
                    'friend_id' => $userDetails['id'],
                    'user_id' => $invite['user_id'],
                );
                 if (isset($orderId) && $orderId != NULL && !empty($orderId)) {
                    $activityFeed = $commonFunctiion->addActivityFeed($feed, 60, $replacementData, $otherReplacementData);
                } else {
                  $activityFeed = $commonFunctiion->addActivityFeed($feed, 7, $replacementData, $otherReplacementData);
                }
                

                $replacementData = array('host_name' => ucfirst($reservationUserName), 'restaurant_name' => $restaurantDetails->restaurant_name);
                $otherReplacementData = array();
                $feed = array(
                    'user_name' => ucfirst($userName),
                    'restaurant_name' => $restaurantDetails->restaurant_name,
                    'restaurant_id' => $invite['restaurant_id'],
                    'event_date_time' => $eventDate,
                    'no_of_people' => $partySize,
                    'reservation_date' => date("M d Y", strtotime($date)),
                    'reservation_time' => $time,
                    'friend_id' => $userDetails['id'],
                    'user_id' => $invite['user_id'],
                    'img' => array()
                );
                $activityFeed = $commonFunctiion->addActivityFeed($feed, 19, $replacementData, $otherReplacementData);


                ###############################
                $channel = "mymunchado_" . $invite['user_id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $userDetails['id'],
                    "friend_iden_Id"=>$invite['user_id'],
                    "type" => 'cancelreservation',
                    "restaurantId" => $invite['restaurant_id'],
                    'curDate' => $currentDate,
                    'username' => ucfirst($userName),
                    'no_of_people' => $partySize,
                    'date' => $date,
                    'time' => $time,
                    'reservation_id' => $reservationId,
                    'restaurant_name' => $reservationDetail[0]['restaurant_name']
                );
                $notificationJsonArray = array('reservation_id' => $reservationId, 'time' => $time, 'date' => $date, 'no_of_people' => $partySize, 'username' => ucfirst($userName), 'restaurant_id' => $invite['restaurant_id'], "user_id" => $userDetails['id'], 'restaurant_name' => $restaurantDetails->restaurant_name);
                $responsePubnub = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                
                 $inviterName=$userModel->getFirstName($invite['user_id']);
                $notificationMsg = 'You declined ' . ucfirst($inviterName) . '’s reservation. Their loss!';
                
                $channel = "mymunchado_" . $userDetails['id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "friend_iden_Id"=>$userDetails['id'],
                    "userId" => $invite['user_id'],
                    "type" => 'cancelreservation',
                    "restaurantId" => $invite['restaurant_id'],
                    'curDate' => $currentDate,
                    'username'=>ucfirst($userName),
                    'restaurant_name'=>$restaurantDetails->restaurant_name
                );
                $notificationJsonArray = array('username'=>ucfirst($inviterName),
                    'restaurant_name'=>$restaurantDetails->restaurant_name,'user_id'=>$invite['user_id'],'restaurant_id'=>$invite['restaurant_id']);
                $responsePubnub = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);

                ######## Send mail to user after decline reservation ##############
                if (isset($orderId) && $orderId != NULL && !empty($orderId)) {
                    $reservationDate = StaticOptions::getFormattedDateTime($reservationDetail[0]['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
                    $reservationTime = StaticOptions::getFormattedDateTime($reservationDetail[0]['time_slot'], 'Y-m-d H:i:s', 'h:i A');
                    $variables = array(
                        'username' => ucfirst($reservationDetail[0]['first_name']), //$recieverUserName,
                        'invitee' => ucfirst($userName),
                        'peopleNo' => $reservationDetail[0]['party_size'],
                        'restaurantName' => $reservationDetail[0]['restaurant_name'],
                        'reservationDate' => $reservationDate,
                        'reservationtime' => $reservationTime,
                    );
                    $template = "Turned_Down_For_What";
                    $subject = ucfirst($userName) . ' Declined Your Generous Invite';
                    $emailData = array(
                        'receiver' => $reservationDetail[0]['email'],
                        'variables' => $variables,
                        'subject' => $subject,
                        'template' => $template,
                        'layout' => 'email-layout/default_new'
                    );

                    ###################                        
                    $user_function->sendMails($emailData);
                } else {
                    $reservationDate = StaticOptions::getFormattedDateTime($reservationDetail[0]['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
                    $reservationTime = StaticOptions::getFormattedDateTime($reservationDetail[0]['time_slot'], 'Y-m-d H:i:s', 'h:i A');
                    $variables = array(
                        'username' => ucfirst($reservationDetail[0]['first_name']), //$recieverUserName,
                        'invitee' => ucfirst($userName),
                        'peopleNo' => $reservationDetail[0]['party_size'],
                        'restaurantName' => $reservationDetail[0]['restaurant_name'],
                        'reservationDate' => $reservationDate,
                        'reservationtime' => $reservationTime,
                    );
                    $template = "Turned_Down_For_What_normal_reservation";
                    $subject = ucfirst($userName) . ' Declined Your Generous Invite';
                    $emailData = array(
                        'receiver' => $reservationDetail[0]['email'],
                        'variables' => $variables,
                        'subject' => $subject,
                        'template' => $template,
                        'layout' => 'email-layout/default_new'
                    );
                    $user_function->sendMails($emailData);
                }
                ##################################################################
            }
        } else {
            throw new \Exception('Type not found', 404);
        }
        if ($response) {
            return array("response" => true);
        } else {
            return array("response" => false);
        }
    }
    
   private function validate() {
        if (empty($this->data)) {
            throw new \Exception("Invalid Parameters", 400);
        }
        if (!isset($this->data['reservation_id'])) {
            throw new \Exception("Reservation detail is invalid", 400);
        }
        if (!isset($this->data['friendsEmailAddress']) && empty($this->data['friendsEmailAddress'])) {
            throw new \Exception("Required valid friend email id", 400);
        }
    }

    //$email : Recievers_email, $userId : inviter_id
    private function sendInvitation($userId, $email) {
        $userModel = new User();
        $userF = $userModel->getUserByEmailMob($email);

        if ($userF) {
            $to_id = $userF['id'];
            $user_type = 1;
            $friend_name = $userF['first_name'];
            if ($friend_name == '') {
                $emails = explode("@", $email);
                $friend_name = $emails[0];
            }
        } else {
            $to_id = 0;
            $user_type = 0;
            $emails = explode("@", $email);
            $friend_name = $emails[0];
        }

        $userInvitationModel = new UserInvitation();
        $userInvitationModel->user_id = $userId;
        $userInvitationModel->reservation_id = $this->data['reservation_id'];
        $userInvitationModel->message = '';
        $userInvitationModel->to_id = $to_id;
        $userInvitationModel->restaurant_id = $this->reservation['restaurant_id'];
        $userInvitationModel->message .= isset($this->data['message']) ? $this->data['message'] : '';
        $userInvitationModel->friend_email = $email;
        $userInvitationModel->user_type = $user_type;
        $userInvitationModel->created_on = $this->currentDate;
        $userInvitationModel->msg_status = 0;

        // if inviter is not on munchado platform, add invitation detail user_reservation_invitation table
        if (empty($userInvitationModel->user_id)) {
            $get_user_invitation = $userInvitationModel->getUserInvitation(array(
                'columns' => array('id'),
                'where' => array(
                    'reservation_id' => $userInvitationModel->reservation_id,
                    'friend_email' => $email
                )
            ));
            $userInvitationModel->id = isset($get_user_invitation['id']) ? $get_user_invitation['id'] : '';
            $userInvitationModel->createInvitation();
            return;
        }
        
        //user is registered on munchado. send notification mail, add invitation detail user_reservation_invitation table
        $get_user_invitation = $userInvitationModel->getUserInvitation(array(
            'columns' => array('id', 'msg_status'),
            'where' => array(
                'reservation_id' => $userInvitationModel->reservation_id,
                'user_id' => $userInvitationModel->user_id,
                'friend_email' => $email
            )
        ));

        $userInvitationModel->id = isset($get_user_invitation['id']) ? $get_user_invitation['id'] : '';
        $userInvitationModel->msg_status = isset($get_user_invitation['msg_status']) ? $get_user_invitation['msg_status'] : 0;
        $user_reservation_invitation = $userInvitationModel->createInvitation();
        $config = $this->getServiceLocator()->get('Config');
        $webUrl = $config['constants']['web_url'];
        $mailText = $this->data['message'];
        if (isset($this->data ['instruction'])) {
            $mailText .= "," . $this->data ['instruction'];
        }

        $recievers = array($email);
        $layout = 'email-layout/default_new';
        $acceptUrl = $webUrl;
        if (isset($this->reservation['order_id']) && !empty($this->reservation['order_id']) && $this->reservation['order_id'] != NULL) {
            $template = "is_buying_food_you_in";
            $subject = "You, Us, Them, & Food";    
            $orderFunctions = new OrderFunctions();
            $orders = $this->getServiceLocator()->get("User\Controller\WebUserOrderController");
            $orderDetails = $orders->get($this->reservation['order_id']);
            $deliveryDateTime = explode(" ", $orderDetails['delivery_time']);
            $orderDatas['order_details']['delivery_date'] = $deliveryDateTime[0];
            $orderDatas['order_details']['delivery_time'] = $deliveryDateTime[1];
            $orderDatas['order_details']['email'] = '';
            $orderDatas['order_details']['items'] = $orderDetails['order_details'];
            $orderDatas['order_details']['order_type'] = $orderDetails['order_Type'];
            $orderDatas['order_details']['order_type1'] = '';
            $orderDatas['order_details']['order_type2'] = '';
            $orderDatas['order_details']['restaurant_id'] = $this->reservation['restaurant_id'];
            $orderDatas['order_details']['special_instruction'] = (isset($orderDetails['special_checks'])) ? explode("||", $orderDetails['special_checks']) : '';
            $orderDatas['order_details']['tax'] = $orderDetails['tax'];
            $orderDatas['order_details']['tip_percent'] = $orderDetails['tip_percent'];
            $orderFunctions->calculatePrice($orderDatas ['order_details']);
            $status = $orderFunctions->getOrderStatus($deliveryDateTime[0], $deliveryDateTime[1], $this->reservation['restaurant_id']);
            $orderDataInvite = $orderFunctions->makeOrderForMailInvite($orderFunctions->itemDetails, $this->reservation['restaurant_id'], $status, $orderDetails['order_amount']);
            $notificationMsg = ucfirst($this->reservation['first_name']) . ' is paying for dinner at ' . $this->reservation['restaurant_name'] . '! You in?';
            $variables = array(
            'username' => $this->reservation['first_name'],
            'friendname' => $friend_name,
            'peopleNo'=>$this->reservation['party_size'],  
            'mailtext' => $mailText,
            'acceptUrl' => $acceptUrl,
            'restaurantName' => $this->reservation['restaurant_name'],
            'reservationDate' => $this->reservationDate,
            'reservationTime' => $this->reservationTime,
            'hostname' => $webUrl,
            'orderType' => "Pre-paid Reservation",
            'receiptNo' => $this->reservation['receipt_no'],
            //'specialInstructions' => (isset($orderDetails['special_checks'])) ? explode("||", $orderDetails['special_checks']) : '',
            'orderData' => $orderDataInvite,
           
        );
        } else {
            $template = "friends-reservation-Invitation";
            $subject = 'Someone Wants To Grab Food With You';           
            $notificationMsg = ucfirst($this->reservation['first_name']) . ' invited you to their reservation, you in?';
            $variables = array(
            'username' => $this->reservation['first_name'],
            'friendname' => $friend_name,
            'mailtext' => $mailText,
            'acceptUrl' => $acceptUrl,
            'restaurantName' => $this->reservation['restaurant_name'],
            'reservationDate' => $this->reservationDate,
            'reservationtime' => $this->reservationTime,
            'hostname' => $webUrl
        );
        }
        
        
        $emailData = array(
            'receiver' => $recievers,
            'variables' => $variables,
            'subject' => $subject,
            'template' => $template,
            'layout' => $layout
        );

        ###################
        if ($user_reservation_invitation) {
            $this->createNotification($to_id, $notificationMsg,$userId);
            $userFunction = new UserFunctions();
            
            $commonFunctiion = new \MCommons\CommonFunctions();              
               $replacementData = array('inviter'=>ucfirst($this->reservation['first_name']));
               $otherReplacementData = array();
               $feed = array(   
                       'user_name'=>ucfirst($this->reservation['first_name']),                        
                       "user_id" => $to_id
                   );
               $activityFeed = $commonFunctiion->addActivityFeed($feed, 57, $replacementData, $otherReplacementData);
               $userFunction->sendMails($emailData);
        }
    }

    private function getInviterDetail($inviterId) {
        if (!empty($inviterId)) {
            $userModel = new User();
            $inviterDetail = $userModel->getUserDetail(array('columns' => array('first_name', 'last_name', 'email'),
                'where' => array('id' => $inviterId)
            ));

            $this->user_name = $inviterDetail['first_name'];
            if ($this->user_name == '') {
                $useremail = explode("@", $inviterDetail['email']);
                $this->user_name = $useremail[0];
            }
        } else {
            $this->user_name = $this->host_name;
            $this->inviter['email'] = $this->user_email;
        }
    }   
    
    private function getReservationDetails() {
        $userReservation = new UserReservation();
        return $reservation = $userReservation->getUserReservationCurrent(array(
            'columns' => array(
                'restaurant_name',
                'restaurant_id',
                'time_slot',
                'email',
                'first_name',
                'order_id',
                'party_size',
                'receipt_no',
                'status'
            ),
            'where' => array(
                'id' => $this->data['reservation_id']
            )
        ));        
    }
    
    private function createNotification($to_id, $notificationMsg, $userId = false) {
        # Pubnub Notification to invitee(Friend) in case of pre-ordering with reservation
        $userNotificationModel = new UserNotification();
        $channel = "mymunchado_" . $to_id;
        $notificationArray = array(
            "msg" => $notificationMsg,
            "channel" => $channel,
            "userId" => ($userId)?$userId:$to_id,
            "type" => 'reservation',
            "restaurantId" => $this->reservation['restaurant_id'],
            'curDate' => $this->currentDate,
            'username' => ucfirst($this->reservation['first_name']),
            'restaurant_name' => $this->reservation['restaurant_name'],
            'reservation_id' => $this->data['reservation_id'],
            'reservation_status' => $this->reservation['status']
        );
             
        $notificationJsonArray = array('reservation_status' => $this->reservation['status'], 'reservation_id' => $this->data['reservation_id'], 'restaurant_id' => $this->reservation['restaurant_id'], "user_id" => $to_id, 'username' => ucfirst($this->reservation['first_name']), 'restaurant_name' => $this->reservation['restaurant_name']);
        $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
        StaticOptions::pubnubPushNotification($notificationArray);
    }


}
