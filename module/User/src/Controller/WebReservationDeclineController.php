<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\User;
use User\Model\UserInvitation;
use User\Model\UserReservation;
use Zend\Db\Sql\Predicate\Expression;
use MCommons\StaticOptions;
use User\Model\UserNotification;

class WebReservationDeclineController extends AbstractRestfulController {

    public function get($id) {
        $action = false;
        $orderId = $this->getQueryParams('orderid', "");
        $session = $this->getUserSession();
        $userModel = new User();
        $userNotificationModel = new UserNotification();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
            //$userDetail = $session->getUserDetail();          
        }
        $reservationModel = new UserReservation();
        if (isset($orderId) && !empty($orderId)) {
            $reservation = $reservationModel->getUserReservationCurrent(array(
                        'columns' => array(
                            'restaurant_name',
                            'restaurant_id',
                            'time_slot',
                            'order_id',
                            'user_id',
                            'email',
                            'party_size',
                            'receipt_no',
                        ),
                        'where' => array(
                            'order_id' => $orderId
                        )
                    ))->getArrayCopy();

            $reservationDate = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
            $reservationTime = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'h:i A');
            
            $recievers = array(
                $reservation ['email']
            );
            if (isset($reservation['user_id']) && !empty($reservation['user_id']) && $reservation['user_id'] != NULL) {
                $joins = array();
                    $joins [] = array(
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
                    $userOptions = array(
                    'columns' => array(
                        'email'
                    ),
                    'where' => array('users.id' => $reservation['user_id']),
                    'joins' => $joins,
                );
                $recieverDetails = $userModel->getUserDetail($userOptions)->getArrayCopy();
                $recieverUserName = ucfirst($recieverDetails['first_name']);
            } elseif (isset($reservation ['email']) && !empty($reservation['email']) && $reservation['email'] != NULL) {
                $emailArray = explode("@", $reservation['email']);
                $recieverUserName = ucfirst($emailArray[0]);
            } else {
                $recieverUserName = "";
            }
        }

