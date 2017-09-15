<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReservation;
use Restaurant\ReservationFunctions;
use User\Model\UserInvitation;
use User\UserFunctions;
use MCommons\StaticOptions;
use Restaurant\Model\Restaurant;
use User\Model\UserNotification;
use User\Model\UserDashboardNotification;
use User\Model\User;
use User\Model\PointSourceDetails;
use User\Model\UserPoint;
use Restaurant\Model\RestaurantAccounts;
use User\Model\UserSetting;
use Restaurant\Model\RestaurantDetail;
use Restaurant\Model\RestaurantNotificationSettings;
use Bookmark\Model\RestaurantBookmark;
use User\Model\UserFriends;
use Restaurant\Model\RestaurantDineinCalendars;
use User\Model\UserReferrals;

class ReservationController extends AbstractRestfulController {

    public function get($reservation_id = 0) {
        if ($reservation_id == 0) {
            throw new \Exception('Reservation id is not valid', 404);
        }

        $user_function = new UserFunctions ();
        $reservationModel = new UserReservation ();
        $user_invitation = new UserInvitation();
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $reservationStatus = isset($config ['constants'] ['reservation_status']) ? $config ['constants'] ['reservation_status'] : array();

        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $friendId = $this->getQueryParams('friendid', false);
        if ($isLoggedIn) {
            if ($friendId) {
                $userId = $friendId;
            } else {
                $userId = $session->getUserId();
            }
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        $reservationIds = array($reservation_id);
        $upcomingCondition = array(
            'reservationIds' => $reservationIds,
            'currentDate' => $currentDate,
            'userId' => $userId,
            'status' => array(
                $reservationStatus ['upcoming'],
                $reservationStatus ['rejected'],
                $reservationStatus ['confirmed'],
                $reservationStatus ['archived'],
                $reservationStatus ['canceled'],
            ),
            'orderBy' => 'time_slot ASC'
        );
        if ($reservationModel->getReservationDetailForMob($upcomingCondition)) {
            $response = $reservationModel->getReservationDetailForMob($upcomingCondition);
        } else {
            throw new \Exception('Reservation not found');
        }

        $reservationDetail = $this->getInvitationDetail($reservation_id, $response[0], $userId);
        $reservationDetail['reservation_invited_by_friend'] = $this->reservation_invited_by_friend($reservation_id, $response[0], $userId);
        $my_invitation_record = $user_invitation->getAllUserInvitation(array(
            'columns' => array(
                'id',
                'to_id',
                'friend_email',
                'msg_status'
            ),
            'where' => array(
                'user_id' => $userId,
                'reservation_id' => $reservation_id
            ),
            'order' => array(
                'created_on DESC'
            )
        ));
        $invitedUserStatus = '';
        if ($my_invitation_record) {
            $invitedUserStatus = $user_function->InvitationFriendList($my_invitation_record);
            $reservationDetail['invited_user'] = $invitedUserStatus;
        }

        $reservationDate = strtotime($reservationDetail['reservation_date']);
        if ($reservationDate >= strtotime($currentDate)) {
            if ($reservationDetail['status'] == 1) {
                $reservationDetail['status'] = 'upcoming';
            } elseif ($reservationDetail['status'] == 3) {
                $reservationDetail['status'] = 'rejected';
            } elseif ($reservationDetail['status'] == 4) {
                $reservationDetail['status'] = 'confirmed';
            } elseif ($reservationDetail['status'] == 2) {
                $reservationDetail['status'] = 'canceled';
            }
        } else {
            if ($reservationDetail['status'] == 2) {
                $reservationDetail['status'] = 'canceled';
            } else {
                $reservationDetail['status'] = 'archived';
            }
        }

        if ($reservationDetail['inactive'] == 1 || $reservationDetail['closed'] == 1) {
            $reservationDetail['is_restaurant_exist'] = 0;
        } else {
            $reservationDetail['is_restaurant_exist'] = 1;
        }
        $reservationDetail['rest_code'] = strtolower($reservationDetail['rest_code']);
        $reservationDetail['user_instruction'] = rtrim(str_replace("||", ', ', $reservationDetail['user_instruction']), ', ');
        $reservationDetail['restaurant_address'] = $reservationDetail['address'] . ", " . $reservationDetail['city_name'] . ", " . $reservationDetail['state_code'] . " " . $reservationDetail['zipcode'];
        unset($reservationDetail['inactive'], $reservationDetail['closed'], $reservationDetail['address'], $reservationDetail['city_name'], $reservationDetail['state_code'], $reservationDetail['zipcode']);
        $orderId = $response[0]['order_id'];
        $userOrderModel = new \User\Model\UserOrder();
        $isReviewed = $userOrderModel->getOrderReview($reservation_id);
        $reservationDetail['is_reviewed'] = isset($isReviewed['is_review']) ? intval($isReviewed['is_review']) : 0;
        $reservationDetail['review_id'] = isset($isReviewed['review_id']) ? intval($isReviewed['review_id']) : 0;
        $orderDetails = NULL;
        if (isset($orderId) && !empty($orderId) && $orderId != NULL) {
            $orders = $this->getServiceLocator()->get("User\Controller\OrderPlaceController");
            $orderDetails = $orders->get($orderId);
        }
        $reservationDetail['order_details'] = $orderDetails;

        return $reservationDetail;
    }

    public function getInvitationDetail($reservationId, $reservationDetail, $userId = false) {
        $reservationInvitationModel = new UserInvitation();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'u' => 'users'
            ),
            'on' => 'u.id = user_reservation_invitation.to_id',
            'columns' => array(
                'invited_name' => 'first_name',
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'ur' => 'user_reservations'
            ),
            'on' => 'ur.id = user_reservation_invitation.reservation_id',
            'columns' => array(
                'invitation_by_phone' => 'phone',
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'id',
                'invited_id' => 'to_id',
                'invitation_by' => 'user_id',
                'invited_email' => 'friend_email',
                'user_message' => 'message',
                'msg_status'
            ),
            'where' => array(
                'user_reservation_invitation.reservation_id' => $reservationId,
                'user_reservation_invitation.to_id' => $userId,
                'user_reservation_invitation.msg_status' => '0'
            ),
            'joins' => $joins
        );

        $reservationInvitationModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        if ($reservationInvitationModel->find($options)->toArray()) {
            $invitationResponse = $reservationInvitationModel->find($options)->toArray();
            $refineInvitationList = $this->refineInvitationResponse($invitationResponse, $reservationDetail);
            $reservationDetail['reservation_is_invited'] = 1;
            $reservationDetail['invitation_from_friend'] = $refineInvitationList['invitation_from_friend'];
            $reservationDetail['invitation_description'] = $refineInvitationList['invitation_description'];
            $reservationDetail['invitation'] = $invitationResponse;
        } else {
            $reservationDetail['reservation_is_invited'] = 0;
            $reservationDetail['invitation'] = array();
            $reservationDetail['invitation_from_friend'] = 0;
            $reservationDetail['invitation_description'] = "";
        }

