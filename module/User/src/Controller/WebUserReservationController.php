<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReservation;
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
use Restaurant\Model\RestaurantNotificationSettings;
use User\Model\UserSetting;
use Zend\Db\Sql\Predicate\Expression;
use User\Model\UserFriends;

class WebUserReservationController extends AbstractRestfulController {

    private $daysMapping = array(
        'mo' => 'mon',
        'tu' => 'tue',
        'we' => 'wed',
        'th' => 'thu',
        'fr' => 'fri',
        'sa' => 'sat',
        'su' => 'sun'
    );

    public function getList() {
        $response = array();
        $upcomingAllData = array();
        $upcomingAllData1 = array();
        $upcomingReservationAll = array();
        $archivelists = array();
        // Get reservation data
        $reservationModel = new UserReservation();
        $user_function = new UserFunctions();
        $restaurant_model = new Restaurant();
        $user_invitation = new UserInvitation();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $reservationStatus = isset($config['constants']['reservation_status']) ? $config['constants']['reservation_status'] : array();
        $page = $this->getQueryParams('page', 1);
        $type = $this->getQueryParams('type');
        $orderReservation = $this->getQueryParams('order', 'date');
        if (!$type) {
            throw new \Exception('Type not found', 404);
        }
        $orderBy = 'user_reservations.time_slot DESC';
        if ($orderReservation == 'date') {
            $orderBy = 'user_reservations.time_slot DESC';
        } elseif ($orderReservation == 'restaurant') {
            $orderBy = 'user_reservations.restaurant_name ASC';
        }
        /**
         * Get User Archive Reservation
         */
        if ($type == 'archive') {
            $offset = 0;
            if ($page > 0) {
                $page = ($page < 1) ? 1 : $page;
                $offset = ($page - 1) * (SHOW_PER_PAGE);
            }
            $archiveCondition = array(
                'userId' => $userId,
                'offset' => $offset,
                'limit' => SHOW_PER_PAGE,
                'currentDate' => $currentDate,
                'status' => array(
                    $reservationStatus['upcoming'],
                    $reservationStatus['archived'],
                    $reservationStatus['rejected']
                ),
                'orderBy' => $orderBy
            );
            $response = $reservationModel->getReservationDetails($archiveCondition);
            if ($response) {
                foreach ($response as $archivevalue) {
                    $archivevalue['reserved_on_readable'] = StaticOptions::getFormattedDateTime($archivevalue['time_slot'], 'Y-m-d H:i:s', 'M d, Y'); // date('M d, Y',strtotime($archivevalue['time_slot']));
                    $archivevalue['restaurant_title'] = $archivevalue['restaurant_name'];
                    $archivevalue['order_date'] = StaticOptions::getFormattedDateTime($archivevalue['reserved_on'], 'Y-m-d H:i:s', 'M d, Y'); // date("M d,Y", strtotime($archivevalue['reserved_on']));
                    $archivevalue['c_date'] = StaticOptions::getFormattedDateTime($archivevalue['reserved_on'], 'Y-m-d H:i:s', 'Y-m-d H:i:s'); // date("Y-m-d H:i:s", strtotime($archivevalue['reserved_on']));
                    $archivevalue['order_type'] = 'dine_in';
                    if ($archivevalue['is_reviewed'] == 0) {
                        $archivevalue['is_reviewed'] = "";
                    }
                    $archivevalue['is_live'] = 0;
                    $archivelists[] = $archivevalue;
                }
            }
            return $archivelists;
        } /**
         * Get User Upcoming Reservation
         */ elseif ($type == 'upcoming') {
            $upcomingCondition = array(
                'userId' => $userId,
                'currentDate' => $currentDate,
                'status' => array(
                    $reservationStatus['upcoming'],
                    $reservationStatus['rejected'],
                    $reservationStatus['confirmed']
                ),
                'orderBy' => 'time_slot ASC'
            );
            $upcomingReservation = $reservationModel->getReservationDetails($upcomingCondition);
            if ($upcomingReservation) {
                foreach ($upcomingReservation as $value) {
                    $reservedOnReadable = StaticOptions::getFormattedDateTime($value['reserved_on'], 'Y-m-d H:i:s', 'M d, Y');
                    $reservedOnDate = StaticOptions::getFormattedDateTime($value['reserved_on'], 'Y-m-d H:i:s', 'D, M d, Y');
                    $value['reserved_date'] = StaticOptions::getFormattedDateTime($value['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
                    $value['reserved_time'] = StaticOptions::getFormattedDateTime($value['time_slot'], 'Y-m-d H:i:s', 'h:i A');
                    $timeSlot = StaticOptions::getFormattedDateTime($value['time_slot'], 'Y-m-d H:i:s', 'H:i:s');
                    $value['type'] = "upcoming";
                    $value['sub_type'] = "";
                    if ($value['status'] === '3') {

                        $value['type'] = "rejected";
                    }
                    $value['restaurant_comment'] = isset($value['restaurant_comment']) ? $user_function->to_utf8($value['restaurant_comment']) : "";
                    $endTime = date('Y-m-d H:i:s', strtotime($value['time_slot'] . ' + 2 hours'));
                    $value['calendar']['start_date'] = (!empty($value['time_slot'])) ? StaticOptions::getFormattedDateTime($value['time_slot'], 'Y-m-d H:i:s', 'D, M d,Y h:i A') : null;
                    $value['calendar']['end_date'] = (!empty($value['time_slot'])) ? StaticOptions::getFormattedDateTime($value['time_slot'], 'Y-m-d H:i:s', 'D, M d,Y h:i A') : null; //(! empty($endTime)) ? date('D, M d,Y h:i A', strtotime($endTime)) : null;
                    $value['calendar']['title'] = (!empty($value['restaurant_name']) && $value['restaurant_name'] != null) ? 'Reservation in ' . $value['restaurant_name'] : null;
                    $value['calendar']['description'] = (!empty($value['restaurant_name']) && $value['restaurant_name'] != null && !empty($value['reserved_seats']) && $value['reserved_seats'] != null && $value['reserved_seats'] != null && !empty($value['reserved_seats'])) ? 'Reservation in ' . $value['restaurant_name'] . ' for ' . $value['reserved_seats'] : null;
                    $loc = (!empty($value ['address']) && $value ['address'] != null) ? $value ['address'] : null;
                    $cityName = (!empty($value ['city_name']) && $value ['city_name'] != null) ? $value ['city_name'] : "";
                    $value ['calendar'] ['location'] = $loc . "," . $cityName . "," . $value['zipcode'];
                    $value['is_live'] = 1;
                    // ########### My Invitation ################

                    $my_invitation_record = $user_invitation->getAllUserInvitation(array(
                        'columns' => array(
                            'id',
                            'to_id',
                            'friend_email',
                            'msg_status'
                        ),
                        'where' => array(
                            'user_id' => $userId,
                            'reservation_id' => $value['id']
                        ),
                        'order' => array(
                            'created_on DESC'
                        )
                    ));

                    if ($my_invitation_record) {
                        $value['slot'] = $user_function->getMealSlot($timeSlot);
                        $value['sub_type'] = "upcoming_host";
                        $value['invited_user'] = $user_function->InvitationFriendList($my_invitation_record);
                    } // end invited other people
                    // ###########End of My invitation################
                    $value = array_map(function ($i) {
                        return $i === null ? '' : $i;
                    }, $value);
                    $upcomingAllData[] = $value;
                } // foreach end
            }
            // ##############INVITATION ACCEPTED BY YOU###########################
            $accepted_invitation = $user_function->getReservationDetailInvitationAccepted($userId, UserInvitation::ACCEPTED, $currentDate, $reservationStatus);

            if ($accepted_invitation) {

                foreach ($accepted_invitation as $key => $invitation_accepted) {
                    $timeSlot = StaticOptions::getFormattedDateTime($invitation_accepted['time_slot'], 'Y-m-d H:i:s', 'H:i:s');
                    $invitation_accepted['slot'] = $user_function->getMealSlot($timeSlot);
                    $invitation_accepted['is_live'] = 1;
                    $upcomingAllData[] = $invitation_accepted;
                }
            }
            // ##################END INVITATION ACCEPT BY YOU###########################
            foreach ($upcomingAllData as $key => $value) {
                $sortDate[$key] = date('Y-m-d H:i:s', strtotime($value['time_slot']));
            }
            if ($upcomingAllData) {
                array_multisort($sortDate, SORT_ASC, $upcomingAllData);
            }
            $invitationReservations = $user_function->getReservationDetailInvitationAccepted($userId, UserInvitation::INVITE, $currentDate, $reservationStatus);

            if ($invitationReservations) {
                foreach ($invitationReservations as $key => $inviteRes) {
                    $inviteRes['is_live'] = 1;
                    $upcomingAllData1[] = $inviteRes;
                }
            }
            if (!empty($upcomingAllData1) && !empty($upcomingAllData)) {
                $upcomingReservationAll = array_merge($upcomingAllData1, $upcomingAllData);
            } elseif (!empty($upcomingAllData1)) {
                $upcomingReservationAll = $upcomingAllData1;
            } else {
                $upcomingReservationAll = $upcomingAllData;
            }
            return $upcomingReservationAll;
        } /**
         * Get User Archive Reservation count
         */ elseif ($type === 'archivecount') {
            $conditions = array(
                'userId' => $userId,
                'status' => array(
                    $reservationStatus['upcoming'],
                    $reservationStatus['archived'],
                    $reservationStatus['rejected']
                ),
                'currentDate' => $currentDate
            );
            $totalReservation = $reservationModel->getTotalReservation($conditions);

            return $totalReservation;
        } else {
            throw new \Exception('Type Not Found', 404);
        }
    }

    public function get($reservationId = 0) {
        $user_function = new UserFunctions();
        $session = $this->getUserSession();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        $reservationModel = new UserReservation();
        $userInvitationModel = new UserInvitation();
        $joins = array();
        $joins[] = array(
            'name' => array(
                'rc' => 'restaurant_calendars'
            ),
            'on' => 'rc.restaurant_id = user_reservations.restaurant_id',
            'columns' => array(
                'working_days' => new Expression('GROUP_CONCAT(rc.calendar_day)')
            ),
            'group' => 'rc.restaurant_id',
            'type' => 'left'
        );
        $joins[] = array(
            'name' => array('rs' => 'restaurants'),
            'on' => 'rs.id =  user_reservations.restaurant_id',
            'columns' => array('city_id', 'closed', 'inactive','accept_cc_phone'),
            'type' => 'inner'
        );

        $joins[] = array('name' => array('c' => 'cities'),
            'on' => 'c.id=rs.city_id',
            'columns' => array('city_name'),
            'type' => 'inner'
        );

        $reservation = $reservationModel->getUserReservation(array(
            'columns' => array(
                'id',
                'restaurant_id',
                'user_id',
                'time_slot',
                'reserved_seats',
                'party_size',
                'user_instruction',
                'restaurant_comment',
                'restaurant_name',
                'status',
                'first_name',
                'last_name',
                'phone',
                'email',
                'receipt_no',
                'reserved_on',
                'order_id'
            ),
            'joins' => $joins,
            'where' => array(
                'user_reservations.id' => $reservationId
            )
        ));

        if (!empty($reservation)) {
            $reservation = current($reservation);
            $reservation['readable_date'] = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y');
            $reservation['readable_time'] = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'h:i A');
            $reservation['invitee'] = $userInvitationModel->getFindInviteeNew($reservationId);
            if ($reservation['status'] == 2) {
                $reservation['is_live'] = 2;
            } elseif (strtotime($currentDate) < strtotime($reservation['time_slot'])) {
                $reservation['is_live'] = 1;
            } elseif (strtotime($currentDate) > strtotime($reservation['time_slot'])) {
                $reservation['is_live'] = 2;
            }

            $userInstruction = isset($reservation['user_instruction']) ? str_replace('||', "<br>", $reservation['user_instruction']) : "";

            $reservation['user_instruction'] = $userInstruction;
            $days = explode(",", $reservation['working_days']);
            $mapping = $this->daysMapping;
            $days = array_map(function ($val) use($mapping) {
                return $val = $mapping[$val];
            }, $days);
            $reservation['working_days'] = $days;
            $dateTime = explode(" ", $reservation['time_slot']);
            $reservation['date'] = $dateTime[0];
            $reservation['time'] = $dateTime[1];

            $reservation = array_map(function ($i) {
                return $i === null ? '' : $i;
            }, $reservation);
            if (isset($reservation['time_slot'])) {
                $reservation['time_slot'] = StaticOptions::getFormattedDateTime($reservation['time_slot'], 'Y-m-d H:i:s', 'Y-m-d H:i');
            }
            if (isset($reservation['time'])) {
                $reservation['time'] = StaticOptions::getFormattedDateTime($reservation['time'], 'H:i:s', 'H:i');
            }
            $userId = $session->getUserId();
            $userPoints = '';
            $reservation['friendList'] = array();
            if ($userId) {
                $userModel = new User ();
                $userData = $userModel->getUserDetail(array(
                    'column' => array(
                        'points'
                    ),
                    'where' => array(
                        'id' => $userId
                    )
                        ));
                $userPoints = $userData ['points'];
                $friendModel = new UserFriends();
                $friendList = $friendModel->getFriendListForCurrentUser($userId);
                $reservation['friendList'] = $friendList;
            }
            $reservation['points'] = $userPoints;
            $orderId = $reservation['order_id'];
            $orderDetails = array();
            if(isset($orderId) && !empty($orderId) && $orderId!=NULL){
                $orders = $this->getServiceLocator()->get("User\Controller\WebUserOrderController");
                $orderDetails = $orders->get($orderId);  
                $reservation['user_instruction']=$orderDetails['special_checks'];
            }
            $reservation['order_details']=$orderDetails;
            return $reservation;
        }
        return array();
    }

    public function update($id, $data) {
        $userReservatioModel = new UserReservation();
        $userReservationInviteModel = new UserInvitation();
        $restaurantModel = new Restaurant();
        $restaurantAccountModel = new RestaurantAccounts();
        $userModel = new User();
        $pointSourceModel = new PointSourceDetails();
        $userPointModel = new UserPoint();
        $userNotificationModel = new UserNotification();
        $dashboardNotificationModel = new UserDashboardNotification();
        $session = $this->getUserSession();
        $user_function = new UserFunctions();
        $restaurantNotiSettingsModel = new RestaurantNotificationSettings();
        $userSettingsModel = new UserSetting();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $pointID = isset($config['constants']['point_source_detail']) ? $config['constants']['point_source_detail'] : array();
        $isLoggedIn = $session->isLoggedIn();

        if ($isLoggedIn)
            $userReservatioModel->user_id = $session->getUserId();

        $userReservatioModel->id = $id;
        $type = $data['type'];
        $token = $data['token'];

        if ($type == 'cancel') {
            $userReservatioModel->status = 2;
            if ($userReservatioModel->id) {
                $reservationOldData = $userReservatioModel->getUserReservationCurrent(array(
                            'column' => array(
                                'restaurant_id',
                                'user_id',
                                'status'
                            ),
                            'where' => array(
                                'id' => $id
                            )
                        ))->getArrayCopy();
                $response = $userReservatioModel->cancelReservation();
            }
            if (!$response) {
                return array(
                    'response' => false
                );
            }
            
            if($response){
               $bookmarkModel = new \Bookmark\Model\RestaurantBookmark();
               $isAlreadyBookedmark = $bookmarkModel->isAlreadyBookmark(array(
                'type' => 'bt',
                'restaurant_id' => $data['restaurant_id'],
                'user_id' => $data['user_id']
                )); 
               if($isAlreadyBookedmark){
                   $bookmarkModel->id = $isAlreadyBookedmark[0]['id'];
                   $bookmarkModel->delete();
               }
            }

            $reservationData = $userReservatioModel->getUserReservationCurrent(array(
                        'column' => array(
                            'restaurant_id',
                            'user_id',
                            'status'
                        ),
                        'where' => array(
                            'id' => $id
                        )
                    ))->getArrayCopy();

            $restaurant = $restaurantModel->findRestaurant(array(
                'column' => array(
                    'restaurant_name'
                ),
                'where' => array(
                    'id' => $reservationData['restaurant_id']
                )
            ));
            $sendMailToHost = false;
            $sendMailToHost = $userSettingsModel->getUserSettingStatus($reservationData['user_id'], 'reservation');
            $sendCancelMailToHostArray = array(
                'receiver' => array(
                    $reservationData['email']
                ),
                'variables' => array(
                    'username' => $reservationData['first_name'],
                    'friendname' => 'you',
                    'numberOfPeople' => $reservationData['reserved_seats'],
                    'restaurantName' => $restaurant->restaurant_name,
                    'reservationDate' => StaticOptions::getFormattedDateTime($reservationData['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                    'reservationtime' => StaticOptions::getFormattedDateTime($reservationData['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                    'reservationLink' => WEB_URL . 'restaurants/view/' . $reservationData['restaurant_id']
                ),
                'subject' => 'Your Reservation at '.$restaurant->restaurant_name.' Was Successfully Canceled. Bummer.',
                'template' => 'send-cancel-reservation',
                'layout'=> 'email-layout/default_new'
            );
            if ($sendMailToHost) {
                $user_function->sendMails($sendCancelMailToHostArray);
            }
            $invitationData = $userReservationInviteModel->getAllUserInvitation(array(
                'where' => array(
                    'reservation_id' => $id,
                    'user_id' => $reservationData['user_id'],
                    'msg_status' => array(0, 1)
                )
            ));

            if (!empty($invitationData)) {
                foreach ($invitationData as $data) {
                    // print_r($data['friend_email']);die;
                    $sendMailToFriend = false;
                    if (!empty($data['friend_email'])) {
                        $userMailDetails = $userModel->getUserDetail(array(
                            'where' => array(
                                'email' => $data['friend_email']
                            )
                        ));
                        if (!empty($userMailDetails)) {
                            $friendFirstName = $userMailDetails['first_name'];
                            $userFriendId = $userMailDetails['id'];
                            $sendMailToFriend = $userSettingsModel->getUserSettingStatus($userFriendId, 'reservation');
                        } else {
                            $friendFirstName = '';
                        }
                        if (empty($friendFirstName)) {
                            $email_array = explode('@', $data['friend_email']);
                            if (!empty($email_array[0])) {
                                $friendFirstName = $email_array[0];
                            }
                        }
                        $sendCancelMailToFriendArray = array(
                            'receiver' => array(
                                $data['friend_email']
                            ),
                            'variables' => array(
                                'username' => $friendFirstName,
                                'friendname' => $reservationData['first_name'],
                                'numberOfPeople' => $reservationData['reserved_seats'],
                                'restaurantName' => $restaurant->restaurant_name,
                                'reservationDate' => StaticOptions::getFormattedDateTime($reservationData['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                                'reservationtime' => StaticOptions::getFormattedDateTime($reservationData['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                                'reservationLink' => WEB_URL . 'restaurants/view/' . $reservationData['restaurant_id']
                            ),
                            'subject' => ucfirst($reservationData ['first_name'])."'s EPIC Fail",
                            'template' => 'send-cancel-reservation-friends',
                            'layout' => 'email-layout/default_new'
                        );
                        if ($sendMailToFriend) {
                            $user_function->sendMails($sendCancelMailToFriendArray);
                        }
                    }
                    if (!empty($data['to_id'])) {
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
                        $options = array(
                        'columns' => array(
                                '*'
                            ),
                            'where' => array('users.email' => $data['friend_email']),
                            'joins' => $joins,
                        );
                        $userDetails = $userModel->getUserDetail($options);
                        $FirstName = $userDetails['first_name'];

                        $notificationMsg = "Hey " . ucfirst($FirstName) . ", " . ucfirst($reservationData['first_name']) . " had to cancel the reservation at " . ucfirst($restaurant->restaurant_name) . ". Dang.";
                        $channel = "mymunchado_" . $data['to_id'];
                        $notificationArray = array(
                            "msg" => $notificationMsg,
                            "channel" => $channel,
                            "userId" => $data['to_id'],
                            "type" => 'reservation',
                            "restaurantId" => $reservationData['restaurant_id'],
                            'curDate' => $currentDate,
                            'username'=>ucfirst($FirstName),
                            'first_name'=>ucfirst($reservationData['first_name']),
                            'restaurant_name'=>ucfirst($restaurant->restaurant_name),
                            'friend_name'=>ucfirst($reservationData ['first_name']),
                            'friend_id'=>$reservationData ['user_id'],
                            'reservation_id' => $id,
                            'is_friend' => 1,
                            'reservation_status' => $userReservatioModel->status
                        );
                        $notificationJsonArray = array('reservation_status' => $userReservatioModel->status,'is_friend' => 1,'reservation_id' => $id,'friend_name'=>ucfirst($reservationData ['first_name']),
                        'friend_id'=>$reservationData ['user_id'],'username'=>ucfirst($FirstName),
                                    'restaurant_name'=>ucfirst($restaurant->restaurant_name),'first_name'=>ucfirst($reservationData['first_name']),'user_id'=>$data['to_id'],'restaurant_id'=>$reservationData['restaurant_id']);
                        $response = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                        $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                    }
                }
            }
            /*
             * Send Mail to Restaurant owner Before mail send check with restaurant notification setting
             */
            $resData = $restaurantAccountModel->getRestaurantAccountDetail(array(
                'where' => array(
                    'restaurant_id' => $reservationData['restaurant_id']
                )
            ));
            $restaurantNotification = $restaurantNotiSettingsModel->getRestaurantNotificationSetting($reservationData['restaurant_id']);
            $sendMailToOwner = false;
            if ($restaurantNotification) {
                $reservationCancellation = $restaurantNotification['notification_setting']['new_reservation_received'];
                $sendMailToOwner = ($reservationCancellation == 0 || $reservationCancellation == NULL) ? false : true;
            } else {
                $sendMailToOwner = true;
            }
            if ($resData) {
                $sendCancelMailToOwnerArray = array(
                    'receiver' => array(
                        $resData['email']
                    ),
                    'variables' => array(
                        'ownername' => $resData['name'],
                        'username' => $reservationData['first_name'],
                        'numberOfPeople' => $reservationData['reserved_seats'],
                        'receiptNo' => $reservationData['receipt_no'],
                        'reservationDate' => StaticOptions::getFormattedDateTime($reservationData['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                        'reservationtime' => StaticOptions::getFormattedDateTime($reservationData['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                        'reservationLink' => WEB_URL . 'restaurants/view/' . $reservationData['restaurant_id']
                    ),
                    'subject' => 'Bad News About a Munch Ado Reservation',
                    'template' => 'send-cancel-reservation-owner'
                );
                if ($sendMailToOwner) {
                    /* mail template closed as per request by mohit and testing team */
                   // $user_function->sendMailsToRestaurant($sendCancelMailToOwnerArray);
                }
            }
            /**
             * Pub Nub Notification send to user and restaurant dashboard
             * Entry in both pubnub table
             */
            if (!empty($reservationData['user_id'])) {
                $notificationMsg = 'You’ve successfully canceled your reservation at ' . ucfirst($restaurant->restaurant_name) . '. Bummer.';
                $channel = "mymunchado_" . $reservationData['user_id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $reservationData['user_id'],
                    "type" => 'reservation',
                    "restaurantId" => $reservationData['restaurant_id'],
                    'reservation_id' => $id,
                    'curDate' => $currentDate,
                    'is_friend' => 0,
                    'username' => ucfirst($reservationData['first_name']),
                    'user_id' => $reservationData ['user_id'],
                    'reservation_status' => $userReservatioModel->status,
                    'restaurant_name'=>ucfirst($restaurant->restaurant_name)
                );
                $notificationJsonArray = array('username' => ucfirst($reservationData['first_name']),'is_friend' => 0,'user_id' => $reservationData ['user_id'],'reservation_status' => $userReservatioModel->status,'reservation_id' => $id,'restaurant_name'=>ucfirst($restaurant->restaurant_name),'restaurant_id'=>$reservationData['restaurant_id']);
                $response = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);

                $msg ="Your customer cancelled their reservation. Their loss!";
                $dashboardChannel = "dashboard_" . $reservationData['restaurant_id'];
                $dashboardArray = array(
                    "msg" => $msg,
                    "channel" => $dashboardChannel,
                    "userId" => $reservationData['user_id'],
                    "type" => 'reservation',
                    "restaurantId" => $reservationData['restaurant_id'],
                    'curDate' => $currentDate
                );
                $response = $userNotificationModel->createPubNubNotification($dashboardArray);
                $pubnub = StaticOptions::pubnubPushNotification($dashboardArray);
            }
            /*
             * Deduct point
             */

            if (!empty($reservationData['user_id']) && $reservationOldData['status'] == 4) {
                // deduct user points to user_points table
                $userData = $userModel->getUserDetail(array(
                    'column' => array(
                        'points'
                    ),
                    'where' => array(
                        'id' => $reservationData['user_id']
                    )
                ));
                $userPoints = $userData['points'];
                $userPoints = !empty($userPoints) ? $userPoints : 0;
                $friendInvitesModel = new UserInvitation();
                $inviteId = $pointID['reservationAccept'];
                $invitePoints = $pointSourceModel->getPointSourceDetail(array(
                    'column' => array(
                        'points'
                    )
                    ,
                    'where' => array(
                        'id' => $inviteId
                    )
                ));
                $acceptedReservation = $friendInvitesModel->getAcceptedInvition($id);
                $acceptedPoints = $acceptedReservation * $invitePoints['points'];
                $updateStausOfpoints = $userPointModel->updateAcceptedInvition($id);
                $points = $pointSourceModel->getPointSourceDetail(array(
                    'column' => array(
                        'points'
                    ),
                    'where' => array(
                        'id' => $pointID['reserveATable']
                    )
                ));

                $userModel->id = $reservationData['user_id'];
                $userTotalPoints = (int) $userPoints - (int) $points['points'] - (int) $acceptedPoints;
                $userModel->update(array(
                    'points' => $userTotalPoints
                ));

                $pointsData = $userPointModel->getUserPointDetail(array(
                    'where' => array(
                        'user_id' => $reservationData['user_id'],
                        'point_source' => $pointID['reserveATable'],
                        'ref_id' => $id
                    )
                ));
                $userPointModel->id = $pointsData['id'];

                if ($userPointModel->id)
                    $userPointModel->updateAttributes(array(
                        'status' => 2
                    ));
            }
            return array(
                'response' => true
            );
        } elseif ($type == 'modify') {

            $userReservatioModel->reserved_seats = $data['requested_seats'];
            $userReservatioModel->party_size = $data['requested_seats'];
            $userReservatioModel->first_name = isset($data['first_name']) ? $data['first_name'] : '';
            $userReservatioModel->last_name = isset($data['last_name']) ? $data['last_name'] : '';
            $userReservatioModel->email = isset($data['email']) ? $data['email'] : '';

            if (!$userReservatioModel->email)
                throw new \Exception("Email id is required", 405);

            $userReservatioModel->phone = isset($data['phone']) ? $data['phone'] : '';
            if (!$userReservatioModel->phone)
                throw new \Exception("Phone number is required", 405);

            $userReservatioModel->status = 1;
            $userReservatioModel->is_read = 0;
            $reservedDate = $data['date'];
            $reservedTime = StaticOptions::getFormattedDateTime($data['time'], 'H:i:s', 'H:i:s');
            $userReservatioModel->time_slot = $reservedDate . ' ' . $reservedTime;
            if ($userReservatioModel->id) {
                $existData = $userReservatioModel->getUserReservation(array(
                            'columns' => array(
                                'time_slot',
                                'reserved_seats',
                                'status'
                            ),
                            'where' => array(
                                'id' => $userReservatioModel->id
                            )
                        ))->getArrayCopy();

                $response = $userReservatioModel->updateUserReservation();
            }
            if (!$response) {
                return array(
                    'response' => false
                );
            }
            /* time slot and reserved seats get compared with exisiting respectively */
            $timeSlotMatch = strcmp($response['time_slot'], $existData['time_slot']);
            $seatMatch = strcmp($response['reserved_seats'], $existData['reserved_seats']);
            if ($timeSlotMatch === 0 && $seatMatch === 0) {
                // do nothing
            } else {
                /**
                 * Send modify reservation mail to user
                 */
                //$sendMail = $userModel->checkUserForMail($existData['user_id'], 'reservation');
                //if ($sendMail == true) {
                    $sendModifyReservationMail = array(
                        'receiver' => array(
                            $data['email']
                        ),
                        'variables' => array(
                            'name' => $data['first_name'],
                            'restaurantName' => $existData['restaurant_name'],
                            'receiptNo' => $existData['receipt_no'],
                            'web_url' => WEB_URL,
                            'reservationDate' => StaticOptions::getFormattedDateTime($response['time_slot'], 'Y-m-d H:i', 'D, M d, Y'),
                            'reservationTime' => StaticOptions::getFormattedDateTime($response['time_slot'], 'Y-m-d H:i', 'h:i A'),
                            'oldReservationDate' => StaticOptions::getFormattedDateTime($existData['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                            'oldReservationTime' => StaticOptions::getFormattedDateTime($existData['time_slot'], 'Y-m-d H:i:s', 'h:i A')
                        ),
                        'subject' => 'Your Modifications Were Successful',
                        'template' => 'modify-reservation'
                    );
                    $user_function->sendMails($sendModifyReservationMail);
                //}
                /**
                 * Send modify reservation mail to user friends
                 */
                $invitationData = $userReservationInviteModel->getAllUserInvitation(array(
                    'where' => array(
                        'reservation_id' => $id,
                        'user_id' => $existData['user_id']
                    )
                ));
                if (!empty($invitationData)) {
                    foreach ($invitationData as $data) {
                        //$sendMail = $userModel->checkUserForMail($data['to_id'], 'reservation');
                        //if ($sendMail == true) {
                            if (!empty($data['friend_email'])) {
                                $userMailDetails = $userModel->getUserDetail(array(
                                    'where' => array(
                                        'email' => $data['friend_email']
                                    )
                                ));
                                if (!empty($userMailDetails)) {
                                    $friendFirstName = $userMailDetails['first_name'];
                                } else {
                                    $friendFirstName = '';
                                }
                                if (empty($friendFirstName)) {
                                    $email_array = explode('@', $data['friend_email']);
                                    if (!empty($email_array[0])) {
                                        $friendFirstName = $email_array[0];
                                    }
                                }
                                $sendCancelMailToFriendArray = array(
                                    'receiver' => array(
                                        $data['friend_email']
                                    ),
                                    'variables' => array(
                                        'username' => $friendFirstName,
                                        'friendname' => $existData['first_name'],
                                        'web_url' => WEB_URL,
                                        'numberOfPeople' => $existData['reserved_seats'],
                                        'restaurantName' => $existData['restaurant_name'],
                                        'reservationDate' => StaticOptions::getFormattedDateTime($existData['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                                        'reservationtime' => StaticOptions::getFormattedDateTime($existData['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                                        'reservationLink' => WEB_URL . 'restaurants/view/' . $existData['restaurant_id'],
                                        
                                    ),
                                    'subject' => 'Update Your Calendar',
                                    'template' => 'modify-reservation-to-friends',
                                    'layout' => 'email-layout/default_update_reservation'
                                );
                                $user_function->sendMails($sendCancelMailToFriendArray);
                            }
                        //}
                        if (!empty($data['to_id'])) {
                            $userDetails = $userModel->getUserDetail(array(
                                'where' => array(
                                    'email' => $data['friend_email']
                                )
                            ));

                            $userName = $userDetails['first_name'];
                            $notificationMsg = "Hey " . ucfirst($userName) . "! the reservation details at " . ucfirst($existData['restaurant_name']) . " with ".  ucfirst($existData ['first_name'])." have changed. Reconfirm if you're going or let them know if you're bailing.";
                            $channel = "mymunchado_" . $data['to_id'];
                            $notificationArray = array(
                                "msg" => $notificationMsg,
                                "channel" => $channel,
                                "userId" => $data['to_id'],
                                "type" => 'reservation',
                                "restaurantId" => $existData['restaurant_id'],
                                'curDate' => $currentDate,'username'=>ucfirst($userName),'restaurant_name'=>ucfirst($existData['restaurant_name']),'friend_name'=>ucfirst($existData ['first_name']),'friend_id'=>$existData ['user_id']
                            );
                            $notificationJsonArray = array('friend_name'=>ucfirst($existData ['first_name']),'friend_id'=>$existData ['user_id'],'username'=>ucfirst($userName),'restaurant_name'=>ucfirst($existData['restaurant_name']),'user_id'=>$data['to_id'],'restaurant_id'=>$existData['restaurant_id']);
                            $response = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
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
                        'restaurant_id' => $existData['restaurant_id']
                    )
                ));
                $sendMailToRestaurant = $restaurantAccountModel->checkRestaurantForMail($existData['restaurant_id'], 'reservation');
                if ($sendMailToRestaurant == true) {
                    $sendCancelMailToOwnerArray = array(
                        'receiver' => array(
                            $resData['email']
                        ),
                        'variables' => array(
                            'ownername' => $resData['name'],
                            'username' => $existData['first_name'],
                            'numberOfPeople' => $existData['reserved_seats'],
                            'receiptNo' => $existData['receipt_no'],
                            'reservationDate' => StaticOptions::getFormattedDateTime($existData['time_slot'], 'Y-m-d H:i:s', 'D, M d, Y'),
                            'reservationtime' => StaticOptions::getFormattedDateTime($existData['time_slot'], 'Y-m-d H:i:s', 'h:i A'),
                            'reservationLink' => WEB_URL . 'restaurants/view/' . $existData['restaurant_id']
                        ),
                        'subject' => 'One of Your Reservations Has Changed',
                        'template' => 'modify-reservation-to-owner'
                    );
                    $user_function->sendMailsToRestaurant($sendCancelMailToOwnerArray);
                }
                if ($userReservatioModel->user_id) {
                    $notificationMsg = 'Your modifications have been sent to '.ucfirst($existData['restaurant_name']).'. Hopefully they can accommodate!';
                    $channel = "mymunchado_" . $userReservatioModel->user_id;
                    $notificationArray = array(
                        "msg" => $notificationMsg,
                        "channel" => $channel,
                        "userId" => $userReservatioModel->user_id,
                        "type" => 'reservation',
                        "restaurantId" => $existData['restaurant_id'],
                        "restaurant_id" => $existData['restaurant_id'],
                        "restaurant_name" => ucfirst($existData['restaurant_name']),
                        'curDate' => $currentDate
                    );
                    $notificationJsonArray = array("restaurant_id" => $existData['restaurant_id'],"restaurant_name" => ucfirst($existData['restaurant_name']));
                    $response = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                    $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
                }
                $notificationMsg = 'A customer modified their reservation. Review it now.';
                $channel = "dashboard_" . $existData['restaurant_id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $userReservatioModel->user_id,
                    "type" => 'reservation',
                    "restaurantId" => $existData['restaurant_id'],
                    'curDate' => $currentDate
                );
                //$response = $userNotificationModel->createPubNubNotification($notificationArray);
                //$pubnub = StaticOptions::pubnubPushNotification($notificationArray);

                if ($existData['status'] == 4) {
                    // deduct user points to user_points table
                    $userData = $userModel->getUserDetail(array(
                        'column' => array(
                            'points'
                        ),
                        'where' => array(
                            'id' => $reservationData['user_id']
                        )
                    ));
                    $userPoints = $userData['points'];
                    $userPoints = !empty($userPoints) ? $userPoints : 0;

                    $friendInvitesModel = new UserInvitation();
                    $inviteId = $pointID['reservationAccept'];
                    $invitePoints = $pointSourceModel->getPointSourceDetail(array(
                        'column' => array(
                            'points'
                        )
                        ,
                        'where' => array(
                            'id' => $inviteId
                        )
                    ));
                    $acceptedReservation = $friendInvitesModel->getAcceptedInvition($id);
                    $acceptedPoints = $acceptedReservation * $invitePoints['points'];
                    $updateStausOfpoints = $userPointModel->updateAcceptedInvition($id);
                    $points = $pointSourceModel->getPointSourceDetail(array(
                        'column' => array(
                            'points'
                        ),
                        'where' => array(
                            'id' => $pointID['reserveATable']
                        )
                    ));

                    $userModel->id = $reservationData['user_id'];
                    $userTotalPoints = (int) $userPoints - (int) $points['points'] - (int) $acceptedPoints;
                    $userModel->update(array(
                        'points' => $userTotalPoints
                    ));

                    $pointsData = $userPointModel->getUserPointDetail(array(
                        'where' => array(
                            'user_id' => $reservationData['user_id'],
                            'point_source' => $pointID['reserveATable'],
                            'ref_id' => $id
                        )
                    ));
                    $userPointModel->id = $pointsData['id'];

                    if ($userPointModel->id)
                        $userPointModel->updateAttributes(array(
                            'status' => 2
                        ));
                }
            }

            return array(
                'response' => true
            );
        } else {
            throw new \Exception('Type Not Found', 404);
        }
    }

}