        $reservationInvitationModel = new UserInvitation();
        $reservationInvitationModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'reservation_id',
                'to_id',
                'friend_email',
            ),
            'where' => array(
                'msg_status' => 0,
                'id' => $id
            )
        );
        $reservationIdDetails = $reservationInvitationModel->find($options)->toArray();

        if ($reservationIdDetails) {


            $reservationModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
            $options = array(
                'columns' => array(
                    'id',
                    'status'
                ),
                'where' => new Expression('(status = 1 or status = 4) AND id=' . $reservationIdDetails[0]['reservation_id']),
            );
            $reservationStatusDetails = $reservationModel->find($options)->toArray();

            if ($reservationStatusDetails) {
                $action = true;
            }
        }

        if ($action) {
            $userFunctions = new UserFunctions();
            $status = $userFunctions->reservationInvitationDecline($id);
            $config = $this->getServiceLocator()->get('Config');
            $webUrl = PROTOCOL . $config ['constants'] ['web_url'];
            if ($status) {
                if (isset($userId) && $userId == $reservationIdDetails[0]['to_id']) {
                    $userOptions = array(
                        'columns' => array(
                            'first_name',
                            'last_name',
                            'email',
                        ),
                        'where' => array('id' => $reservationIdDetails[0]['to_id']),
                    );

                    if ($userModel->getUserDetail($userOptions)) {
                        $inviteeDetails = $userModel->getUserDetail($userOptions)->getArrayCopy();
                    }

                    if (isset($inviteeDetails) && !empty($inviteeDetails)) {
                        if ($inviteeDetails['first_name']) {
                            $invitee = ucfirst($inviteeDetails['first_name']);
                        } elseif ($userDetails['email']) {
                            $inviteeArray = explode("@", $inviteeDetails['email']);
                            $invitee = ucfirst($inviteeArray[0]);
                        } else {
                            $invitee = "";
                        }
                    } else {
                        $friendEmailArray = explode("@", $reservationIdDetails[0]['friend_email']);
                        $invitee = $friendEmailArray[0];
                    }
                    if (isset($orderId) && !empty($orderId)) {

                        $variables = array(
                            'username' => $recieverUserName,
                            'invitee' => $invitee,
                            'peopleNo' => $reservation['party_size'],
                            'restaurantName' => $reservation['restaurant_name'],
                            'reservationDate' => $reservationDate,
                            'reservationtime' => $reservationTime,
                        );
                        $template = "Turned_Down_For_What";
                        $subject = $recieverUserName.' Declined Your Generous Invite';
                        $emailData = array(
                            'receiver' => $recievers,
                            'variables' => $variables,
                            'subject' => $subject,
                            'template' => $template,
                            'layout' => 'email-layout/default_new'
                        );

                        ###################
                        $user_function = new UserFunctions();
                        $user_function->sendMails($emailData);
                        
                        #Pubnub Notification : to host from invitee(friend)
                        if (isset($reservation['user_id']) && !empty($reservation['user_id']) && $reservation['user_id'] != NULL) {
                            
                            $notificationMsg = $invitee." declined your generous pre-paid reservation invitation to ".$reservation['restaurant_name'].". Drats.";
                            $channel = "mymunchado_" . $reservation['user_id'];
                            $notificationArray = array(
                                "msg" => $notificationMsg,
                                "channel" => $channel,
                                "userId" => $reservation['user_id'],
                                "type" => 'reservation',
                                "restaurantId" => $reservation['restaurant_id'],
                                'curDate' => StaticOptions::getRelativeCityDateTime(array(
                                    'restaurant_id' => $reservation['restaurant_id']
                                ))->format(StaticOptions::MYSQL_DATE_FORMAT),'invitee'=>$invitee,'restaurant_name'=>$reservation['restaurant_name']
                            );
                            $notificationJsonArray = array('restaurant_id' => $reservation['restaurant_id'],'invitee_id'=>$reservationIdDetails[0]['to_id'],'invitee'=>$invitee,'restaurant_name'=>$reservation['restaurant_name']);
                            $response = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                        }
                    }
                    $response = $this->redirect()->toUrl($webUrl . DS . 'myreservations');
                    $response->sendHeaders();
                } else {
                    $userOptions = array(
                        'columns' => array(
                            'first_name',
                            'last_name',
                            'email',
                        ),
                        'where' => array('id' => $reservationIdDetails[0]['to_id']),
                    );
                    $userModel = new User();
                    if ($userModel->getUserDetail($userOptions)) {
                        $inviteeDetails = $userModel->getUserDetail($userOptions)->getArrayCopy();
                        if ($inviteeDetails['first_name']) {
                            $invitee = ucfirst($inviteeDetails['first_name']);
                        } elseif ($inviteeDetails['email']) {
                            $inviteeArray = explode("@", $inviteeDetails['email']);
                            $invitee = ucfirst($inviteeArray[0]);
                        } else {
                            $invitee = "";
                        }
                    } else {
                        $friendEmailArray = explode("@", $reservationIdDetails[0]['friend_email']);
                        $invitee = $friendEmailArray[0];
                    }
                    if (isset($orderId) && !empty($orderId)) {
                        $variables = array(
                            'username' => $recieverUserName,
                            'invitee' => $invitee,
                            'peopleNo' => $reservation['party_size'],
                            'restaurantName' => $reservation['restaurant_name'],
                            'reservationDate' => $reservationDate,
                            'reservationtime' => $reservationTime,
                        );
                        $template = "Turned_Down_For_What";
                        $subject = $recieverUserName.' Declined Your Generous Invite';
                        $emailData = array(
                            'receiver' => $recievers,
                            'variables' => $variables,
                            'subject' => $subject,
                            'template' => $template,
                            'layout' => 'email-layout/default_new'
                        );

                        ###################
                        $user_function = new UserFunctions();
                        $user_function->sendMails($emailData);
                        #Pubnub Notification : to host from invitee(friend)
                        if (isset($reservation['user_id']) && !empty($reservation['user_id']) && $reservation['user_id'] != NULL) {
                            
                            $notificationMsg = $invitee." declined your generous pre-paid reservation invitation to ".$reservation['restaurant_name'].". Drats.";
                            $channel = "mymunchado_" . $reservation['user_id'];
                            $notificationArray = array(
                                "msg" => $notificationMsg,
                                "channel" => $channel,
                                "userId" => $reservation['user_id'],
                                "type" => 'reservation',
                                "restaurantId" => $reservation['restaurant_id'],
                                'curDate' => StaticOptions::getRelativeCityDateTime(array(
                                    'restaurant_id' => $reservation['restaurant_id']
                                ))->format(StaticOptions::MYSQL_DATE_FORMAT),'invitee'=>$invitee,'restaurant_name'=>$reservation['restaurant_name']
                            );
                            $notificationJsonArray = array('restaurant_id' => $reservation['restaurant_id'],'invitee_id'=>$reservationIdDetails[0]['to_id'],'invitee'=>$invitee,'restaurant_name'=>$reservation['restaurant_name']);
                            $response = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                        }
                    }
                    $response = $this->redirect()->toUrl($webUrl);
                    $response->sendHeaders();
                }
            } else {
                $response = $this->redirect()->toUrl($webUrl);
                $response->sendHeaders();
            }
        } else {
            $config = $this->getServiceLocator()->get('Config');
            $webUrl = PROTOCOL . $config ['constants'] ['web_url'];
            $response = $this->redirect()->toUrl($webUrl . DS . 'myreservations');
            $response->sendHeaders();
        }
    }

}