        return $reservationDetail;
    }

    public function refineInvitationResponse($data, $reservationDetail) {

        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $userId = $session->getUserId();
        $userFunction = new UserFunctions();
        $user = new User();
        $invitation_description = "";
        foreach ($data as $key => $val) {

            if ($val['invitation_by'] == $userId) {
                $invitation_from_friend = 0;
            } else {
                $invitation_from_friend = 1;
            }

            if ($invitation_from_friend == 1) {
                $typeOfMeal = $userFunction->getMealSlot(StaticOptions::getFormattedDateTime($reservationDetail['reservation_date'], 'Y-m-d H:i:s', 'H:i:s'));
                $invitation_description = $typeOfMeal . " invitation from " . $reservationDetail['first_name'];
            }
        }
        $data['invitation_from_friend'] = $invitation_from_friend;
        $data['invitation_description'] = $invitation_description;
        return $data;
    }

    public function update($id, $data) {

        $userReservatioModel = new UserReservation ();
        $userReservationInviteModel = new UserInvitation ();
        $restaurantModel = new Restaurant ();
        $restaurantAccountModel = new RestaurantAccounts ();
        $userModel = new User ();
        $pointSourceModel = new PointSourceDetails ();
        $userPointModel = new UserPoint ();
        $userNotificationModel = new UserNotification ();
        $dashboardNotificationModel = new UserDashboardNotification ();
        $commonFunctiion = new \MCommons\CommonFunctions();
        $session = $this->getUserSession();
        $user_function = new UserFunctions ();
        $userSetting = new UserSetting ();
        $restaurantNotificationSettings = new RestaurantNotificationSettings ();
        $reservationFunction = new ReservationFunctions();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $pointID = isset($config ['constants'] ['point_source_detail']) ? $config ['constants'] ['point_source_detail'] : array();
        $isLoggedIn = $session->isLoggedIn();
        $config = $this->getServiceLocator()->get('Config');
        $webUrl = PROTOCOL . $config ['constants'] ['web_url'];

        if ($isLoggedIn) {
            $userReservatioModel->user_id = $session->getUserId();
            $userModel->id = $userReservatioModel->user_id;
        }

        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $pointID = isset($config ['constants'] ['point_source_detail']) ? $config ['constants'] ['point_source_detail'] : array();

        $userReservatioModel->id = $id;
        $token = $data ['token'];

        if (!isset($data ['email'])) {
            throw new \Exception("Email id is required", 405);
        }
        if (!isset($data ['phone'])) {
            throw new \Exception("Phone number is required", 405);
        }
        if (!isset($data ['time_slot'])) {
            throw new \Exception("Date is required", 405);
        }
        if (!isset($data ['reserved_seats'])) {
            throw new \Exception("Requested seats is required", 405);
        }
        if (!isset($data ['first_name'])) {
            throw new \Exception("First name is required", 405);
        }
        $data['time_slot'] = date("Y-m-d H:i", strtotime($data ['time_slot']));
        $data ['time_slot'] = date('Y-m-d H:i', strtotime($data ['time_slot']));
        $timeslot = explode(" ", $data ['time_slot']);
        $data ['date'] = $timeslot[0];
        $data['time'] = $timeslot[1];
        //Get Dinein Detail of Restaurant
        $restaurantDineinCalendars = new RestaurantDineinCalendars();
        $options = array("where" => array("restaurant_id" => $data['restaurant_id']));
        $restaurantAccountDetails = $restaurantAccountModel->getRestaurantAccountDetail(array('where' => array(
                'restaurant_id' => $data ['restaurant_id'],
                'status' => '1'
            )
        ));
        $dineinCalendarsDetail = $restaurantDineinCalendars->findRestaurantDineinDetail($options);

        if (isset($dineinCalendarsDetail) && !empty($dineinCalendarsDetail) && $restaurantAccountDetails) {
            if (isset($data['time']) && !empty($data['time'])) {
                $requested_time = strtotime($data['time']);
            } elseif (isset($data['time_slot']) && !empty($data['time_slot'])) {
                $dateTimeArray = explode(" ", $data['time_slot']);
                $requested_time = $dateTimeArray[1];
            } else {
                $requested_time = "00:00";
            }
            $requested_seat = $data ['reserved_seats'];
            $restaurantAllocatedSeat = 0;
            $totalSeatCount = 0;
            $dst = strtotime($dineinCalendarsDetail['dinner_start_time']);
            $det = strtotime($dineinCalendarsDetail['dinner_end_time']);
            if (strtotime($dineinCalendarsDetail['breakfast_start_time']) <= $requested_time && $requested_time <= strtotime($dineinCalendarsDetail['breakfast_end_time'])) {
                $restaurantAllocatedSeat = $dineinCalendarsDetail['breakfast_seats'];
            } elseif (strtotime($dineinCalendarsDetail['lunch_start_time']) <= $requested_time && $requested_time <= strtotime($dineinCalendarsDetail['lunch_end_time'])) {
                $restaurantAllocatedSeat = $dineinCalendarsDetail['lunch_seats'];
            } else {
                $restaurantAllocatedSeat = $dineinCalendarsDetail['dinner_seats'];
            }


            ## If Allocated seat is less than Requested seat--Decline the reservation HERE ##
            //echo $restaurantAllocatedSeat . ":" . $requested_seat; die;
            if ($restaurantAllocatedSeat < $requested_seat) {
                //return array( 'error' => 1,'msg'=>'Restaurant have limited seat '.$restaurantAllocatedSeat.'. It not  allow reservation for '.$requested_seat.' seat.' );
                return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
            }

            ## Get Occupied Seat ##
            $getExistingReservation = array('restaurant_id' => $data['restaurant_id'], 'time_slot' => $data['time_slot'], 'reservation_id' => $userReservatioModel->id);
            $existingReservation = $userReservatioModel->getUserReservationToCheckSeat($getExistingReservation);
            /* echo $totalExistReservation = count($existingReservation);
              print_r($existingReservation); */
            $seatCount = 0;
            foreach ($existingReservation as $key => $val) {
                $seatCount = $seatCount + $val['reserved_seats'];
            }
            $totalSeatCount = $seatCount + $requested_seat;

            ## If Occupied Seat is greater or equel to Restaurant allocated seat then decline the reservation HERE ##
            if ($totalSeatCount > $restaurantAllocatedSeat) {
                return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
            } else {

                //calculate total seat occupied by small group
                $smallGroupBackwordSeatCount = 0;
                $smallGroupForwordSeatCount = 0;
                $largeGroupBackwordSeatCount = 0;
                $largeGroupForwordSeatCount = 0;
                $largeGroupBackwordImpactSeatCount = 0;
                if ($dineinCalendarsDetail['dinningtime_small'] > TIME_INTERVAL) {
                    $smallGroupBackwordSeatCount = $this->checkSmallBackword($dineinCalendarsDetail, $data);
                    $smallGroupForwordSeatCount = $this->checkSmallForword($dineinCalendarsDetail, $data);
                }
                //echo "smallGroupForwordSeatCount-".$smallGroupForwordSeatCount . ":" ;
                //calculate total seat occupied by large group
                if ($dineinCalendarsDetail['dinningtime_large'] > TIME_INTERVAL) {
                    $largeGroupBackwordSeatCount = $this->checkLargeBackword($dineinCalendarsDetail, $data);
                    $largeGroupForwordSeatCount = $this->checkLargeForword($dineinCalendarsDetail, $data);
                }

                //echo "largeGroupForwordSeatCount-".$largeGroupForwordSeatCount . ":" ;
                //caclculate carry forward reservation on future time slots
                if ($dineinCalendarsDetail['dinningtime_large'] > (2 * TIME_INTERVAL)) {
                    $largeGroupBackwordImpactSeatCount = $this->checkLargeBackwordImpact($dineinCalendarsDetail, $data);
                }

                $totalBackWordSeatCount = $smallGroupBackwordSeatCount + $largeGroupBackwordSeatCount + $requested_seat;
                $totalForWordSeatCount = $smallGroupForwordSeatCount + $largeGroupForwordSeatCount + $requested_seat + $largeGroupBackwordImpactSeatCount;
                ## if Occupied seat by small, large and Requested seat is greater by Restaurant Allocated seat then decline the reservation ##
                //print_r($smallGroupForwordSeatCount).'||'.print_r($largeGroupForwordSeatCount).'||'.print_r($requested_seat); die();
                //print_r($largeGroupBackwordImpactSeatCount . " : " . $totalBackWordSeatCount . ":" . $totalForWordSeatCount); die();

                if ($totalBackWordSeatCount > $restaurantAllocatedSeat) {
                    return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
                } elseif ($totalForWordSeatCount > $restaurantAllocatedSeat) {
                    return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
                }
            }
        }

        $userReservatioModel->user_instruction = isset($data['user_instruction']) ? $data['user_instruction'] : "";
        $userReservatioModel->reserved_seats = $data ['reserved_seats'];
        $userReservatioModel->party_size = $data ['reserved_seats'];
        $userReservatioModel->first_name = isset($data ['first_name']) ? $data ['first_name'] : '';
        $userReservatioModel->last_name = isset($data ['last_name']) ? $data ['last_name'] : '';
        $userReservatioModel->email = $data ['email'];
        $userReservatioModel->phone = $data ['phone'];
        $userReservatioModel->restaurant_id = $data ['restaurant_id'];
        $userReservatioModel->reserved_on = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $data ['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT);
        $userReservatioModel->is_modify = 1;
        $userFullName = $userReservatioModel->first_name . ' ' . $userReservatioModel->last_name;
        $userReservatioModel->status = 1;

        $userReservatioModel->is_read = 0;

        $userReservatioModel->time_slot = $data ['time_slot'] . ":00";

        if ($userReservatioModel->id) {
            $existData = $userReservatioModel->getUserReservation(array(
                'columns' => array(
                    'restaurant_id',
                    'time_slot',
                    'receipt_no',
                    'reserved_seats',
                    'restaurant_name',
                    'user_id',
                    'first_name',
                    'status',
                    'is_modify'
                ),
                'where' => array(
                    'id' => $userReservatioModel->id
                )
            ));
            $existData = current($existData);
            $response = $userReservatioModel->updateUserReservation();
        }

        ## Update user point ##
        if ($response) {
            if ($existData['status'] == 4) {
                if ($userReservatioModel->user_id) {

                    $userData = $userModel->getUserDetail(array(
                        'column' => array(
                            'points'
                        ),
                        'where' => array(
                            'id' => $userReservatioModel->user_id
                        )
                    ));

                    $userPoints = $userData ['points'];
                    $pointId = $pointID['reserveATable'];

                    $reservationPoints = $pointSourceModel->getPointSourceDetail(array(
                        'column' => array(
                            'points',
                            'id'
                        )
                        ,
                        'where' => array(
                            'id' => $pointId
                        )
                    ));

                    $userModel->id = $userReservatioModel->user_id;

                    //user Invitation point calculation 
                    $totalInvitationPoint = 0;
                    $reservationInvitationModel = new UserInvitation ();
                    $reservationInvitationModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
                    $options = array(
                        'columns' => array(
                            'to_id'
                        ),
                        'where' => array(
                            'msg_status' => 1,
                            'user_id' => $userReservatioModel->user_id,
                            'reservation_id' => $userReservatioModel->id
                        )
                    );
                    $reservationInvitationDetails = $reservationInvitationModel->find($options)->toArray();
                    // check if email user has account

                    if (count($reservationInvitationDetails) > 0) {
                        $totalInvitation = count($reservationInvitationDetails);
                        $key = 'acceptReservation';
                        $points = $user_function->getAllocatedPoints($key);
                        $totalInvitationPoint = $points ['points'] * $totalInvitation;
                    }

                    $userPoints = $userData['points'] - $reservationPoints['points'] - $totalInvitationPoint;
                    $userModel->update(array(
                        'points' => $userPoints
                    ));

                    //edit userpoint source
                    $status = '2';
                    $pointSorce = 3;
                    $refId = $userReservatioModel->id;
                    $userPointModel->updateUserRef($userReservatioModel->user_id, $pointSorce, $refId, $status);
                }
            }
        }


        if (!$response) {
            return array(
                'response' => false
            );
        }
        /* time slot and reserved seats get compared with exisiting respectively */
        $timeSlotMatch = strcmp($response ['time_slot'], $existData ['time_slot']);
        $seatMatch = strcmp($response ['reserved_seats'], $existData ['reserved_seats']);
        if ($timeSlotMatch === 0 && $seatMatch === 0) {
            // do nothing
        } else {
            /**
             * Send modify reservation mail to user
             * send update in pubnub
             * insert data into pububnub mymunchado
             * insert data into pubnub dashboard
             */
            if (strlen($response ['time_slot']) == 22) {
                $time = substr($response ['time_slot'], 0, -3);
            } else {
                $time = $response ['time_slot'];
            }

            //$sendMail = $userModel->checkUserForMail($existData ['user_id'], 'reservation');
            //if ($sendMail == true) {
            $sendModifyReservationMail = array(
                'receiver' => array(
                    $data ['email']
                ),
                'variables' => array(
                    'name' => $data ['first_name'],
                    'restaurantName' => $existData ['restaurant_name'],
                    'receiptNo' => $existData ['receipt_no'],
                    'web_url' => $webUrl,
                    'reservationDate' => StaticOptions::getFormattedDateTime($time, 'Y-m-d H:i:s', 'D, M d, Y'),
                    'reservationTime' => StaticOptions::getFormattedDateTime($time, 'Y-m-d H:i:s', 'h:i A'),
                    'oldReservationDate' => StaticOptions::getFormattedDateTime($existData ['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                    'oldReservationTime' => StaticOptions::getFormattedDateTime($existData ['time_slot'], 'Y-m-d H:i:s', 'h:i A')
                ),
                'subject' => "We've Updated Your Reservation at ".$existData ['restaurant_name'],
                'template' => 'modify-reservation',
                'layout' => 'email-layout/default_new'
            );
            $user_function->sendMails($sendModifyReservationMail);
            //}
            /**
             * Send modify reservation mail to user friends
             * send update in pubnub
             * insert data into pububnub mymunchado
             * insert data into pubnub dashboard
             */
            $invitationData = $userReservationInviteModel->getAllUserInvitation(array(
                'where' => array(
                    'reservation_id' => $id,
                    'user_id' => $existData ['user_id']
                )
            ));
            if (!empty($invitationData)) {
                foreach ($invitationData as $data) {
                    //$sendMail = $userModel->checkUserForMail($data ['to_id'], 'reservation');
                    //if ($sendMail == true) {
                    if (!empty($data ['friend_email'])) {
                        $userMailDetails = $userModel->getUserDetail(array(
                            'where' => array(
                                'email' => $data ['friend_email']
                            )
                        ));
                        if (!empty($userMailDetails)) {
                            $friendFirstName = $userMailDetails ['first_name'];
                        } else {
                            $friendFirstName = '';
                        }
                        if (empty($friendFirstName)) {
                            $email_array = explode('@', $data ['friend_email']);
                            if (!empty($email_array [0])) {
                                $friendFirstName = $email_array [0];
                            }
                        }
                        $sendCancelMailToFriendArray = array(
                            'receiver' => array(
                                $data ['friend_email']
                            ),
                            'variables' => array(
                                'username' => $friendFirstName,
                                'friendname' => $existData ['first_name'],
                                'web_url' => $webUrl,
                                'numberOfPeople' => $existData ['reserved_seats'],
                                'restaurantName' => $existData ['restaurant_name'],
                                'reservationDate' => StaticOptions::getFormattedDateTime($existData ['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                                'reservationtime' => StaticOptions::getFormattedDateTime($existData ['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                                'reservationLink' => $webUrl . '/restaurants/view/' . $existData ['restaurant_id'],
                                
                            ),
                            'subject' => 'Update Your Calendar',
                            'template' => 'modify-reservation-to-friends',
                            'layout' => 'email-layout/default_update_reservation'
                        );
                        $user_function->sendMails($sendCancelMailToFriendArray);
                    }
                    //}
                    if (!empty($data ['to_id'])) {
                        $userDetails = $userModel->getUserDetail(array(
                            'where' => array(
                                'email' => $data ['friend_email']
                            )
                        ));

                        $userName = $userDetails ['first_name'];
                        $notificationMsg = "Hey " . ucfirst($userName) . "! the reservation details at " . ucfirst($existData ['restaurant_name']) . " with ".  ucfirst($existData ['first_name'])." have changed. Reconfirm if you're going or let them know if you're bailing.";
                        $channel = "mymunchado_" . $data ['to_id'];
                        $notificationArray = array(
                            "msg" => $notificationMsg,
                            "channel" => $channel,
                            "userId" => $data ['to_id'],
                            "type" => 'reservation',
                            "restaurantId" => $existData ['restaurant_id'],
                            'curDate' => $currentDate,
                            'username' => ucfirst($userFullName),
                            'restaurant_name' => ucfirst($existData ['restaurant_name']),
                            'is_friend' => 0,
                            'reservation_id' => $userReservatioModel->id,
                            'user_id' => $userReservatioModel->user_id,
                            'reservation_status' => $userReservatioModel->status,
                            'first_name' => $userReservatioModel->first_name,
                            'friend_name'=>ucfirst($existData ['first_name']),
                            'friend_id'=>$existData ['user_id']
                        );

                        $notificationJsonArray = array('friend_name'=>ucfirst($existData ['first_name']),'friend_id'=>$existData ['user_id'],'is_friend' => 0, 'reservation_id' => $userReservatioModel->id, 'user_id' => $userReservatioModel->user_id, 'reservation_status' => $userReservatioModel->status, 'username' => ucfirst($userName), 'user_id' => $data ['to_id'], 'restaurant_id' => $existData['restaurant_id'], 'restaurant_name' => ucfirst($existData ['restaurant_name']));
                        $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                    }
                }
            }
            $resData = $restaurantAccountModel->getRestaurantAccountDetail(array(
                'columns' => array(
                    'status',
                    'email',
                    'name'
                ),
                'where' => array(
                    'restaurant_id' => $existData ['restaurant_id']
                )
            ));
            $sendMailToRestaurant = $restaurantAccountModel->checkRestaurantForMail($existData ['restaurant_id'], 'reservation');
            if ($sendMailToRestaurant == true) {
                $sendCancelMailToOwnerArray = array(
                    'receiver' => array(
                        $resData ['email']
                    ),
                    'variables' => array(
                        'ownername' => $resData ['name'],
                        'username' => $existData ['first_name'],
                        'numberOfPeople' => $existData ['reserved_seats'],
                        'receiptNo' => $existData ['receipt_no'],
                        'reservationDate' => StaticOptions::getFormattedDateTime($existData ['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                        'reservationtime' => StaticOptions::getFormattedDateTime($existData ['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                        'reservationLink' => $webUrl . '/restaurants/view/' . $existData ['restaurant_id']
                    ),
                    'subject' => 'One of Your Reservations Has Changed',
                    'template' => 'modify-reservation-to-owner',
                );
                $user_function->sendMailsToRestaurant($sendCancelMailToOwnerArray);
            }
            if ($userReservatioModel->user_id) {
                $notificationMsg = "Your've updated your reservation at '. ucfirst($existData ['restaurant_name']) . '. Mark your calendar.";
                $channel = "mymunchado_" . $userReservatioModel->user_id;
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $userReservatioModel->user_id,
                    "type" => 'reservation',
                    "restaurantId" => $existData ['restaurant_id'],
                    'curDate' => $currentDate,
                    'is_friend' => 0,
                    'reservation_id' => $userReservatioModel->id,
                    'user_id' => $userReservatioModel->user_id,
                    'reservation_status' => $userReservatioModel->status,
                    'username' => ucfirst($userFullName),
                    'first_name' => ucfirst($userReservatioModel->first_name),
                    'restaurant_name'=>ucfirst($existData ['restaurant_name'])
                );
                $notificationJsonArray = array('restaurant_name'=>ucfirst($existData ['restaurant_name']),'is_friend' => 0, 'reservation_id' => $userReservatioModel->id, 'user_id' => $userReservatioModel->user_id, 'reservation_status' => $userReservatioModel->status, 'username' => ucfirst($userFullName), 'first_name' => ucfirst($userReservatioModel->first_name));
                $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                
                $replacementData = array('restaurant_name' => $existData ['restaurant_name']);
                $otherReplacementData = array('user_name' => ucfirst($userFullName),'restaurant_name' => $existData ['restaurant_name']);
                $feed = array(
                    'restaurant_id' => $existData ['restaurant_id'],
                    'restaurant_name' => $existData ['restaurant_name'],
                    'user_name' => ucfirst($userFullName),
                    "user_id" => $userReservatioModel->user_id,
                    'img' => array()
                );

                $activityFeed = $commonFunctiion->addActivityFeed($feed, 73, $replacementData, $otherReplacementData);
                
            }
            $notificationMsg = 'A customer modified their reservation. Review it now.';
            $channel = "dashboard_" . $existData ['restaurant_id'];
            $notificationArray = array(
                "msg" => $notificationMsg,
                "channel" => $channel,
                "userId" => $userReservatioModel->user_id,
                "type" => 'reservation',
                "restaurantId" => $existData ['restaurant_id'],
                'curDate' => $currentDate,
                'is_friend' => 0,
                'reservation_id' => $userReservatioModel->id,
                'user_id' => $userReservatioModel->user_id,
                'reservation_status' => $userReservatioModel->status,
                'username' => ucfirst($userFullName),
                'first_name' => ucfirst($userReservatioModel->first_name)
            );
            $notificationJsonArray = array('is_friend' => 0, 'reservation_id' => $userReservatioModel->id, 'user_id' => $userReservatioModel->user_id, 'reservation_status' => $userReservatioModel->status, 'username' => ucfirst($userFullName), 'first_name' => ucfirst($userReservatioModel->first_name));
            //$response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
            //$pubnub = StaticOptions::pubnubPushNotification($notificationArray);
        }

        return array(
            'response' => true
        );
    }

    public function delete($id) {
        $userReservatioModel = new UserReservation ();
        $userNotificationModel = new UserNotification ();
        $userReservationInviteModel = new UserInvitation ();
        $locationData = $this->getUserSession()->getUserDetail('selected_location');
        $user_function = new UserFunctions ();
        $currentDate = $user_function->userCityTimeZone($locationData);
        $userReservatioModel->id = $id;
        $userReservatioModel->status = 2;
        $response = $userReservatioModel->cancelReservation();
        $reservationData = $userReservatioModel->getUserReservation(array(
            'column' => array(
                'restaurant_id',
                'user_id',
                'status'
            ),
            'where' => array(
                'id' => $id
            )
        ));
        $reservationData = current($reservationData);
        $restaurantModel = new Restaurant ();
        $restaurant = $restaurantModel->findRestaurant(array(
            'column' => array(
                'restaurant_name'
            ),
            'where' => array(
                'id' => $reservationData ['restaurant_id']
            )
        ));
        $userModel = new User ();
        $sendMail = $userModel->checkUserForMail($reservationData ['user_id'], 'reservation');
        if($sendMail){//Bug 39763 - Reservation cancel mail not coming
            $sendCancelMailToHostArray = array(
                'receiver' => array(
                    $reservationData ['email']
                ),
                'variables' => array(
                    'username' => $reservationData ['first_name'],
                    'friendname' => 'you',
                    'numberOfPeople' => $reservationData ['reserved_seats'],
                    'restaurantName' => $restaurant->restaurant_name,
                    'reservationDate' => StaticOptions::getFormattedDateTime($reservationData ['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                    'reservationtime' => StaticOptions::getFormattedDateTime($reservationData ['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                    'reservationLink' => WEB_URL . 'restaurants/view/' . $reservationData ['restaurant_id']
                ),
                'subject' => 'Your Reservation at '.$restaurant->restaurant_name.' Was Successfully Canceled. Bummer.',
                'template' => 'send-cancel-reservation',
                'layout'=> 'email-layout/default_new'
            );
            $user_function->sendMails($sendCancelMailToHostArray);
        }
        $invitationData = $userReservationInviteModel->getAllUserInvitation(array(
            'where' => array(
                'reservation_id' => $id
            )
        ));
        if (!empty($invitationData)) {
            foreach ($invitationData as $data) {
                if (!empty($data ['friend_email'])) {
                    $userMailDetails = $userModel->getUserDetail(array(
                        'where' => array(
                            'email' => $data ['friend_email']
                        )
                    ));
                    if (!empty($userMailDetails)) {
                        $friendFirstName = $userMailDetails ['first_name'];
                    } else {
                        $friendFirstName = '';
                    }
                    if (empty($friendFirstName)) {
                        $email_array = explode('@', $data ['friend_email']);
                        if (!empty($email_array [0])) {
                            $friendFirstName = $email_array [0];
                        }
                    }
                    $sendCancelMailToFriendArray = array(
                        'receiver' => array(
                            $data ['friend_email']
                        ),
                        'variables' => array(
                            'username' => $friendFirstName,
                            'friendname' => $reservationData ['first_name'],
                            'numberOfPeople' => $reservationData ['reserved_seats'],
                            'restaurantName' => $restaurant->restaurant_name,
                            'reservationDate' => StaticOptions::getFormattedDateTime($reservationData ['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                            'reservationtime' => StaticOptions::getFormattedDateTime($reservationData ['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                            'reservationLink' => WEB_URL . 'restaurants/view/' . $reservationData ['restaurant_id']
                        ),
                        'subject' => ucfirst($reservationData ['first_name'])."'s EPIC Fail",
                        'template' => 'send-cancel-reservation-friends',
                        'layout' => 'email-layout/default_new'
                    );
                    $user_function->sendMails($sendCancelMailToFriendArray);
                }
                
                if (!empty($data ['to_id'])) {
                    $userDetails = $userModel->getUserDetail(array(
                        'where' => array(
                            'email' => $data ['friend_email']
                        )
                    ));
                    $FirstName = $userDetails ['first_name'];

                    $notificationMsg = "Hey " . ucfirst($FirstName) . ", " . ucfirst($reservationData ['first_name']) . " had to cancel the reservation at " . ucfirst($restaurant->restaurant_name) . ". Dang.";
                    $channel = "mymunchado_" . $data ['to_id'];
                    $notificationArray = array(
                        "msg" => $notificationMsg,
                        "channel" => $channel,
                        "userId" => $data ['to_id'],
                        "type" => 'reservation',
                        "restaurantId" => $reservationData ['restaurant_id'],
                        'curDate' => $currentDate,
                        'username' => ucfirst($FirstName),
                        'restaurant_name' => ucfirst($restaurant->restaurant_name),
                        'reservation_status' => $userReservatioModel->status,
                        'is_friend' => 1,
                        'reservation_id' => $id,
                        'first_name' => ucfirst($FirstName),
                        'friend_name' => ucfirst($reservationData ['first_name']),
                        'friend_id' => $reservationData ['user_id']
                    );
                    $notificationJsonArray = array('friend_name' => ucfirst($reservationData ['first_name']), 'friend_id' => $reservationData ['user_id'], 'first_name' => ucfirst($FirstName), 'reservation_status' => $userReservatioModel->status, 'is_friend' => 1, 'reservation_id' => $id, 'username' => ucfirst($FirstName), 'user_id' => $data ['to_id'], 'restaurant_id' => $reservationData ['restaurant_id'], 'restaurant_name' => ucfirst($restaurant->restaurant_name));
                    $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                    $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                    
                    $commonFunctiion = new \MCommons\CommonFunctions();
                    $replacementData = array('friend_name' => ucfirst($reservationData ['first_name']),'restaurant_name' => ucfirst($restaurant->restaurant_name));
                    $otherReplacementData = array();
                    $feed = array(
                        'friend_name' => ucfirst($reservationData ['first_name']),
                        "user_id" => $data ['to_id'],
                        "restaurant_id" => $reservationData ['restaurant_id'],
                        'restaurant_name' => ucfirst($restaurant->restaurant_name)
                        
                    );
                    $activityFeed = $commonFunctiion->addActivityFeed($feed, 58, $replacementData, $otherReplacementData);
                }
            }
        }
        $restaurantAccountModel = new RestaurantAccounts ();
        $resData = $restaurantAccountModel->getRestaurantAccountDetail(array(
            'columns' => array(
                'status',
                'email',
                'user_name'
            ),
            'where' => array(
                'restaurant_id' => $reservationData ['restaurant_id']
            )
        ));
        $sendMailToRestaurant = $restaurantAccountModel->checkRestaurantForMail($reservationData ['restaurant_id'], 'reservation');
        if ($sendMailToRestaurant == true) {
            $sendCancelMailToOwnerArray = array(
                'receiver' => array(
                    $resData ['email']
                ),
                'variables' => array(
                    'ownername' => $resData ['user_name'],
                    'username' => $reservationData ['first_name'],
                    'numberOfPeople' => $reservationData ['reserved_seats'],
                    'receiptNo' => $reservationData ['receipt_no'],
                    'reservationDate' => StaticOptions::getFormattedDateTime($reservationData ['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                    'reservationtime' => StaticOptions::getFormattedDateTime($reservationData ['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                    'reservationLink' => WEB_URL . 'restaurants/view/' . $reservationData ['restaurant_id']
                ),
                'subject' => 'Bad News About a Munch Ado Reservation',
                'template' => 'send-cancel-reservation-owner'
            );
            /* mail template closed as per request by mohit and testing team */
           // $user_function->sendMailsToRestaurant($sendCancelMailToOwnerArray);
        }
        if (!empty($reservationData ['user_id'])) {
            $notificationMsg = 'You’ve successfully canceled your reservation at ' . ucfirst($restaurant->restaurant_name) . '. Bummer.';
            $channel = "mymunchado_" . $reservationData ['user_id'];
            $notificationArray = array(
                "msg" => $notificationMsg,
                "channel" => $channel,
                "userId" => $reservationData ['user_id'],
                "type" => 'reservation',
                "restaurantId" => $reservationData ['restaurant_id'],
                'curDate' => $currentDate,
                'restaurant_name' => ucfirst($restaurant->restaurant_name),
                'reservation_status' => $userReservatioModel->status,
                'is_friend' => 0,
                'reservation_id' => $id
            );
            $notificationJsonArray = array('reservation_status' => $userReservatioModel->status, 'is_friend' => 0, 'reservation_id' => $id, 'restaurant_id' => $reservationData ['restaurant_id'], 'restaurant_name' => ucfirst($restaurant->restaurant_name));
            $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
        }
        $msg ="Your customer cancelled their reservation. Their loss!";
        $channel = "dashboard_" . $reservationData ['restaurant_id'];
        $dashboardArray = array(
            "msg" => $msg,
            "channel" => $channel,
            "userId" => $reservationData ['user_id'],
            "type" => 'reservation',
            "restaurantId" => $reservationData ['restaurant_id'],
            'curDate' => $currentDate
        );
        $notificationJsonArray = array('reservation_status' => $userReservatioModel->status, 'is_friend' => 0, 'reservation_id' => $id, 'restaurant_id' => $reservationData ['restaurant_id'], 'restaurant_name' => ucfirst($restaurant->restaurant_name));
        $dashboardNotificationModel = new UserDashboardNotification ();
        $response = $dashboardNotificationModel->createPubNubDashboardNotification($dashboardArray, $notificationJsonArray);
        $pubnub = StaticOptions::pubnubPushNotification($dashboardArray);
        $pointSourceModel = new PointSourceDetails ();
        $userPointModel = new UserPoint ();


        ############ Deduct User Points ############
        $identifier = 'reserveTable';
        $points = $user_function->getAllocatedPoints($identifier);
        $user_function->takePoints($points, $reservationData ['user_id'], $id);

        return array(
            "deleted" => (bool) $response
        );
    }

    public function create($data) {        
        $userPointTable = 0;
        $commonFunctiion = new \MCommons\CommonFunctions();
        $userFunctions = new \User\UserFunctions ();
        if (isset($data['reservation_id']) && !empty($data['reservation_id']) && is_numeric($data['reservation_id'])) {
            $reReservationDetail = $this->reservationAgain($data);
            $data['restaurant_id'] = $reReservationDetail['restaurant_id'];
            $data ['reserved_seats'] = $reReservationDetail['party_size'];
            $data ['first_name'] = $reReservationDetail['first_name'];
            $data ['last_name'] = $reReservationDetail['last_name'];
            $data ['email'] = $reReservationDetail['email'];
            $data ['phone'] = $reReservationDetail['phone'];
            $data ['restaurant_name'] = $reReservationDetail['restaurant_name'];
            $data['user_instruction'] = $reReservationDetail['user_instruction'];
            $data['order_id'] = $reReservationDetail['order_id'];
            unset($data['reservation_id']);
        }
        $userId = $this->getUserSession()->getUserId();        
        $this->validate($data,$userId);
        
        $currentDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $data ['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT); 
        
        if(strtotime($data ['time_slot']) < strtotime($currentDateTime)){
             throw new \Exception('Reservation date time is not valid',400);
        }

        ############ Get user IP Address ##############           
        $ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        ###############################################

        $data ['time_slot'] = date('Y-m-d H:i', strtotime($data ['time_slot']));
        $timeslot = explode(" ", $data ['time_slot']);
        $data ['date'] = $timeslot[0];
        $data['time'] = $timeslot[1];
        $reservationFunctions = new ReservationFunctions ();
        $userReservationModel = new UserReservation ();
        $restaurantAccount = new RestaurantAccounts ();
        $restaurantDineinCalendars = new RestaurantDineinCalendars();
        $options = array("where" => array("restaurant_id" => $data['restaurant_id']));
        $restaurantAccountDetails = $restaurantAccount->getRestaurantAccountDetail(array('where' => array(
                'restaurant_id' => $data ['restaurant_id'],
                'status' => '1'
            )
        ));

        $dineinCalendarsDetail = $restaurantDineinCalendars->findRestaurantDineinDetail($options);
        if (isset($dineinCalendarsDetail) && !empty($dineinCalendarsDetail) && $restaurantAccountDetails) {

            $requested_time = strtotime($data['time']);
            $requested_seat = $data ['reserved_seats'];
            $restaurantAllocatedSeat = 0;
            $totalSeatCount = 0;
            //echo "here";echo $data['time']; die();
            $dst = strtotime($dineinCalendarsDetail['dinner_start_time']);
            $det = strtotime($dineinCalendarsDetail['dinner_end_time']);
            if (strtotime($dineinCalendarsDetail['breakfast_start_time']) <= $requested_time && $requested_time <= strtotime($dineinCalendarsDetail['breakfast_end_time'])) {
                $restaurantAllocatedSeat = $dineinCalendarsDetail['breakfast_seats'];
            } elseif (strtotime($dineinCalendarsDetail['lunch_start_time']) <= $requested_time && $requested_time <= strtotime($dineinCalendarsDetail['lunch_end_time'])) {
                $restaurantAllocatedSeat = $dineinCalendarsDetail['lunch_seats'];
            } else {
                $restaurantAllocatedSeat = $dineinCalendarsDetail['dinner_seats'];
            }


            ## If Allocated seat is less than Requested seat--Decline the reservation HERE ##			
            //echo $restaurantAllocatedSeat . ":" . $requested_seat; die;
            if ($restaurantAllocatedSeat < $requested_seat) {
                //return array( 'error' => 1,'msg'=>'Restaurant have limited seat '.$restaurantAllocatedSeat.'. It not  allow reservation for '.$requested_seat.' seat.' );
                return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
            }

            ## Get Occupied Seat ##
            $getExistingReservation = array('restaurant_id' => $data['restaurant_id'], 'time_slot' => $data['time_slot']);
            $existingReservation = $userReservationModel->getUserReservationToCheckSeat($getExistingReservation);

            $seatCount = 0;
            foreach ($existingReservation as $key => $val) {
                $seatCount = $seatCount + $val['reserved_seats'];
            }
            $totalSeatCount = $seatCount + $requested_seat;

            ## If Occupied Seat is greater or equel to Restaurant allocated seat then decline the reservation HERE ##
            if ($totalSeatCount > $restaurantAllocatedSeat) {
                return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
            } else {

                //calculate total seat occupied by small group
                $smallGroupBackwordSeatCount = 0;
                $smallGroupForwordSeatCount = 0;
                $largeGroupBackwordSeatCount = 0;
                $largeGroupForwordSeatCount = 0;
                $largeGroupBackwordImpactSeatCount = 0;
                if ($dineinCalendarsDetail['dinningtime_small'] > TIME_INTERVAL) {
                    $smallGroupBackwordSeatCount = $this->checkSmallBackword($dineinCalendarsDetail, $data);
                    $smallGroupForwordSeatCount = $this->checkSmallForword($dineinCalendarsDetail, $data);
                }

                //calculate total seat occupied by large group
                if ($dineinCalendarsDetail['dinningtime_large'] > TIME_INTERVAL) {
                    $largeGroupBackwordSeatCount = $this->checkLargeBackword($dineinCalendarsDetail, $data);
                    $largeGroupForwordSeatCount = $this->checkLargeForword($dineinCalendarsDetail, $data);
                }

                //caclculate carry forward reservation on future time slots
                if ($dineinCalendarsDetail['dinningtime_large'] > (2 * TIME_INTERVAL)) {
                    $largeGroupBackwordImpactSeatCount = $this->checkLargeBackwordImpact($dineinCalendarsDetail, $data);
                }

                $totalBackWordSeatCount = $smallGroupBackwordSeatCount + $largeGroupBackwordSeatCount + $requested_seat;
                $totalForWordSeatCount = $smallGroupForwordSeatCount + $largeGroupForwordSeatCount + $requested_seat + $largeGroupBackwordImpactSeatCount;
                ## if Occupied seat by small, large and Requested seat is greater by Restaurant Allocated seat then decline the reservation ##
                //print_r($smallGroupForwordSeatCount).'||'.print_r($largeGroupForwordSeatCount).'||'.print_r($requested_seat); die();
                //print_r($largeGroupBackwordImpactSeatCount . " : " . $totalBackWordSeatCount . ":" . $totalForWordSeatCount); die();
                if ($totalBackWordSeatCount > $restaurantAllocatedSeat) {
                    return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
                } elseif ($totalForWordSeatCount > $restaurantAllocatedSeat) {
                    return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
                }
            }
        }

        
        $selectedLocation = $this->getUserSession()->getUserDetail('selected_location', array());
        $cityId = isset($selectedLocation ['city_id']) ? $selectedLocation ['city_id'] : 18848;        
//      $userSetting = new UserSetting ();
//      $restaurantDetail = new RestaurantDetail ();
        $userModel = new User ();
        $restaurantModel = new Restaurant ();
//      $restaurantNotificationSettings = new RestaurantNotificationSettings ();
        $userNotificationModel = new UserNotification ();
        $pointSourceModel = new PointSourceDetails ();
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $pointID = isset($config ['constants'] ['point_source_detail']) ? $config ['constants'] ['point_source_detail'] : array();
//        $token = $data ['token'];
        $userReservationModel->city_id = $cityId;
        $userReservationModel->order_id = isset($data['order_id']) ? $data['order_id'] : NULL;
        $userReservationModel->restaurant_id = $data ['restaurant_id'];
        $userReservationModel->time_slot = $data ['time_slot'];
        $userReservationModel->party_size = $data ['reserved_seats'];
        $userReservationModel->reserved_seats = $data ['reserved_seats'];
        $userReservationModel->user_instruction = isset($data ['user_instruction']) ? $data ['user_instruction'] : '';
        $userReservationModel->first_name = $data ['first_name'];
        $userReservationModel->last_name = isset($data ['last_name']) ? $data ['last_name'] : '';
        $userReservationModel->phone = $data ['phone'];
        $userReservationModel->email = $data ['email'];
        $userReservationModel->restaurant_name = $data ['restaurant_name'];
        $userReservationModel->host_name = (StaticOptions::$_userAgent==="iOS")?"iphone":"android";
        $userReservationModel->receipt_no = $reservationFunctions->generateReservationReceipt();
        $userReservationModel->reserved_on = $currentDateTime;
        $userReservationModel->user_ip = $ipAddress;

        $userFullName = $userReservationModel->first_name . ' ' . $userReservationModel->last_name;
//        if ($restaurantAccountDetails)
//            $userReservationModel->status = 4;
//        else
        $userReservationModel->status = 1;

        // give points for reservation
        if ($userId) {
            $userReservationModel->user_id = $userId;
        }

        $reservation = $userReservationModel->reserveTable();

        $userPoints = '';
        $awardPoint = 0;
        $confirm = 0;
        if ($userId) {           
            /* $points = $userFunctions->getAllocatedPoints ( 'reserveTable' );
              $message = 'You have upcoming plans! This calls for a celebration, here are 10 points!';
              $userFunctions->givePoints ( $points, $userId, $message, $reservation ['id'] ); */
            $userModel = new User ();
            $userData = $userModel->getUserDetail(array(
                'column' => array(
                    'points',
                    'phone'
                ),
                'where' => array(
                    'id' => $userId
                )
            ));
            if ($userData ['phone'] == null) {
                $userModel->id = $userId;
                $userModel->update(array(
                    'phone' => $data ['phone']
                ));
            }
            $userPointsModel = new \User\Model\UserPoint();
            $totalPoints = $userPointsModel->countUserPoints($userId);
            $redeem_points = $totalPoints[0]['redeemed_points'];
            $userPoints = strval($totalPoints[0]['points'] - $redeem_points);

//          $userModel = new User();
            $pointId = $pointID['reserveATable'];
            $reservationPoints = $pointSourceModel->getPointSourceDetail(array(
                'column' => array(
                    'points',
                    'id'
                )
                ,
                'where' => array(
                    'id' => $pointId
                )
            ));
                ########## Dine and more awards point calculation ###############
                $userPoints = $userPoints + $reservationPoints['points'];
                $userPointTable = $reservationPoints['points'];
                $userFunctions->userId = $userId;
                $userFunctions->restaurantId = $userReservationModel->restaurant_id;
                $userFunctions->activityDate = $data ['time_slot'];
                $userFunctions->restaurant_name = $userReservationModel->restaurant_name;
                $userFunctions->typeValue = $reservation['id'];
                $userFunctions->typeKey = 'reservation_id';
                $awardPoint_reservation = $userFunctions->dineAndMoreAwards("awardsreservation");
                $awardPoint = (isset($awardPoint_reservation['points']))?$awardPoint_reservation['points']:0;
               
                if(isset($awardPoint_reservation['points'])){
                    $userPoints = ($userPoints + $awardPoint_reservation['points'])-$reservationPoints['points'];
                    $userPointTable = $awardPoint_reservation['points'];                    
                }elseif(isset($data['order_id'])) {
                    $userTransaction = $userFunctions->getFirstTranSactionUser();
                    if ($userTransaction == 0) {
                        $userPoints = $userPoints + $data['order_point'];
                        $userPointTable = $reservationPoints['points'] + $data['order_point'];
                    } else {
                        $userPoints = $userPoints + $reservationPoints['points'] + $data['order_point'];
                        $userPointTable = $reservationPoints['points'] + $data['order_point'];
                    }
                    
                } 
//                $userModel->update(array(
//                    'points' => $userPoints
//                ));
//
//                $userPointsModel = new UserPoint ();
//                $dataPoins = array(
//                    'user_id' => $userId,
//                    'point_source' => $reservationPoints['id'],
//                    'points' => $userPointTable,
//                    'created_at' => $userReservationModel->reserved_on,
//                    'status' => '1',
//                    'points_descriptions' => 'You have upcoming plans! This calls for a celebration, here are ' . $userPointTable . ' points!',
//                    'ref_id' => $reservation['id']
//                );
//
//                $userPointsModel->createPointDetail($dataPoins);
//                $confirm = 1;
//            } else {
//                $userPoints = $userPoints + $userPointTable;
//            }
        }
//        if ($userReservationModel->status == 1) {
            
//            $totalordercount = $userFunctions->sendSmsonTransactionCount($userId);
//            send SMS Clickatell  
//            $userSmsData = array();
//            $specChar = $config ['constants']['special_character'];
//            $userSmsData['user_mob_no'] = $data ['phone'];
//            if (isset($data['order_id'])) {
//                $userSmsData['message'] = "We received your Munch Ado pre-paid reservation for " . $data ['reserved_seats'] . " at " . strtr($data['restaurant_name'], $specChar) . " and it makes us wish we were invited...maybe next time?"; 
//            } else {
//                $userSmsData['message'] = "We received your Munch Ado reservation for " . $data ['reserved_seats'] . " at " . strtr($data['restaurant_name'], $specChar) . "! We're stuck in the office, but paint the town orange for us! Don't forget to upload a pic of your receipt to earn points!";
//            }
//            if($totalordercount == 1){
//                $userSmsData['message'] .=" Want to just unplug from food updates? Reply Unsubscribe and embrace the peace and tranquility of the unknown.";
//            }else{
//             $userSmsData['message'] .="";
//            }
//            StaticOptions::sendSmsClickaTell($userSmsData,$userId);
//        }
//        $settingData = '';

        if ($data ['first_name']) {
            $userName = $data ['first_name'];
        } else {
            $emailArr = explode("@", $data ['email']);
            $userName = $emailArr [0];
        }
        $instruction = "";
        if (!empty($data ['user_instruction'])) {
            $instruction = str_replace('||', '<br>', $data ['user_instruction']);
        }
        $restaurant = $restaurantModel->findRestaurant(array(
            'columns' => array(
                'address',
                'landmark',
                'zipcode'
            ),
            'where' => array(
                'id' => $data ['restaurant_id']
            )
        ));
        $restaurantAddress = $restaurant->address . ',' . $restaurant->landmark . ',' . $restaurant->zipcode;
        // ###### Send Mail to user for reservation confirmation #########
        
        $webUrl = PROTOCOL . $config ['constants'] ['web_url'];

        $sendMail = $userModel->checkUserForMail($userId, 'reservation');
        $specChar = $config ['constants']['special_character'];
        if (!isset($data['order_id']) && $sendMail) {
            $emailData = array(
                'receiver' => array(
                    $data ['email']
                ),
                'variables' => array(
                    'username' => $userName,
                    'restaurantName' => $data ['restaurant_name'],
                    'reservationDate' => StaticOptions::getFormattedDateTime($timeslot [0], 'Y-m-d', 'D, M d, Y'),
                    'reservationtime' => StaticOptions::getFormattedDateTime($timeslot [1], 'H:i', 'h:i A'),
                    'numberOfPeople' => $data ['reserved_seats'],
                    'url' => $webUrl
                ),
                'subject' => 'We’ve (Almost) Claimed a Table in Your Name at '.strtr($data['restaurant_name'], $specChar),
                'template' => 'user-reservation-confirmation',
                'layout' => 'email-layout/default_new'
            );
            //StaticOptions::resquePush ( $emailData, "SendEmail" );
            //$userFunctions->sendMails($emailData);
        }
        // ###### Send Mail to restaurnat for reservation confirmation #########
        
        $sendMailToRestaurant = $restaurantAccount->checkRestaurantForMail ( $data ['restaurant_id'], 'reservation' ); 
        if ($sendMailToRestaurant) { 
            $resDetail = $restaurantAccount->getRestaurantAccountDetail (
                    array ( 
                        'columns' => array ( 'email' ), 
                        'where' => array ( 'restaurant_id' => $data ['restaurant_id'] ) ) ); 
            $config = $this->getServiceLocator ()->get ( 'Config' ); 
            $webUrl = PROTOCOL.SITE_URL; 
            $sendMailToMunchAdoCustomerArray = array ( 
                'receiver' => array ( $resDetail ['email'] ), 
                'variables' => array ( 
                    'username' => $userName, 
                    'restaurant_name' => $data ['restaurant_name'], 
                    'party_size' => $data ['reserved_seats'], 
                    'display_date' => StaticOptions::getFormattedDateTime ( $timeslot [0], 'Y-m-d', 'D, M d, Y' ), 
                    'display_time' => StaticOptions::getFormattedDateTime ( $timeslot [1], 'H:i', 'h:i A' ), 
                    'receipt_number' => $userReservationModel->receipt_no, 
                    'web_url' => $webUrl ), 
                    'subject' => 'Reservation From a Munch Ado Customer', 
                    'template' => 'munchado-customer-reservation-confirmation' 
                ); 
            //StaticOptions::resquePush($sendMailToMunchAdoCustomerArray,"SendEmail"); 
            $userFunctions->sendMailsToRestaurant ( $sendMailToMunchAdoCustomerArray ); 
        }
         
        // ###### End of send mail
        /**
         * Push To Pubnub For User
         */
//        if ($userReservationModel->status == 4) {
//            if (!isset($data['order_id'])) {
//                $notificationMsg = 'Reservation reserved at ' . ucfirst($data ['restaurant_name']) . '. Good get.';
//                $channel = "mymunchado_" . $userId;
//                $notificationArray = array(
//                    "msg" => $notificationMsg,
//                    "channel" => $channel,
//                    "userId" => $userId,
//                    "type" => 'reservation',
//                    "restaurantId" => $data ['restaurant_id'],
//                    "restaurant_name" => ucfirst($data ['restaurant_name']),
//                    'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                        'restaurant_id' => $data ['restaurant_id']
//                    ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                    'restaurant_name' => ucfirst($data ['restaurant_name']),
//                    'is_friend' => 0,
//                    'username' => ucfirst($userFullName),
//                    'reservation_id' => $reservation['id'],
//                    'user_id' => $userId,
//                    'reservation_status' => $userReservationModel->status,
//                    'first_name' => ucfirst($userReservationModel->first_name)
//                );
//                $notificationJsonArray = array('first_name' => ucfirst($userReservationModel->first_name), 'is_friend' => 0, 'username' => ucfirst($userName), 'reservation_id' => $reservation['id'], 'user_id' => $userId, 'reservation_status' => $userReservationModel->status, 'restaurant_id' => $data ['restaurant_id'], 'restaurant_name' => ucfirst($data ['restaurant_name']));
//                $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//            } else {
//                $notificationMsg = 'Your pre-paid reservation has been pre-approved.';
//                $channel = "mymunchado_" . $userId;
//                $notificationArray = array(
//                    "msg" => $notificationMsg,
//                    "channel" => $channel,
//                    "userId" => $userId,
//                    "type" => 'reservation',
//                    "restaurantId" => $data ['restaurant_id'],
//                    "restaurant_name" => ucfirst($data ['restaurant_name']),
//                    'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                        'restaurant_id' => $data ['restaurant_id']
//                    ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                    'restaurant_name' => ucfirst($data ['restaurant_name']),
//                    'is_friend' => 0,
//                    'username' => ucfirst($userFullName),
//                    'reservation_id' => $reservation['id'],
//                    'user_id' => $userId,
//                    'reservation_status' => $userReservationModel->status,
//                    'first_name' => ucfirst($userReservationModel->first_name)
//                );
//                $notificationJsonArray = array('first_name' => ucfirst($userReservationModel->first_name), 'is_friend' => 0, 'username' => ucfirst($userFullName), 'reservation_id' => $reservation['id'], 'user_id' => $userId, 'reservation_status' => $userReservationModel->status, 'restaurant_id' => $data ['restaurant_id'], 'restaurant_name' => ucfirst($data ['restaurant_name']));
//                $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//            }
//            /**
//             * Push To Pubnub For Restaurant
//             */
//            $notificationMsg = "You've got a new reservation. Make some room in the dining room.";
//            $channel = "dashboard_" . $data ['restaurant_id'];
//            $notificationArray = array(
//                "msg" => $notificationMsg,
//                "channel" => $channel,
//                "userId" => $userId,
//                "type" => 'reservation',
//                "restaurantId" => $data ['restaurant_id'],
//                'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                    'restaurant_id' => $data ['restaurant_id']
//                ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                'is_friend' => 0,
//                'username' => ucfirst($userFullName),
//                'reservation_id' => $reservation['id'],
//                'user_id' => $userId,
//                'reservation_status' => $userReservationModel->status,
//                'first_name' => ucfirst($userReservationModel->first_name)
//            );
//            $notificationJsonArray = array('first_name' => ucfirst($userReservationModel->first_name), 'is_friend' => 0, 'username' => ucfirst($userFullName), 'reservation_id' => $reservation['id'], 'user_id' => $userId, 'reservation_status' => $userReservationModel->status);
//            $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//        } else {
//        if (!isset($data['order_id'])) {
//            $notificationMsg = "We got your reservation and will let you know once it's confirmed.";
//            $channel = "mymunchado_" . $userId;
//            $notificationArray = array(
//                "msg" => $notificationMsg,
//                "channel" => $channel,
//                "userId" => $userId,
//                "type" => 'reservation',
//                "restaurantId" => $data ['restaurant_id'],
//                "restaurant_name" => ucfirst($data ['restaurant_name']),
//                'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                    'restaurant_id' => $data ['restaurant_id']
//                ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                'restaurant_name' => ucfirst($data ['restaurant_name']),
//                'is_friend' => 0,
//                'username' => ucfirst($userFullName),
//                'reservation_id' => $reservation['id'],
//                'user_id' => $userId,
//                'reservation_status' => $userReservationModel->status,
//                'first_name' => ucfirst($userReservationModel->first_name)
//            );
//            $notificationJsonArray = array('first_name' => ucfirst($userReservationModel->first_name), 'is_friend' => 0, 'username' => ucfirst($userName), 'reservation_id' => $reservation['id'], 'user_id' => $userId, 'reservation_status' => $userReservationModel->status, 'restaurant_id' => $data ['restaurant_id'], 'restaurant_name' => ucfirst($data ['restaurant_name']));
//            $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//        } else {
//                $notificationMsg = 'We received your Munch Ado pre-paid reservation at ' . ucfirst($data ['restaurant_name']) . '.';
//                $channel = "mymunchado_" . $userId;
//                $notificationArray = array(
//                    "msg" => $notificationMsg,
//                    "channel" => $channel,
//                    "userId" => $userId,
//                    "type" => 'reservation',
//                    "restaurantId" => $data ['restaurant_id'],
//                    "restaurant_name" => ucfirst($data ['restaurant_name']),
//                    'curDate' => StaticOptions::getRelativeCityDateTime(array(
//                        'restaurant_id' => $data ['restaurant_id']
//                    ))->format(StaticOptions::MYSQL_DATE_FORMAT),
//                    'restaurant_name' => ucfirst($data ['restaurant_name']),
//                    'is_friend' => 0,
//                    'username' => ucfirst($userFullName),
//                    'reservation_id' => $reservation['id'],
//                    'user_id' => $userId,
//                    'reservation_status' => $userReservationModel->status,
//                    'first_name' => ucfirst($userReservationModel->first_name)
//                );
//                $notificationJsonArray = array('first_name' => ucfirst($userReservationModel->first_name), 'is_friend' => 0, 'username' => ucfirst($userFullName), 'reservation_id' => $reservation['id'], 'user_id' => $userId, 'reservation_status' => $userReservationModel->status, 'restaurant_id' => $data ['restaurant_id'], 'restaurant_name' => ucfirst($data ['restaurant_name']));
//                $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
//                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
//        }
//        }
        /*
         * push to pubmub for dashboard
         * 
         */
        $notificationMsg = "You have a new reservation! Make some space.";
        $channel = "dashboard_" . $data ['restaurant_id'];
        $notificationArray = array(
            "msg" => $notificationMsg,
            "channel" => $channel,
            "userId" => $userId,
            "type" => 'reservation',
            "restaurantId" => $data ['restaurant_id'],
            'curDate' => StaticOptions::getRelativeCityDateTime(array(
                'restaurant_id' => $data ['restaurant_id']
            ))->format(StaticOptions::MYSQL_DATE_FORMAT),
            'is_friend' => 0,
            'username' => ucfirst($userFullName),
            'reservation_id' => $reservation['id'],
            'user_id' => $userId,
            'reservation_status' => $userReservationModel->status,
            'first_name' => ucfirst($userReservationModel->first_name)
        );
        $notificationJsonArray = array('first_name' => ucfirst($userReservationModel->first_name), 'is_friend' => 0, 'username' => ucfirst($userFullName), 'reservation_id' => $reservation['id'], 'user_id' => $userId, 'reservation_status' => $userReservationModel->status);
        $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
        /**
         * Push To Pubnub For cms
         */
        $notificationMsg = "You have received a new reservation request.";
        $channel = "cmsdashboard";
        $notificationArray = array(
            "msg" => $notificationMsg,
            "channel" => $channel,
            "userId" => $userId,
            "type" => 'reservation',
            "restaurantId" => $data ['restaurant_id'],
            'curDate' => StaticOptions::getRelativeCityDateTime(array(
                'restaurant_id' => $data ['restaurant_id']
            ))->format(StaticOptions::MYSQL_DATE_FORMAT),
            'is_friend' => 0,
            'username' => ucfirst($userFullName),
            'reservation_id' => $reservation['id'],
            'user_id' => $userId,
            'reservation_status' => $userReservationModel->status,
            'first_name' => ucfirst($userReservationModel->first_name)
        );
        $notificationJsonArray = array('first_name' => ucfirst($userReservationModel->first_name), 'is_friend' => 0, 'username' => ucfirst($userName), 'reservation_id' => $reservation['id'], 'user_id' => $userId, 'reservation_status' => $userReservationModel->status);
        $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
        /*
         * End of Push To Pubnub For cms dashboard
         */
        $friendList = array();
        // create auto restaurant bookmark
        if ($userId) {
            $restaurantBookmark = new RestaurantBookmark ();
            $bookmarkData = array(
                'restaurant_id' => $data ['restaurant_id'],
                'restaurant_name' => $data ['restaurant_name'],
                'user_id' => $userId,
                'created_on' => StaticOptions::getRelativeCityDateTime(array('restaurant_id' => $data ['restaurant_id']))->format(StaticOptions::MYSQL_DATE_FORMAT),
                'type' => 'bt'
            );
            $restaurantBookmark->insertBookmark($bookmarkData);
            $friendModel = new UserFriends ();
            $friendList = $friendModel->getFriendListForCurrentUser($userId);
        }
        /* below are implement the logic for send activity */
//        if (!isset($data['order_id']) || $data['order_id'] == '') {
            $feedDate = date('M d Y', strtotime($data ['time_slot']));
            $feedTime = date('h:i a', strtotime($data ['time_slot']));
            $replacementData = array('restaurant_name' => $data ['restaurant_name']);
            $otherReplacementData = array();
            $uname = (isset($data ['last_name']) && !empty($data ['last_name'])) ? $userName . " " . $data ['last_name'] : $userName;
            $feed = array(
                'restaurant_id' => $data ['restaurant_id'],
                'restaurant_name' => $data ['restaurant_name'],
                'user_name' => ucfirst($uname),
                'img' => array(),
                'reservation_time' => $feedTime,
                'reservation_date' => $feedDate,
                'no_of_people' => $data ['reserved_seats']
            );
            $activityFeed = $commonFunctiion->addActivityFeed($feed, 4, $replacementData, $otherReplacementData);
//        }
        # Assign muncher #
        //$user_function->userAvatar('reservation');
        ##################
        $orderTimeSlot = explode("-", ORDER_TIME_SLOT);
        $cappingMessage=(CRM_CAPPING)?'We process all orders and reservation between '.date("h:i A",strtotime($orderTimeSlot[0].":00")).' and '.date("h:i A",strtotime($orderTimeSlot[1])).' EST':'';
        
        $restDetails = $userFunctions->getRestOrderFeatures($data ['restaurant_id']);
        $clevertapData = array(
            "user_id"=>($userId && $userId!=0)?$userId:"",
            "name"=>$uname,
            "email"=>$data ['email'],
            "identity"=>$data ['email'],
            "date"=>StaticOptions::getFormattedDateTime($userReservationModel->time_slot, 'Y-m-d H:i', 'Y-m-d'),
            "time"=> StaticOptions::getFormattedDateTime($userReservationModel->time_slot, 'Y-m-d H:i', 'h:i A'),
            "restaurant_name"=>$data ['restaurant_name'],
            "restaurant_id"=>$data ['restaurant_id'],
            "seats"=>$userReservationModel->reserved_seats,
            "earned_points"=>$userPointTable,                
            "eventname"=>"reservation",
            "event"=>1,
            "reservation_id"=>$reservation ['id'],
            "delivery_enabled" => $restDetails['delivery'],
            "takeout_enabled" => $restDetails['takeout'],
            "is_register"=>($userId)?"yes":"no",
            "type"=>"reservation"

        );
            
        $userFunctions->createQueue($clevertapData, 'clevertap');
        
        return array(
            'reservation_id' => $reservation ['id'],
            'receipt_no' => $userReservationModel->receipt_no,
            'points' => $userPoints,
            'orderpoints' => $userPointTable,
            'friendList' => $friendList,
            'reserved_on' => $userReservationModel->reserved_on,
            'phone' => $userReservationModel->phone,
            'confirm' => $confirm,
            'capping_message'=>$cappingMessage,
            'dine_more_wards'=>$awardPoint,
            'restaurant_id'=>$data ['restaurant_id']
        );
    }

    public function getList() {
        // Get reservation data
        $reservationModel = new UserReservation ();
        $user_function = new UserFunctions ();       
        $user_invitation = new UserInvitation ();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $friendId = $this->getQueryParams('friendid', false);
        if ($isLoggedIn) {
            if ($friendId) {
                $userId = $friendId;
            } else {
                $userId = $session->getUserId();
            }
        } else {
            throw new \Exception('User detail not found', 404);
        }

        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $reservationStatus = isset($config ['constants'] ['reservation_status']) ? $config ['constants'] ['reservation_status'] : array();
        $page = $this->getQueryParams('page', 1);
        $limit = $this->getQueryParams('limit', SHOW_PER_PAGE);
        $orderReservation = $this->getQueryParams('order', 'date');
        $orderBy = 'user_reservations.time_slot DESC';
        if ($orderReservation == 'date') {
            $orderBy = 'user_reservations.time_slot DESC';
        } elseif ($orderReservation == 'restaurant') {
            $orderBy = 'user_reservations.restaurant_name ASC';
        }
        /*
         * Archive Reservation
         */
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
        $archiveCondition = array(
            'userId' => $userId,
            'offset' => $offset,
            'limit' => $limit,
            'currentDate' => $currentDate,
            'status' => array(
                $reservationStatus ['upcoming'],
                $reservationStatus ['archived'],
                $reservationStatus ['rejected'],
            ),
            'orderBy' => $orderBy
        );
        $archiveReservationResponse = $reservationModel->getReservationArchiceDetails($archiveCondition);

        $totalArchiveRecords = $archiveReservationResponse['archive_count'];
        unset($archiveReservationResponse['archive_count']);
        /*
         * Upcomming Reservation & Friend invitation
         */
        $reservation_id = $user_invitation->getAllUserInvitation(array(
            'columns' => array('reservation_id'),
            'where' => array('to_id' => $userId, 'msg_status' => array('0', '1')),
            'order' => array('created_on DESC')
                )
        );
        if ($reservation_id) {
            foreach ($reservation_id as $value) {
                $reservationIds [] = $value ['reservation_id'];
            }

            $upcomingCondition = array(
                'reservationIds' => $reservationIds,
                'offset' => $offset,
                'limit' => SHOW_PER_PAGE,
                'currentDate' => $currentDate,
                'status' => array(
                    $reservationStatus ['upcoming'],
                    $reservationStatus ['rejected'],
                    $reservationStatus ['confirmed']
                ),
                'orderBy' => 'time_slot ASC'
            );
            $upcomingInvitationReservationResponse = $reservationModel->getReservationUpcommingDetails($upcomingCondition);
        }
        // pr($upcomingInvitationReservationResponse,1);
        $upcomingCondition = array(
            'userId' => $userId,
            'offset' => $offset,
            'limit' => $limit,
            'currentDate' => $currentDate,
            'status' => array(
                $reservationStatus ['upcoming'],
                $reservationStatus ['rejected'],
                $reservationStatus ['confirmed']
            ),
            'orderBy' => 'time_slot ASC'
        );
        $upcomingReservationResponse = $reservationModel->getReservationUpcommingDetails($upcomingCondition);

        if (isset($upcomingInvitationReservationResponse)) {
            $upcomingReservationResponse = array_merge($upcomingInvitationReservationResponse, $upcomingReservationResponse);
        }

        $reservation = array();
        if ($archiveReservationResponse) {
            $reservation['archive_reservation'] = $archiveReservationResponse;
        } else {
            $reservation['archive_reservation'] = array();
        }

        if ($upcomingReservationResponse) {
            $reservation['upcomming_reservation'] = $upcomingReservationResponse;
        } else {
            $reservation['upcomming_reservation'] = array();
        }

        foreach ($reservation as $key => $reservationDetail) {
            foreach ($reservationDetail as $k => $v) {

                if ($v['is_restaurant_exist'] == 'Yes') {
                    $reservation[$key][$k]['is_restaurant_exist'] = true;
                } else {
                    $reservation[$key][$k]['is_restaurant_exist'] = false;
                }
                if (!$v['invitation_from_friend'])
                    $reservation[$key][$k]['invitation_from_friend'] = 0;
                if (!$v['reservation_is_invited'])
                    $reservation[$key][$k]['reservation_is_invited'] = 0;

                if ($v['invitation'] == "") {
                    $reservation[$key][$k]['invitation'] = array();
                }
            }
        }
        $reservation['total_archive_records'] = $totalArchiveRecords;

        return $reservation;
    }

    public function reservationAgain($data) {
        $userReservatioModel = new UserReservation ();
        $reservationId = $data['reservation_id'];
        $reservation = $userReservatioModel->getUserReservation(array(
            'columns' => array(
                'id',
                'receipt_no',
                'restaurant_id',
                'user_id',
                'time_slot',
                'reserved_seats',
                'party_size',
                'reserved_on',
                'user_instruction',
                'restaurant_name',
                'status',
                'first_name',
                'last_name',
                'phone',
                'email',
                'order_id'
            ),
            'where' => array(
                'id' => $reservationId
            )
        ));

        if (!$reservation) {
            throw new \Exception('Reservation details not found', 404);
        }
        $previousReservationDetail = $reservation[0];
        return $previousReservationDetail;
    }

    private function checkSmallBackword($dineinCalendarsDetail, $data) {
        $smallGroupSeatCount = 0;
        $smallGroupBackwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        $userReservationModel = new UserReservation();
        $noOfSlot = ($dineinCalendarsDetail['dinningtime_small'] - TIME_INTERVAL) / TIME_INTERVAL;

        if (is_float($noOfSlot)) {

            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }
        // print_r($noOfSlot);
        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60;
        //print_r($calculateHourToBack);
        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);
            // print_r($cHBArray);
            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60);

            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }
        //print_r($calculateHourToBackMinute); die();
        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute - $calculateHourToBackMinute) / 60; //60 is minute
        //  if(!is_int($timeSlotToCheckFrom)){
        if (strpos($timeSlotToCheckFrom, '.') !== false) {

            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            ///print_r($timeSlotToCheckFromArray); die();
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }

        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;


        $getSmallGroupReservation = array(
            "restaurant_id" => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'backword',
            "time_slot" => $data['time_slot'],
            "groupType" => "small",
            "smallGroupValue" => SMALL_GROUP_VALUE
        );

        $existingSmallGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getSmallGroupReservation);

        if (count($existingSmallGroupReservation) > 0) {
            foreach ($existingSmallGroupReservation as $key => $val) {
                $smallGroupBackwordSeatCount += $val['reserved_seats'];
            }
        }

        return $smallGroupBackwordSeatCount;
    }

    private function checkSmallForword($dineinCalendarsDetail, $data) {
        $userReservationModel = new UserReservation();
        $smallGroupSeatCount = 0;
        $smallGroupForwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        $noOfSlot = ($dineinCalendarsDetail['dinningtime_small'] - TIME_INTERVAL) / TIME_INTERVAL;

        if (is_float($noOfSlot)) {

            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }

        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60;

        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);

            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60); //+TIME_INTERVAL;

            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }

        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute + $calculateHourToBackMinute) / 60; //60 is minute
        // print_r($timeSlotToCheckFrom);
        /// print_r($timeSlotToCheckFromArray); die();
        //if(is_float($timeSlotToCheckFrom)){
        if (strpos($timeSlotToCheckFrom, '.') !== false) {
            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }

        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;


        $getSmallGroupReservation = array(
            "restaurant_id" => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'forword',
            "time_slot" => $data['time_slot'],
            "groupType" => "small",
            "smallGroupValue" => SMALL_GROUP_VALUE
        );

        $existingSmallGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getSmallGroupReservation);
        // echo "checkSmallForword"; print_r($existingSmallGroupReservation); die();
        if (count($existingSmallGroupReservation) > 0) {
            foreach ($existingSmallGroupReservation as $key => $val) {
                $smallGroupForwordSeatCount +=$val['reserved_seats'];
            }
        }
        return $smallGroupForwordSeatCount;
    }

    private function checkLargeBackword($dineinCalendarsDetail, $data) {
        $userReservationModel = new UserReservation();
        $largeGroupSeatCount = 0;
        $largeGroupBackwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        $noOfSlot = ($dineinCalendarsDetail['dinningtime_large'] - TIME_INTERVAL) / TIME_INTERVAL;
        if (is_float($noOfSlot)) {
            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }
        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60; //60 is minute
        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);
            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60); //+TIME_INTERVAL;
            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }


        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute - $calculateHourToBackMinute) / 60;
        if (strpos($timeSlotToCheckFrom, '.') !== false) {
            // if(is_float($timeSlotToCheckFrom)){
            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }


        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;

        $getLargeGroupReservation = array(
            'restaurant_id' => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'backword',
            "time_slot" => $data['time_slot'],
            "groupType" => "large",
            "smallGroupValue" => SMALL_GROUP_VALUE
        );

        $existingLargeGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getLargeGroupReservation);
        // echo "checkLargeBackword"; print_r($existingLargeGroupReservation); die();
        if (count($existingLargeGroupReservation) > 0) {
            foreach ($existingLargeGroupReservation as $key => $val) {
                $largeGroupBackwordSeatCount += $val['reserved_seats'];
            }
        }

        return $largeGroupBackwordSeatCount;
    }

    //this function checks the impact of backward reservation slots on future slots
    private function checkLargeBackwordImpact($dineinCalendarsDetail, $data) {
        $userReservationModel = new UserReservation();
        $largeGroupSeatCount = 0;
        $largeGroupBackwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        $noOfSlot = $dineinCalendarsDetail['dinningtime_large'] / TIME_INTERVAL;
        if (is_float($noOfSlot)) {
            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }
        $noOfSlot = $noOfSlot - 2;

        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60; //60 is minute
        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);
            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60); //+TIME_INTERVAL;
            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }

        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute - $calculateHourToBackMinute) / 60;
        if (strpos($timeSlotToCheckFrom, '.') !== false) {
            //if(is_float($timeSlotToCheckFrom)){
            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }


        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;

        $timeSlotToCheckUpto = ($requestedTimeInMinute - TIME_INTERVAL) / 60; //adding time interval in time slot for going to next time slot 
        if (is_float($timeSlotToCheckUpto)) {
            $timeSlotToCheckUptoArray = explode(".", $timeSlotToCheckUpto);
            if ($timeSlotToCheckUptoArray[1] > 5) {
                $$timeSlotToCheckUpto = ($timeSlotToCheckUptoArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckUptoArray[1] <= 5) {
                $timeSlotToCheckUpto = $timeSlotToCheckUptoArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckUpto = $timeSlotToCheckUpto . ":00";
        }

        $timeSlotToCheckUpto = $data ['date'] . " " . $timeSlotToCheckUpto;

        $getLargeGroupReservation = array(
            'restaurant_id' => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'backword',
            "time_slot" => $timeSlotToCheckUpto,
            "groupType" => "large",
            "smallGroupValue" => SMALL_GROUP_VALUE
        );

        $existingLargeGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getLargeGroupReservation);
        //print_r($existingLargeGroupReservation); die();
        if (count($existingLargeGroupReservation) > 0) {
            foreach ($existingLargeGroupReservation as $key => $val) {
                $largeGroupBackwordSeatCount += $val['reserved_seats'];
            }
        }

        return $largeGroupBackwordSeatCount;
    }

    private function checkLargeForword($dineinCalendarsDetail, $data) {
        $userReservationModel = new UserReservation();
        $largeGroupSeatCount = 0;
        $largeGroupForwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        if ($data['reserved_seats'] > SMALL_GROUP_VALUE) {
            $noOfSlot = ($dineinCalendarsDetail['dinningtime_large'] - TIME_INTERVAL) / TIME_INTERVAL;
        } else {
            $noOfSlot = ($dineinCalendarsDetail['dinningtime_small'] - TIME_INTERVAL) / TIME_INTERVAL;
        }

        if (is_float($noOfSlot)) {
            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }
        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60; //60 is minute
        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);
            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60); //+TIME_INTERVAL;
            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }


        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute + $calculateHourToBackMinute) / 60;
        //if(is_float($timeSlotToCheckFrom)){
        if (strpos($timeSlotToCheckFrom, '.') !== false) {
            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }


        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;

        $getLargeGroupReservation = array(
            'restaurant_id' => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'forword',
            "time_slot" => $data['time_slot'],
            "groupType" => "large",
            "smallGroupValue" => SMALL_GROUP_VALUE
        );

        $existingLargeGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getLargeGroupReservation);
        ///echo "checkLargeForword"; print_r($existingLargeGroupReservation); die();
        if (count($existingLargeGroupReservation) > 0) {
            foreach ($existingLargeGroupReservation as $key => $val) {
                $largeGroupForwordSeatCount += $val['reserved_seats'];
            }
        }

        return $largeGroupForwordSeatCount;
    }

    public function reservation_invited_by_friend($reservationId, $reservationDetail, $userId = false) {
        $reservationInvitationModel = new UserInvitation();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'u' => 'users'
            ),
            'on' => 'u.id = user_reservation_invitation.to_id',
            'columns' => array(
                'invited_name' => 'first_name',
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'ur' => 'user_reservations'
            ),
            'on' => 'ur.id = user_reservation_invitation.reservation_id',
            'columns' => array(
                'invitation_by_phone' => 'phone',
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'id',
                'invited_id' => 'to_id',
                'invitation_by' => 'user_id',
                'invited_email' => 'friend_email',
                'user_message' => 'message',
                'msg_status'
            ),
            'where' => array(
                'user_reservation_invitation.reservation_id' => $reservationId,
                'user_reservation_invitation.to_id' => $userId,
            ),
            'joins' => $joins
        );

        $reservationInvitationModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        if ($reservationInvitationModel->find($options)->toArray()) {
            $reservation_invited_by_friend = 1;
        } else {
            $reservation_invited_by_friend = 0;
        }

        return $reservation_invited_by_friend;
    }
    
    private function validate($data,$userId){
        if (!isset($data ['restaurant_id']) || empty($data ['restaurant_id'])) {
           throw new \Exception('Please provide restaurant id',400);
        }     
        
        if (!isset($data ['reserved_seats']) || empty($data ['reserved_seats']) ) {
            throw new \Exception('Please provide party size',400);
        }
        if (!isset($data ['time_slot']) || empty($data ['time_slot'])) {
            throw new \Exception('Please provide timeslot',400);
        }
        if (!isset($data ['first_name']) || empty($data ['first_name'])) {
            throw new \Exception('First name can not be empty',400);
        }
        if (!isset($data ['email']) || empty($data ['email'])) {
            throw new \Exception('Email can not be empty',400);
        }
        if (!isset($data ['phone']) || empty($data ['phone'])) {
            throw new \Exception('Phone can not be empty',400);
        }
        if (!isset($data ['restaurant_name']) || empty($data ['restaurant_name'])) {
            throw new \Exception('Restaurant name can not be empty',400);
        }
    }

}
