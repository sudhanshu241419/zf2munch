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

class WebUserReservationInvitationController extends AbstractRestfulController {
    /*
     * Reservation Invitation accept/deny PUT Method
     */

    public function update($id, $data) {
        $response = array();
        $session = $this->getUserSession();  
        $userInvitationModel = new UserInvitation();
        $userModel = new User();
        $userFriendModel = new UserFriends();              
        $user_function = new UserFunctions();
        $userNotificationModel = new UserNotification();
        $restaurantModel = new Restaurant();
        $reservationModel = new UserReservation();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        
        $isLoggedIn = $session->isLoggedIn();

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
        $invite = $userInvitationModel->getUserInvitation(array(
            'where' => array(
                'id' => $id
            )
        ));
        $restaurantDetails = $restaurantModel->findRestaurant(array(
            'columns' => array(
                'restaurant_name'
            ),
            'where' => array(
                'id' => $invite['restaurant_id']
            )
        ));
        if ($type == 'true') {
            $response = $userInvitationModel->getInvitationStatusCheck($status['accepted']);
            if ($response) {
                
                $reservationId = $invite['reservation_id'];
                $opts = array('where' => array('id' => $reservationId));
                $reservationDetail = $reservationModel->getUserReservation($opts);
                $orderId = $reservationDetail[0]['order_id'];
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
                        $insertdata = array(
                            'user_id' => $invite['user_id'],
                            'friend_id' => $friend_id,
                            'created_on' => $currentDate,
                            'status' => 1,
                            'invitation_id' => $id
                        );
                        $insertdata2 = array(
                            'user_id' => $friend_id,
                            'friend_id' => $invite['user_id'],
                            'created_on' => $currentDate,
                            'status' => 1,
                            'invitation_id' => $id
                        );
                        if ($invite['user_id'] != $friend_id) {
                            $userFriendModel->createFriends($insertdata);
                            $userFriendModel->createFriends($insertdata2);
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
                    $notificationMsg = ucfirst($userName) . '  joined your reservation at ' . $restaurant->restaurant_name . ' in a shared quest for food greatness.';
                }
                $channel = "mymunchado_" . $invite['user_id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $friend_id,
                    "friend_iden_Id" => $invite['user_id'],
                    "type" => 'reservation',
                    "restaurantId" => $invite['restaurant_id'],
                    'curDate' => $currentDate,
                    'username' => ucfirst($userName),
                    'restaurant_name' => $restaurant->restaurant_name,
                    'reservation_id' => $reservationId,
                    'reservation_status' => $reservationDetail[0]['status']
                );
                $notificationJsonArray = array('reservation_id' => $reservationId,
                    'reservation_status' => $reservationDetail[0]['status'], 'username' => ucfirst($userName),
                    'restaurant_name' => $restaurant->restaurant_name, 'user_id' => $friend_id, 'restaurant_id' => $invite['restaurant_id']);
                $responsePubnub = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);


                $inviterName = $userModel->getFirstName($invite['user_id']);
                $notificationMsg = ' You\'re going to ' . ucfirst($inviterName) . '’s reservation. Have fun!';

                $channel = "mymunchado_" . $invite['to_id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "friend_iden_Id" => $invite['to_id'],
                    "userId" => $invite['user_id'],
                    "type" => 'cancelreservation',
                    "restaurantId" => $invite['restaurant_id'],
                    'curDate' => $currentDate,
                    'username' => ucfirst($userName),
                    'restaurant_name' => $restaurantDetails->restaurant_name
                );
                $notificationJsonArray = array('username' => ucfirst($inviterName),
                    'restaurant_name' => $restaurantDetails->restaurant_name, 'user_id' => $invite['user_id'], 'restaurant_id' => $invite['restaurant_id']);
                $responsePubnub = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
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
                    $subject = ucfirst($userName) . ' is So Ready for Food at ' . $restaurant->restaurant_name;
                } else {
                    $template = "agreed_to_join_munchado";
                    $subject = "Good Times Ahead at " . $reservationDetail[0]['restaurant_name'];
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
                #######################################
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

            $userDetails = $userModel->getUserDetail(array(
                'where' => array(
                    'email' => $invite['friend_email']
                )
            ));

            if ($userDetails) {
                $userName = $userDetails['first_name'];

                if (isset($orderId) && $orderId != NULL && !empty($orderId)) {
                    // $notificationMsg = ucfirst($userName) . ' declined your generous pre-paid reservation invitation to ' . $restaurantDetails->restaurant_name . '. Drats.'; 
                    $notificationMsg = ucfirst($userName) . ' RSVPed “No” to your reservation at ' . $restaurantDetails->restaurant_name . '.Who needs em anyway?';
                } else {
                    $notificationMsg = ucfirst($userName) . ' declined to join you in breaking bread at ' . $restaurantDetails->restaurant_name . '.Who needs \'em anyway?';
                }
                $channel = "mymunchado_" . $invite['user_id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "friend_iden_Id" => $invite['user_id'],
                    "userId" => $invite['to_id'],
                    "type" => 'cancelreservation',
                    "restaurantId" => $invite['restaurant_id'],
                    'curDate' => $currentDate,
                    'username' => ucfirst($userName),
                    'restaurant_name' => $restaurantDetails->restaurant_name
                );
                $notificationJsonArray = array('username' => ucfirst($userName),
                    'restaurant_name' => $restaurantDetails->restaurant_name, 'user_id' => $invite['to_id'], 'restaurant_id' => $invite['restaurant_id']);
                $responsePubnub = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);

                $inviterName = $userModel->getFirstName($invite['user_id']);
                $notificationMsg = ' You declined  ' . ucfirst($inviterName) . '’s reservation. Their loss!';

                $channel = "mymunchado_" . $invite['to_id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "friend_iden_Id" => $invite['to_id'],
                    "userId" => $invite['user_id'],
                    "type" => 'cancelreservation',
                    "restaurantId" => $invite['restaurant_id'],
                    'curDate' => $currentDate,
                    'username' => ucfirst($userName),
                    'restaurant_name' => $restaurantDetails->restaurant_name
                );
                $notificationJsonArray = array('username' => ucfirst($inviterName),
                    'restaurant_name' => $restaurantDetails->restaurant_name, 'user_id' => $invite['user_id'], 'restaurant_id' => $invite['restaurant_id']);
                $responsePubnub = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
            }
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
        } else {
            throw new \Exception('Type not found', 404);
        }

