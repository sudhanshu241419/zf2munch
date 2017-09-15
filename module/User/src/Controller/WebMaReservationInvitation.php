<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserInvitation;
use User\Model\UserReservation;
use User\Model\User;
use MCommons\StaticOptions;
use User\Model\UserFriends;
use User\UserFunctions;
use User\Model\UserNotification;
use Restaurant\Model\Restaurant;
use Restaurant\OrderFunctions;

class WebUserReservationInvitationController extends AbstractRestfulController {

    public function create($data) {
        $host_url = (isset($data['host_name']) && !empty($data['host_name']))?$data['host_name']:"";
        $sendmail = false;
        $recievers = array();
        try {
            if (empty($data)) {
                throw new \Exception("Invalid Parameters", 400);
            }

            $userInvitationModel = new UserInvitation();
            $userReservation = new UserReservation();
            $userModel = new User();
            $userNotificationModel = new UserNotification();
            $reservationId = (isset($data['reservation_id']) && !empty($data['reservation_id'])) ? $data['reservation_id'] : false;
            if (!isset($data['friendsEmailAddress']) && empty($data['friendsEmailAddress'])) {
                throw new \Exception("Required valid friend email id", 400);
            }
            if ($reservationId) {
                $reservation = $userReservation->getUserReservationCurrent(array(
                            'columns' => array('restaurant_name', 'restaurant_id', 'time_slot', 'email', 'first_name', 'order_id', 'party_size', 'receipt_no', 'status'),
                            'where' => array('id' => $reservationId)))->getArrayCopy();
                $variables = array();
                $userInvitationModel->reservation_id=$reservationId;

                $restaurantName = $reservation['restaurant_name'];
                $restaurantId = $reservation['restaurant_id'];
                $reservationDate = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
                $reservationTime = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'h:i A');
                $user_email = $reservation['email'];
                $host_name = ucfirst($reservation['first_name']);
                if (!$host_name && $user_email) {
                    $email = explode("@", $user_email);
                    $host_name = $email[0];
                }


                if (!empty($data['friendsEmailAddress'])) {
                    $email1 = false;
                    $emailids = explode(',', $data['friendsEmailAddress']);

                    foreach ($emailids as $email) {
                        if (!empty($email)) {

                            if ($user['email'] == $email) {
                                continue;
                            }
                            $recievers = $email;

                            $userF = $userModel->getUserByEmail($email);
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
                            $deny_link = '';
                            $accept_url_template = '';
                            $deny_url_template = '';
                            $userInvitationModel->message = '';
                            $userInvitationModel->to_id = $to_id;
                            $userInvitationModel->restaurant_id = $restaurantId;
                            $userInvitationModel->message .= isset($data['message']) ? $data['message'] : '';

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

                            $userInvitationModel->id = isset($get_user_invitation['id']) ? $get_user_invitation['id'] : '';
                            $userInvitationModel->msg_status = isset($get_user_invitation['msg_status']) ? $get_user_invitation['msg_status'] : 0;
                            $user_reservation_invitation = $userInvitationModel->createInvitation();

                            
                            $webUrl = $host_url;
                            $mailText = $userInvitationModel->message;
                            $sender = 'notifications@munchado.com';
                            
                            $recievers = array(
                                $email
                            );

                            $template = "ma_friends-reservation-Invitation";
                            $subject = 'Someone Wants To Grab Food With You';
                            $userTokenAccept = base64_encode($email . '##' . $userInvitationModel->user_id . '##1##' . $userInvitationModel->reservation_id);
                            $userTokenDeny = base64_encode($email . '##' . $userInvitationModel->user_id . '##2##' . $userInvitationModel->reservation_id);
                            $acceptUrl = $webUrl;
                            $deny_link = WEB_URL . $webUrl . 'wapi/user/reservationdecline/' . $user_reservation_invitation . "?token=" . $data['token'] . "&orderid=" . $reservation['order_id'];
                            //$accept_url_template = '<br><a href="' . $accept_link . '"><img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0"></a>';
                            $accept_url_template = '<img style="margin-top:8px;" src="' . TEMPLATE_IMG_PATH . 'accept-the-invitation.gif" alt="Accept Invitation" width="209" height="33" border="0">';
                            $deny_url_template = '<a href="' . $deny_link . '"><img style="margin-top:8px; margin-left:15px;" src="' . TEMPLATE_IMG_PATH . 'deny-the-invitation.gif" alt="Deny the Invitation" width="209" height="33" border="0"></a>';
                            $layout = 'email-layout/ma_default';
                            $sender['first_name'] = $host_name;
                            $variables = array(
                                'username' => $host_name,
                                'friendname' => $friend_name,
                                'mailtext' => $mailText,
                                'acceptlink' => $accept_url_template,
                                'acceptUrl' => $acceptUrl,
                                'restaurantName' => $restaurantName,
                                'reservationDate' => $reservationDate,
                                'reservationtime' => $reservationTime,
                                'hostname' => $webUrl
                            );
//              
//                            $notificationMsg = ucfirst($host_name) . ' invited you to their reservation, you in?';
//                            $channel = "mymunchado_" . $to_id;
//                            $notificationArray = array(
//                                "msg" => $notificationMsg,
//                                "channel" => $channel,
//                                "userId" => $to_id,
//                                "type" => 'reservation',
//                                "restaurantId" => $restaurantId,
//                                'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                                    'restaurant_id' => $restaurantId
//                                ))->format(StaticOptions::MYSQL_DATE_FORMAT), 'username' => ucfirst($host_name),
//                                'restaurant_name' => $restaurantName
//                            );
//                            $notificationJsonArray = array('username' => ucfirst($host_name),
//                                'restaurant_name' => $restaurantName, 'user_id' => $to_id, 'restaurant_id' => $restaurantId, 'reservation_id' => $userInvitationModel->reservation_id);
//                            if ($user_reservation_invitation != false) {
//                                $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//                            }



                            $emailData = array(
                                'receiver' => $recievers,
                                'variables' => $variables,
                                'subject' => $subject,
                                'template' => $template,
                                'layout' => $layout
                            );

                            $user_function = new UserFunctions();
                            $user_function->sendMails($emailData,$sender);
                            
                        }
                    }
                } // end if email
            } // end of foreach
            return array(
                'response' => true
            );
        } catch (\Exception $ex) {
            return $this->sendError(array(
                        'error' => $ex->getMessage()
                            ), $ex->getCode());
        }
    }

}
