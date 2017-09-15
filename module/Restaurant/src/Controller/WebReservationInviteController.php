<?php

namespace Restaurant\Controller;

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

class WebReservationInviteController extends AbstractRestfulController {
   
    public function create($data) {

        $sendmail = false;
        $recievers = array();
        try {
            if (empty($data)) {
                throw new \Exception("Invalid Parameters", 400);
            } else {

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

                if (!isset($data['friend_email']) && empty($data['friend_email']))
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
                                'receipt_no','status'
                            ),
                            'where' => array(
                                'id' => $userInvitationModel->reservation_id
                            )
                        ))->getArrayCopy();
                                

                if ($reservation) {
                    $restaurantName = $reservation['restaurant_name'];
                    $restaurantId = $reservation['restaurant_id'];
                    $reservationDate = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
                    $reservationTime = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'h:i A');
                    $user_email = $reservation['email'];
                    $host_name = $reservation['first_name'];
                     if(isset($reservation['order_id']) && !empty($reservation['order_id']) && $reservation['order_id']!=NULL){
                        $orderFunctions = new OrderFunctions ();
                        $orders = $this->getServiceLocator()->get("User\Controller\WebUserOrderController");
                        $orderDetails = $orders->get($reservation['order_id']);  
                                             
                        $deliveryDateTime = explode(" ", $orderDetails['delivery_time']);
                        $orderDatas['order_details']['delivery_date']=$deliveryDateTime[0];
                        $orderDatas['order_details']['delivery_time']=$deliveryDateTime[1];
                        $orderDatas['order_details']['email']='';
                        $orderDatas['order_details']['items']=$orderDetails['order_details'];
                        $orderDatas['order_details']['order_type']=$orderDetails['order_Type'];
                        $orderDatas['order_details']['order_type1']='';
                        $orderDatas['order_details']['order_type2']='';
                        $orderDatas['order_details']['restaurant_id']=$restaurantId;
                        $orderDatas['order_details']['special_instruction']=(isset($orderDetails['special_checks']))?explode("||",$orderDetails['special_checks']):'';
                        $orderDatas['order_details']['tax']=$orderDetails['tax'];
                        $orderDatas['order_details']['tip_percent']=$orderDetails['tip_percent'];
                        $finalPrice = $orderFunctions->calculatePrice($orderDatas ['order_details']);                        
                        $subtotal=$orderDetails['order_amount'];
                        $tax=$orderDetails['tax'];
                        $tipAmount=$orderDetails['tip_amount'];
                        $total=$orderDetails['total_amount'];
                        $deal_discount = $orderDetails['deal_discount'];
                        $promocode_discount = $orderDetails['promocode_discount'];
                        $status = $orderFunctions->getOrderStatus($orderDatas['order_details']['delivery_date'], $orderDatas['order_details'] ['delivery_time'], $restaurantId);
                        $orderData= $orderFunctions->makeOrderForMail($orderFunctions->itemDetails, $restaurantId, $status,$orderDetails['order_amount']); 
                        $orderDataInvite = $orderFunctions->makeOrderForMailInvite($orderFunctions->itemDetails, $restaurantId, $status,$orderDetails['order_amount']);
                     }
                } else {
                    throw new \Exception("Reservation id is not valid", 400);
                }

                if (!empty($userInvitationModel->user_id)) {
                    $user = $userModel->getUserDetail(array(
                        'columns' => array(
                            'first_name',
                            'last_name',
                            'email'
                        ),
                        'where' => array(
                            'id' => $userInvitationModel->user_id
                        )
                    ));

                    $user_name = $user['first_name'];
                    if ($user_name == '') {
                        $useremail = explode("@", $user['email']);
                        $user_name = $useremail[0];
                    }
                } else {
                    $user_name = $host_name;
                    $user['email'] = $user_email;
                }

                if (!empty($data['friend_email'])) {

                    $email1 = false;
                    $emailids = $data['friend_email']; //explode(',', $data['friend_email']);

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
                                $ms = isset($data['message']) ? $data['message'] : '';
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
                                $userTokenAccept = base64_encode($email . '##' . $userInvitationModel->user_id . '##1##' . $userInvitationModel->reservation_id);
                                $userTokenDeny = base64_encode($email . '##' . $userInvitationModel->user_id . '##2##' . $userInvitationModel->reservation_id);
                                $config = $this->getServiceLocator()->get('Config');
                                $webUrl = PROTOCOL.$config['constants']['web_url'];
                                $acceptUrl = $webUrl;
                                $deny_link = $webUrl . '/reservation/friend/deny/' . $userTokenDeny;
                                //$accept_url_template = '<br><a href="' . $accept_link . '" target="_blank"><img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0"></a>';
                                $accept_url_template = '<img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0">';
                                // $deny_url_template = '<a href="' . $deny_link . '"><img style="margin-top:8px; margin-left:15px;" src="' . TEMPLATE_IMG_PATH . 'deny-the-invitation.gif" alt="Deny the Invitation" width="209" height="33" border="0"></a>';
                                $mailText = $ms;
                                $sender = 'notifications@munchado.com';
                                $sendername = "Munch Ado";
                                $recievers = array(
                                    $email
                                );
                                if(isset($reservation['order_id']) && !empty($reservation['order_id']) && $reservation['order_id']!=NULL){
                                    $template = "is_buying_food_you_in";
                                    $layout = 'email-layout/default_new';
                                    $subject = "You, Us, Them, & Food";
                                    $userTokenAccept = base64_encode($email . '##' . $userInvitationModel->user_id . '##1##' . $userInvitationModel->reservation_id);
                                    $userTokenDeny = base64_encode($email . '##' . $userInvitationModel->user_id . '##2##' . $userInvitationModel->reservation_id);
                                    $acceptUrl = $webUrl;
                                    $deny_link = WEB_URL . 'wapi/user/reservationdecline/' . $user_reservation_invitation."?token=" . $data['token']."&orderid=".$reservation['order_id'];
                                    //$accept_url_template = '<br><a href="' . $accept_link . '" target="_blank"><img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0"></a>';
                                    $accept_url_template = '<img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0">';
                                    $deny_url_template = '<a href="'.$deny_link.'"><img style="margin-top:8px; margin-left:15px;" src="' . TEMPLATE_IMG_PATH . 'deny-the-invitation.gif" alt="Deny the Invitation" width="209" height="33" border="0"></a>';
                                
                                    $variables = array(
                                    'username' => ucfirst($host_name),
                                    'friendname' => ucfirst($friend_name),
                                    'peopleNo'=>$reservation['party_size'],                                    
                                    'restaurantName' => $restaurantName,
                                    'reservationDate' => $reservationDate,
                                    'reservationTime' => $reservationTime,
                                    'host_name' => $webUrl,
                                    'orderType'=>"Pre-paid Reservation",
                                    'receiptNo'=>$reservation['receipt_no'],
                                    'specialInstructions'=>  isset($orderDatas['order_details']['special_instruction'])?implode(", ", $orderDatas['order_details']['special_instruction']):'',
                                    'subtotal'=>$subtotal,
                                    'tax'=>$tax,
                                    'tipAmount'=>$tipAmount,
                                    'total'=>$total,
                                    'cardType'=>$orderDetails['card_type'],
                                    'cardNo'=>$orderDetails['card_number'],
                                    'expiredOn'=>$orderDetails['expired_on'],
                                    'acceptlink'=>$accept_url_template,
                                    'acceptUrl'=>$acceptUrl,
                                    'orderData'=>$orderData, 
                                    'dealDiscount'=>$deal_discount,
                                    'promocodeDiscount'=>$promocode_discount,
                                    'mailtext' => $mailText,
                                    );
                                    # Pubnub Notification to invitee(Friend) in case of pre-ordering with reservation

                                     $notificationMsg = ucfirst($host_name).' is paying for dinner at '.$restaurantName.'! You in?';
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
                                        'restaurant_name'=>ucfirst($restaurantName),
                                         'reservation_id'=>$userInvitationModel->reservation_id,
                                         'reservation_status'=>$reservation['status']
                                     );
                                     //$notificationJsonArray = array('reservation_id'=>$reservationId,'time'=>$time,'date'=>$date,'no_of_people'=>$partySize,'username'=>ucfirst($userName),'restaurant_id' => $invite['restaurant_id'],"user_id" => $invite['user_id'],'restaurant_name'=>$restaurantDetails->restaurant_name);
                                     $notificationJsonArray = array('reservation_id'=>$userInvitationModel->reservation_id,'reservation_status'=>$reservation['status'],'user_id'=>$to_id,'username'=>ucfirst($host_name),'restaurant_id' => $restaurantId,'reservation_id'=>$userInvitationModel->reservation_id,'restaurant_name'=>ucfirst($restaurantName));
                                     if ($user_reservation_invitation != false) {
                                        $response = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                                        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                                     }
                                }else{
                                    $template = "friends-reservation-Invitation";                                    
                                    $subject = 'Someone Wants To Grab Food With You';
                                    $layout = 'email-layout/default_new';
                                    $variables = array(
                                        'username' => $host_name,
                                        'friendname' => $friend_name,
                                        'mailtext' => $mailText,
                                        'acceptlink' => $accept_url_template,
                                        'acceptUrl'=> $acceptUrl,
                                        'restaurantName' => $restaurantName,
                                        'reservationDate' => $reservationDate,
                                        'reservationtime' => $reservationTime,
                                        'hostname' => $webUrl
                                    );
                                    
                                    #Pubnub notification to invitee (friend) in case of reservation
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
                                      ))->format(StaticOptions::MYSQL_DATE_FORMAT),
                                        'username'=>ucfirst($host_name),
                                        'restaurant_name'=>ucfirst($restaurantName)
                                        
                                    );
                                    //$notificationJsonArray = array('user_id'=>$to_id,'username'=>ucfirst($host_name),'restaurant_id' => $restaurantId,'reservation_id'=>$userInvitationModel->reservation_id,'restaurant_name'=>ucfirst($restaurantName));
                                    $notificationJsonArray = array('user_id'=>$to_id,'username'=>ucfirst($host_name),'restaurant_id' => $restaurantId,'restaurant_name'=>ucfirst($restaurantName),'reservation_id'=>$userInvitationModel->reservation_id);
                                    if ($user_reservation_invitation != false) {
                                       $response = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
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
                                    $user_function = new UserFunctions();
                                    if ($user_reservation_invitation != false) {
                                        $user_function->sendMails($emailData);
                                    }
                                    
                              } else {
                                $ms1 = isset($data['message']) ? $data['message'] : '';
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
                                        'friend_email' => $email
                                    )
                                ));

                                // ##############################################
                                $userInvitationModel->id = isset($get_user_invitation['id']) ? $get_user_invitation['id'] : '';

                                $user_reservation_invitation = $userInvitationModel->createInvitation();

                                $userTokenAccept = base64_encode($email . '##' . 0 . '##1##' . $userInvitationModel->reservation_id);
                                $userTokenDeny = base64_encode($email . '##' . 0 . '##2##' . $userInvitationModel->reservation_id);
                                $config = $this->getServiceLocator()->get('Config');
                                $webUrl = PROTOCOL.$config['constants']['web_url'];
                                $acceptUrl = $webUrl;
                                $deny_link = 'http://' . $webUrl . '/reservation/friend/deny/' . $userTokenDeny;
                                $accept_url_template = '<img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0">';
                                // $deny_url_template = '<a href="' . $deny_link . '"><img style="margin-top:8px; margin-left:15px;" src="' . TEMPLATE_IMG_PATH . 'deny-the-invitation.gif" alt="Deny the Invitation" width="209" height="33" border="0"></a>';
                                $mailText = $ms1;
                                $sender = 'notifications@munchado.com';
                                $sendername = "Munch Ado";
                                $recievers = array(
                                    $email
                                );
                                
                                if(isset($reservation['order_id']) && !empty($reservation['order_id']) && $reservation['order_id']!=NULL){
                                    $template = "is_buying_food_you_in";
                                    $layout = 'email-layout/default_new';
                                    $subject = "You, Us, Them, & Food";

                                    $userTokenAccept = base64_encode($email . '##' . $userInvitationModel->user_id . '##1##' . $userInvitationModel->reservation_id);
                                    $userTokenDeny = base64_encode($email . '##' . $userInvitationModel->user_id . '##2##' . $userInvitationModel->reservation_id);
                                    $acceptUrl = $webUrl;
                                    $deny_link = WEB_URL . 'wapi/user/reservationdecline/' . $user_reservation_invitation."?token=" . $data['token']."&orderid=".$reservation['order_id'];
                                    $accept_url_template = '<img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0">';
                                    $deny_url_template = '<a href="'.$deny_link.'"><img style="margin-top:8px; margin-left:15px;" src="' . TEMPLATE_IMG_PATH . 'deny-the-invitation.gif" alt="Deny the Invitation" width="209" height="33" border="0"></a>';
                                
                                    $variables = array(
                                    'username' => ucfirst($host_name),
                                    'friendname' => ucfirst($friend_name),
                                    'peopleNo'=>$reservation['party_size'],                              
                                    'restaurantName' => $restaurantName,
                                    'reservationDate' => $reservationDate,
                                    'reservationTime' => $reservationTime,
                                    'host_name' => $webUrl,
                                    'orderType'=>$orderDetails['order_Type'],
                                    'receiptNo'=>$reservation['receipt_no'],
                                    'specialInstructions'=>  isset($orderDatas['order_details']['special_instruction'])?implode(", ", $orderDatas['order_details']['special_instruction']):'',
                                    'subtotal'=>$subtotal,
                                    'dealDiscount'=>$deal_discount,
                                    'tax'=>$tax,
                                    'tipAmount'=>$tipAmount,
                                    'total'=>$total,
                                    'cardType'=>$orderDetails['card_type'],
                                    'cardNo'=>$orderDetails['card_number'],
                                    'expiredOn'=>$orderDetails['expired_on'],
                                    'acceptlink'=>$accept_url_template,  
                                    'acceptUrl'=>$acceptUrl,
                                    'orderData'=>$orderData, 
                                    'promocodeDiscount'=>$promocode_discount,
                                    'mailtext' => $mailText
                                    );
                                }else{
                                    $template = "friends-reservation-Invitation";
                                    $subject = 'Someone Wants To Grab Food With You';
                                    $layout = 'email-layout/default_new';
                                    $variables = array(
                                        'username' => $host_name,
                                        'friendname' => $friend_name,
                                        'mailtext' => $mailText,
                                        'acceptlink' => $accept_url_template,
                                        'acceptUrl'=>$acceptUrl,
                                        'restaurantName' => $restaurantName,
                                        'reservationDate' => $reservationDate,
                                        'reservationtime' => $reservationTime,
                                        'hostname' => $webUrl
                                    );
                                }
                                ###################
                                $emailData = array(
                                    'receiver' => $recievers,
                                    'variables' => $variables,
                                    'subject' => $subject,
                                    'template' => $template,
                                    'layout' => $layout
                                );
                                $user_function = new UserFunctions();
                                if ($user_reservation_invitation != false) {
                                   // $user_function->sendMails($emailData);
                                }
                            }
                        } // end if email
                    } // end of foreach
                    return array(
                        'response' => true
                    );
                } // end of if friendsEmailAddress
                // StaticOptions::sendMail('support@munchado.com', 'Munchado Support', array('bshah@hungrybuzz.info'), 'email-template/test', 'email-layout/default', array('name' => 'test'), 'Support mail');

                return array(
                    'response' => false
                );
            } // else end of if data empty
        } catch (\Exception $ex) {
            return $this->sendError(array(
                        'error' => $ex->getMessage()
                            ), $ex->getCode());
        }
    }

// end of function
}