        if ($response) {
            return array(
                "response" => true
            );
        } else {
            return array(
                "response" => false
            );
        }
    }

    /*
     * Reservation Invitation CREATE rervation id and email address required
     */

    public function create($data) {
        $sendmail = false;
        $recievers = array();
        $hosturl = (isset($data['host_name']) && !empty($data['host_name']))?$data['host_name']:PROTOCOL.SITE_URL;
        try {
            if (empty($data)) {
                throw new \Exception("Invalid Parameters", 400);
            } else {
                $user_function = new UserFunctions();
                $userInvitationModel = new UserInvitation();
                $userReservation = new UserReservation();
                $userModel = new User();
                $userNotificationModel = new UserNotification();
                // get user id
                $session = $this->getUserSession();
                $isLoggedIn = $session->isLoggedIn();

                if ($isLoggedIn)
                    $userInvitationModel->user_id = $session->getUserId();
                else
                    $userInvitationModel->user_id = 0;

                if (isset($data['reservation_id']))
                    $userInvitationModel->reservation_id = $data['reservation_id'];
                else //
                    throw new \Exception("Reservation detail is invalid", 400);

                if (!isset($data['friendsEmailAddress']) && empty($data['friendsEmailAddress']))
                    throw new \Exception("Required valid friend email id", 400);

                $reservation = $userReservation->getUserReservationCurrent(array(
                            'columns' => array(
                                'restaurant_name',
                                'restaurant_id',
                                'time_slot',
                                'email',
                                'first_name',
                                'order_id',
                                'party_size',
                                'receipt_no',
                                'status',
                            ),
                            'where' => array(
                                'id' => $userInvitationModel->reservation_id
                            )
                        ))->getArrayCopy();
                $variables = array();
                $orderDatas = array();

                if ($reservation) {
                    $restaurant = new Restaurant();
                    $restaurantFunction = new \Restaurant\RestaurantDetailsFunctions();
                    $restDetails = $restaurant->getRestaurantSocialUrls($reservation['restaurant_id']);
                    
                    $restaurantAddress= $restaurantFunction->restaurantAddress($reservation['restaurant_id']);
                    
                    $restaurantName = $reservation['restaurant_name'];
                    $restaurantId = $reservation['restaurant_id'];
                    $reservationDate = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
                    $reservationTime = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'h:i A');
                    $user_email = $reservation['email'];
                    $host_name = ucfirst($reservation['first_name']);
                    if (isset($reservation['order_id']) && !empty($reservation['order_id']) && $reservation['order_id'] != NULL) {
                        $orderFunctions = new OrderFunctions ();
                        $orders = $this->getServiceLocator()->get("User\Controller\WebUserOrderController");
                        $orderDetails = $orders->get($reservation['order_id']);

                        $deliveryDateTime = explode(" ", $orderDetails['delivery_time']);
                        $orderDatas['order_details']['delivery_date'] = $deliveryDateTime[0];
                        $orderDatas['order_details']['delivery_time'] = $deliveryDateTime[1];
                        $orderDatas['order_details']['email'] = '';
                        $orderDatas['order_details']['items'] = $orderDetails['order_details'];
                        $orderDatas['order_details']['order_type'] = $orderDetails['order_Type'];
                        $orderDatas['order_details']['order_type1'] = '';
                        $orderDatas['order_details']['order_type2'] = '';
                        $orderDatas['order_details']['restaurant_id'] = $restaurantId;
                        $orderDatas['order_details']['special_instruction'] = (isset($orderDetails['special_checks'])) ? explode("||", $orderDetails['special_checks']) : '';
                        $orderDatas['order_details']['tax'] = $orderDetails['tax'];
                        $orderDatas['order_details']['tip_percent'] = $orderDetails['tip_percent'];
                        $finalPrice = $orderFunctions->calculatePrice($orderDatas ['order_details']);
                        $subtotal = $orderDetails['order_amount'];
                        $dealDiscount = $orderDetails['deal_discount'];
                        $tax = $orderDetails['tax'];
                        $tipAmount = $orderDetails['tip_amount'];
                        $total = $orderDetails['total_amount'];
                        $promocodeDiscount = $orderDetails['promocode_discount'];
                        $status = $orderFunctions->getOrderStatus($orderDatas['order_details']['delivery_date'], $orderDatas['order_details'] ['delivery_time'], $restaurantId);
                        $orderData = $orderFunctions->makeOrderForMail($orderFunctions->itemDetails, $restaurantId, $status, $orderDetails['order_amount']);
                        $orderDataInvite = $orderFunctions->makeOrderForMailInvite($orderFunctions->itemDetails, $restaurantId, $status, $orderDetails['order_amount']);
                    }
                } else {
                    throw new \Exception("Reservation id is not valid", 400);
                }

                if (!empty($userInvitationModel->user_id)) {
                    $joins_user = array();
                    $joins_user [] = array(
                        'name' => array(
                            'ua' => 'user_account'
                        ),
                        'on' => 'users.id = ua.user_id',
                        'columns' => array(
                            'first_name',
                            'last_name',
                        ),
                        'type' => 'inner'
                    );
                    $options = array(
                        'columns' => array(
                            'email'
                        ),
                        'where' => array('users.id' => $userInvitationModel->user_id),
                        'joins' => $joins_user,
                    );
                    $user = $userModel->getUserDetail($options);
                    $user_name = $user['first_name'];
                    if ($user_name == '') {
                        $useremail = explode("@", $user['email']);
                        $user_name = $useremail[0];
                    }
                } else {
                    $user_name = $host_name;
                    $user['email'] = $user_email;
                }

                if (!empty($data['friendsEmailAddress'])) {
                    $email1 = false;
                    $emailids = explode(',', $data['friendsEmailAddress']);
                    $mailText = $data['message'];
                    foreach ($emailids as $email) {
                        if (!empty($email)) {

                            if ($user['email'] == $email) {
                                continue;
                            }
                            $recievers = $email;

                            $userF = $userModel->getUserByEmail($email);
                            if ($userF) {
                                // $userF = $userF [0];
                                $to_id = $userF['id'];
                                // PubnubNotification::send_invition_to_pubnub($to_id,$restaurantId);
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
                            $accept_link = '';
                            $deny_link = '';
                            $accept_url_template = '';
                            $deny_url_template = '';
                           
                            if (!empty($userInvitationModel->user_id)) {
                                $userInvitationModel->message = '';
                                $userInvitationModel->to_id = $to_id;
                                $userInvitationModel->restaurant_id = $restaurantId;
                                $userInvitationModel->message .= isset($data['message']) ? $data['message'] : '';
                                /*
                                 * if (isset ( $data ['instruction'] )) { $userInvitationModel->message .= "," . $data ['instruction']; }
                                 */
                                $userInvitationModel->friend_email = $email;
                                $userInvitationModel->user_type = $user_type;
                                $userInvitationModel->created_on = date("Y-m-d H:i:s");
                                $userInvitationModel->msg_status = 0;

                                $get_user_invitation = $userInvitationModel->getUserInvitation(array(
                                    'columns' => array(
                                        'id',
                                        'msg_status'
                                    ),
                                    'where' => array(
                                        'reservation_id' => $userInvitationModel->reservation_id,
                                        'user_id' => $userInvitationModel->user_id,
                                        'friend_email' => $email
                                    )
                                ));

                                // #####################################################
                                $userInvitationModel->id = isset($get_user_invitation['id']) ? $get_user_invitation['id'] : '';
                                $userInvitationModel->msg_status = isset($get_user_invitation['msg_status']) ? $get_user_invitation['msg_status'] : 0;
                                $user_reservation_invitation = $userInvitationModel->createInvitation();

                                $config = $this->getServiceLocator()->get('Config');
                                $webUrl = $hosturl;
                                
                                $sender = 'notifications@munchado.com';
                                $sendername = "Munch Ado";
                                $recievers = array(
                                    $email
                                );

                                if (isset($reservation['order_id']) && !empty($reservation['order_id']) && $reservation['order_id'] != NULL) {
                                    $template = "is_buying_food_you_in";
                                    $layout = 'email-layout/default_new';
                                    $subject = "You, Us, Them, & Food";
                                    $userTokenAccept = base64_encode($email . '##' . $userInvitationModel->user_id . '##1##' . $userInvitationModel->reservation_id);
                                    $userTokenDeny = base64_encode($email . '##' . $userInvitationModel->user_id . '##2##' . $userInvitationModel->reservation_id);
//                                    $accept_link = WEB_URL . 'wapi/user/reservationaccepted/' . $user_reservation_invitation . "?token=" . $data['token']."&orderid=".$reservation['order_id'];
                                    $acceptUrl = $webUrl;
                                    $deny_link = WEB_URL . 'wapi/user/reservationdecline/' . $user_reservation_invitation . "?token=" . $data['token'] . "&orderid=" . $reservation['order_id'];
                                    //$accept_url_template = '<br><a href="' . $accept_link . '"><img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0"></a>';
                                    $accept_url_template = '<img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0">';
                                    $deny_url_template = '<a href="' . $deny_link . '"><img style="margin-top:8px; margin-left:15px;" src="' . TEMPLATE_IMG_PATH . 'deny-the-invitation.gif" alt="Deny the Invitation" width="209" height="33" border="0"></a>';

                                    $variables = array(
                                        'username' => ucfirst($host_name),
                                        'friendname' => ucfirst($friend_name),
                                        'peopleNo' => $reservation['party_size'],
                                        'restaurantName' => $restaurantName,
                                        'reservationDate' => $reservationDate,
                                        'reservationTime' => $reservationTime,
                                        'hostname' => $webUrl,
                                        'orderType' => "Pre-paid Reservation",
                                        'receiptNo' => $reservation['receipt_no'],
                                        'specialInstructions' => isset($orderDatas['order_details']['special_instruction']) ? implode(", ", $orderDatas['order_details']['special_instruction']) : '',
                                        'subtotal' => $subtotal,
                                        'dealDiscount' => $dealDiscount,
                                        'tax' => $tax,
                                        'tipAmount' => $tipAmount,
                                        'total' => $total,
                                        'cardType' => $orderDetails['card_type'],
                                        'cardNo' => $orderDetails['card_number'],
                                        'expiredOn' => $orderDetails['expired_on'],
                                        'acceptlink' => $accept_url_template,
                                        'acceptUrl' => $acceptUrl,
                                        'orderData' => $orderDataInvite,
                                        'promocodeDiscount' => $promocodeDiscount,
                                        'mailtext' => $mailText,
                                    );

                                    # Pubnub Notification to invitee(Friend) in case of pre-ordering with reservation

                                    $notificationMsg = ucfirst($host_name) . ' is paying for dinner at ' . $restaurantName . '! You in?';
                                    $channel = "mymunchado_" . $to_id;
                                    $notificationArray = array(
                                        "msg" => $notificationMsg,
                                        "channel" => $channel,
                                        "userId" => $to_id,
                                        "type" => 'reservation',
                                        "restaurantId" => $restaurantId,
                                        'curDate' => StaticOptions::getRelativeCityDateTime(array(
                                            'restaurant_id' => $restaurantId
                                        ))->format(StaticOptions::MYSQL_DATE_FORMAT),
                                        'username' => ucfirst($host_name),
                                        'restaurant_name' => $restaurantName,
                                        'reservation_id' => $userInvitationModel->reservation_id,
                                        'reservation_status' => $reservation['status']
                                    );
                                    $notificationJsonArray = array('reservation_status' => $reservation['status'], 'username' => ucfirst($host_name),
                                        'restaurant_name' => $restaurantName, 'user_id' => $to_id, 'restaurant_id' => $restaurantId, 'reservation_id' => $userInvitationModel->reservation_id);

                                    if ($user_reservation_invitation != false) {
                                        $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                                        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                                    }
                                } else {
                                    $template = "friends-reservation-Invitation";
                                     $subject = 'Someone Wants To Grab Food With You';
                                    if($hosturl==PROTOCOL.SITE_URL){
                                        $template = "friends-reservation-Invitation";                                       
                                        $layout = 'email-layout/default_new';
                                        $sender = array();
                                    }else{
                                        $template = "ma_friends-reservation-Invitation";                                        
                                        $layout = 'email-layout/ma_default';
                                        $sender = array('first_name'=>$restaurantName);
                                    }
                                    
                                    $userTokenAccept = base64_encode($email . '##' . $userInvitationModel->user_id . '##1##' . $userInvitationModel->reservation_id);
                                    $userTokenDeny = base64_encode($email . '##' . $userInvitationModel->user_id . '##2##' . $userInvitationModel->reservation_id);
                                    $acceptUrl = $hosturl;
                                    $deny_link = WEB_URL . $webUrl . 'wapi/user/reservationdecline/' . $user_reservation_invitation . "?token=" . $data['token'] . "&orderid=" . $reservation['order_id'];
                                    //$accept_url_template = '<br><a href="' . $accept_link . '"><img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0"></a>';
                                    $accept_url_template = '<img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0">';
                                    $deny_url_template = '<a href="' . $deny_link . '"><img style="margin-top:8px; margin-left:15px;" src="' . TEMPLATE_IMG_PATH . 'deny-the-invitation.gif" alt="Deny the Invitation" width="209" height="33" border="0"></a>';
                                    
                                    $variables = array(
                                        'username' => $host_name,
                                        'friendname' => $friend_name,
                                        'mailtext' => $mailText,
                                        'acceptlink' => $accept_url_template,
                                        'acceptUrl' => $acceptUrl,
                                        'restaurantName' => $restaurantName,
                                        'reservationDate' => $reservationDate,
                                        'reservationtime' => $reservationTime,
                                        'hostname' => $webUrl,
                                        'restaurant_address'=>$restaurantAddress,
                                        'restaurant_logo'=>$restDetails['restaurant_logo_name'],
                                        'facebook_url'=>$restDetails['facebook_url'],
                                        'twitter_url'=>$restDetails['twitter_url'],
                                        'instagram_url'=>$restDetails['instagram_url'],
                                        'rest_code'=>  strtolower($restDetails['rest_code'])
                                    );

                                    #Pubnub notification to invitee (friend) in case of reservation
                                    //$notificationMsg = 'Your friend ' . $user_name . ' would like you to join a reservation at ' . $restaurantName;
                                    $notificationMsg = ucfirst($host_name) . ' invited you to their reservation, you in?';
                                    $channel = "mymunchado_" . $to_id;
                                    $notificationArray = array(
                                        "msg" => $notificationMsg,
                                        "channel" => $channel,
                                        "userId" => $to_id,
                                        "type" => 'reservation',
                                        "restaurantId" => $restaurantId,
                                        'curDate' => StaticOptions::getRelativeCityDateTime(array(
                                            'restaurant_id' => $restaurantId
                                        ))->format(StaticOptions::MYSQL_DATE_FORMAT), 'username' => ucfirst($host_name),
                                        'restaurant_name' => $restaurantName
                                    );
                                    $notificationJsonArray = array('username' => ucfirst($host_name),
                                        'restaurant_name' => $restaurantName, 'user_id' => $to_id, 'restaurant_id' => $restaurantId, 'reservation_id' => $userInvitationModel->reservation_id);
                                    if ($user_reservation_invitation != false) {
                                        $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                                        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                                    }
                                }

                                ###################
                                $emailData = array(
                                    'receiver' => $recievers,
                                    'variables' => $variables,
                                    'subject' => $subject,
                                    'template' => $template,
                                    'layout' => $layout
                                );
                                ###################                                
                                if ($user_reservation_invitation != false && $hosturl==PROTOCOL.SITE_URL) {
                                    $user_function->sendMails($emailData,$sender);
                                }else{
                                    $user_function->sendMails($emailData,$sender);
                                }
                            } else {                               
                                $config = $this->getServiceLocator()->get('Config');
                                $webUrl = $hosturl;
                                $userInvitationModel->message = '';
                                $userInvitationModel->to_id = $to_id;
                                $userInvitationModel->restaurant_id = $restaurantId;
                                $userInvitationModel->message .= $data['message'];
                                /*
                                 * if (isset ( $data ['instruction'] )) { $userInvitationModel->message .= "," . $data ['instruction']; }
                                 */
                                $userInvitationModel->friend_email = $email;
                                $userInvitationModel->user_type = $user_type;
                                $userInvitationModel->created_on = date("Y-m-d H:i:s");
                                $userInvitationModel->msg_status = 0;

                                $get_user_invitation = $userInvitationModel->getUserInvitation(array(
                                    'columns' => array(
                                        'id',
                                        'msg_status'
                                    ),
                                    'where' => array(
                                        'reservation_id' => $userInvitationModel->reservation_id,
                                        'friend_email' => $email
                                    )
                                ));

                                // ##############################################
                                $userInvitationModel->id = isset($get_user_invitation['id']) ? $get_user_invitation['id'] : '';
                                if($userInvitationModel->id > 0 && $get_user_invitation['msg_status']==2){
                                   $userInvitationModel->msg_status = $get_user_invitation['msg_status'];
                                   $user_reservation_invitation = $userInvitationModel->createInvitation();
                                }
                                
                                $userTokenAccept = base64_encode($email . '##' . $userInvitationModel->reservation_id . '##1##' . $userInvitationModel->reservation_id);
                                $userTokenDeny = base64_encode($email . '##' . $userInvitationModel->reservation_id . '##2##' . $userInvitationModel->reservation_id);
                                $order_url = WEB_URL . '#!order';
                                $reserve_url = WEB_URL . '#!reserve';

                                $acceptUrl = $webUrl;
                                $deny_link = $webUrl . 'reservation/friend/deny/' . $userTokenDeny;
                                //$accept_url_template = '<br><a href="' . $accept_link . '"><img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0"></a>';
                                $accept_url_template = '<img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0">';
                                $deny_url_template = '<a href="' . $deny_link . '"><img style="margin-top:8px; margin-left:15px;" src="' . TEMPLATE_IMG_PATH . 'deny-the-invitation.gif" alt="Deny the Invitation" width="209" height="33" border="0"></a>';

                                $subject = 'Someone Wants To Grab Food With You';
                                if($hosturl==PROTOCOL.SITE_URL){
                                    $template = "friends-reservation-Invitation";                                       
                                    $layout = 'email-layout/default_new';
                                    $sender = array();
                                }else{
                                    $template = "ma_friends-reservation-Invitation";                                        
                                    $layout = 'email-layout/ma_default';
                                    $sender = array('first_name'=>$restaurantName);
                                }                                
                                
                                $variables = array(
                                    'username' => $host_name,
                                    'friendname' => $friend_name,
                                    'mailtext' => $mailText,
                                    'acceptlink' => $accept_url_template,
                                    'acceptUrl' => $acceptUrl,
                                    'restaurantName' => $restaurantName,
                                    'reservationDate' => $reservationDate,
                                    'reservationtime' => $reservationTime,
                                    'hostname' => $webUrl,
                                    'restaurant_logo'=>$restDetails['restaurant_logo_name'],
                                    'facebook_url'=>$restDetails['facebook_url'],
                                    'twitter_url'=>$restDetails['twitter_url'],
                                    'instagram_url'=>$restDetails['instagram_url'],
                                    'restaurant_address'=>$restaurantAddress,
                                    'rest_code'=>  strtolower($restDetails['rest_code'])
                                );
                                $emailData = array(
                                'receiver' => array($recievers),
                                'variables' => $variables,
                                'subject' => $subject,
                                'template' => $template,
                                'layout' => $layout
                                 );
                                
                                //pr($emailData,1);                               
                                $user_function->sendMails($emailData,$sender);                                                 
                            
                        } // end if email
                    } // end of foreach
                   
                } // end of if friendsEmailAddress
                // StaticOptions::sendMail('support@munchado.com', 'Munchado Support', array('bshah@hungrybuzz.info'), 'email-template/test', 'email-layout/default', array('name' => 'test'), 'Support mail');

               return array('response' => true);
            } // else end of if data empty
             return array('response' => false);
            }
        } catch (\Exception $ex) {
            return $this->sendError(array(
                        'error' => $ex->getMessage()
                            ), $ex->getCode());
        }
    }

// end of function

    public function get($token) {
        $userReservationInvitationModel = new UserInvitation();
        $token_coded = $token;
        if (empty($token_coded)) {
            $response = $this->redirect()->toUrl(WEB_URL);
            $response->sendHeaders();
        }
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }

        if ($userId) {
            $response = $userReservationInvitationModel->saveInviteResponse($token_coded);
            $this->_redirect()->toUrl(WEB_URL . 'myreservations');
        } else {
            $expiry_time = $this->expiry_time();
            $this->set_cookie('userToken', $token_coded, $expiry_time);
            $this->set_cookie('login_popup', 1, $expiry_time);
            $this->set_cookie('action_pending', 'reservation_invite', $expiry_time);
            $this->_redirect(WEB_URL);
        }
    }

}
