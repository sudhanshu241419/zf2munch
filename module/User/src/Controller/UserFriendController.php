<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\UserFriends;
use User\Model\User;
use MCommons\StaticOptions;
use User\Model\UserFriendsInvitation;
use User\Model\PointSourceDetails;
use User\Model\UserPoint;
use User\Model\UserNotification;
use Zend\Db\Sql\Expression;
use User\Model\UserEatingHabits;
use Home\Model\City;
use User\Model\UserOrder;
use User\Model\UserReferrals;
use User\Model\UserTransactions;

class UserFriendController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $data['referral_info'] = array();
        $data['invitation'] = array();
        $data['pending_invitation'] = array();
        $data['user_dine_restaurant'] = array();
        $data['referral_code'] = '';
        $userFriendModel = new UserFriends();
        $userFunctions = new UserFunctions();
        $userFriendInvitations = new UserFriendsInvitation();
        //$pointSourceModel = new PointSourceDetails();
        $cityModel = new City();
        $session = $this->getUserSession();
        $friendId = $this->getQueryParams('friendid', false);
        $isLoggedIn = $session->isLoggedIn();
        
        if ($isLoggedIn) {
            if ($friendId) {
                $userId = $friendId;
            } else {
                $userId = $session->getUserId();
                $userModel=new User();
                $userEmailData = $userModel->getUserEmail($userId);
                $userEmail=$userEmailData['email'];
            }
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $loginUserId = $session->getUserId();
        if (!$friendId) {
            $data['my_friends'] = array();
        } else {
            $data['friends_friend'] = array();
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $data1 = "";
        $userPoints = new User();
        $data1 = current($userPoints->countUserPoints($userId));
        $pintSourceDetail = isset($config['constants']['point_source_detail']) ? $config['constants']['point_source_detail'] : array();
        $inviteFriend = $pintSourceDetail['inviteFriends'];
        $orderby = $this->getQueryParams('orderby', 'date');
        $page = $this->getQueryParams('page', 1);
        $limit = $this->getQueryParams('limit', SHOW_PER_PAGE);
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
        /**
         * Get Usre Friends List
         */
        //pr($userId,true);
        $currentUserOrderPlaced = $this->hasPlacedOrder($userId);
        $friends = $userFriendModel->getUserFriendList($userId, $orderby);
        $ur_model = new \User\Model\UserReferrals();
        $referredUsersList = $ur_model->getReferredUsersArr($userId);
        $earningAndCycles = $this->getUserReferralEarningAndCycles($userId);
        if (!empty($friends) && $friends != null) {
            $i = 0;
            if (!$friendId) {
                $total_friends = count($friends);
                $data['total_friends'] = $total_friends;
            }
            foreach ($friends as $key => $value) {
                //if($friendId && $loginUserId != $value['friend_id']){                       
                if ($friendId) {
                    $value['display_pic_url'] = $userFunctions->findImageUrlNormal($value['display_pic_url'], $value['friend_id']);
                    $data['friends_friend'][$i] = $value;
                    if ($value['city_id'] != null && !empty($value['city_id'])) {
                        $cityDetails = $cityModel->fetchCityDetails($value['city_id']);
                        $data['friends_friend'][$i]['city'] = $cityDetails['city_name'];
                    } else {
                        $data['friends_friend'][$i]['city'] = NULL;
                    }
                    $data['referral_code'] = $this->getUserReferralCode($value['friend_id']);
                    $data['order_placed'] = (int)$this->hasPlacedOrder($value['friend_id']);
                    $data['is_referred'] = intval(in_array(intval($value['friend_id']), $referredUsersList));
                } elseif (!$friendId) {
                    $value['display_pic_url'] = $userFunctions->findImageUrlNormal($value['display_pic_url'], $value['friend_id']);
                    $data['my_friends'][$i] = $value;
                    if ($value['city_id'] != null && !empty($value['city_id'])) {
                        $cityDetails = $cityModel->fetchCityDetails($value['city_id']);
                        $data['my_friends'][$i]['city'] = $cityDetails['city_name'];
                    } else {
                        $data['my_friends'][$i]['city'] = NULL;
                    }
                    $data['my_friends'][$i]['referral_code'] = $this->getUserReferralCode($value['friend_id']);
                    $data['my_friends'][$i]['order_placed'] = (int)$this->hasPlacedOrder($value['friend_id']);
                    $data['my_friends'][$i]['is_referred'] = intval(in_array(intval($value['friend_id']), $referredUsersList));
                }
                $i++;
            }
        }
        if (!$friendId) {
            $comingInvitation = $userFriendInvitations->getUserInvitationList($userEmail, $orderby);

            if (!empty($comingInvitation) && $comingInvitation != null) {
                $i = 0;
                foreach ($comingInvitation as $k => $val) {
                    $val['display_pic_url'] = $userFunctions->findImageUrlNormal($val['display_pic_url'], $val['user_id']);
                    $data['invitation'][$i] = $val;
                    if ($val['city_id'] != null && !empty($val['city_id'])) {
                        $cityDetails = $cityModel->fetchCityDetails($val['city_id']);
                        $data['invitation'][$i]['city'] = $cityDetails['city_name'];
                    } else {
                        $data['invitation'][$i]['city'] = NULL;
                    }

                    $i++;
                }
            }
            $pendingInvitation = $userFriendInvitations->getComingInvitationList($userId, $orderby);

            if (!empty($pendingInvitation) && $pendingInvitation != null) {
                $i = 0;
                foreach ($pendingInvitation as $ky => $val1) {
                    $val1['display_pic_url'] = $userFunctions->findImageUrlNormal($val1['display_pic_url'], $val1['user_id']);
                    $data['pending_invitation'][$i] = $val1;
                    if ($val1['city_id'] != null && !empty($val1['city_id'])) {
                        $cityDetails = $cityModel->fetchCityDetails($val1['city_id']);
                        $data['pending_invitation'][$i]['city'] = $cityDetails['city_name'];
                    } else {
                        $data['pending_invitation'][$i]['city'] = NULL;
                    }
                    $i++;
                }
            }
        }
        if (!empty($data['my_friends'])) {
            $myfriends = array_slice($data['my_friends'], $offset, $limit);
            $data['my_friends'] = $myfriends;
        }
        if (!empty($userId)) {           
            $data['referral_info']['referral_left'] = $this->getUserReferralOrderRemainingCount($userId);
            $data['referral_info']['referral_earning'] = $earningAndCycles['earning'];
            $data['referral_info']['referral_cycles'] = $earningAndCycles['cycles'] + 1;
            $restaurantServer = new \User\Model\RestaurantServer();
            $userDineAndMoreRestaurant = $restaurantServer->userDineAndMoreRestaurant($userId);
            $commonFunctions = new \MCommons\CommonFunctions();
            $commonFunctions->replaceParticulerKeyValueInArray($userDineAndMoreRestaurant);
            
            $count = count($userDineAndMoreRestaurant);
            $dineAmdMoreMunchado = array(
                "code" => MUNCHADO_DINE_MORE_CODE,
                "restaurant_id" => "",
                "restaurant_name" => "Munch Ado",
                "restaurant_image_name" =>"",
                "rest_code" => "",
                "tag_id" => "",
                "rest_short_url" =>"" );
            array_push($userDineAndMoreRestaurant,$dineAmdMoreMunchado);
//            
            $data['user_dine_restaurant'] = $userDineAndMoreRestaurant;
            $data['referral_code'] = $this->getUserReferralCode($userId);
        }  
        
        return $data;
    }

    /**
     * get user detail with statistics
     */
    public function get($userId) {
        $friendDetail = array();
        $userModel = new User();
        $userFriendModel = new UserFriends();
        $userFunctionModel = new UserFunctions();
        $UserRestaurantImageModel = new \User\Model\UserRestaurantimage();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId1 = $session->getUserId();
            $userEmail = $session->getUserDetail('email');
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctionModel->userCityTimeZone($locationData);
        $friendDetail = $userModel->getUserDetailWithStatistics($userId, $userId1);

        if ($friendDetail) {
//            if ($friendDetail['last_login']) {
//                $friendDetail['last_login'] = $userFunctionModel->timeLater($currentDate, $friendDetail['last_login'], 'ago');
//            }
            $friendDetail = array_map(function ($i) {
                return $i === null ? '' : $i;
            }, $friendDetail);
        } else {
            throw new \Exception('Details not found', 404);
        }

        $friendDetail['profile_pic'] = $userFunctionModel->findImageUrlNormal($friendDetail['display_pic_url'], $userId);
        $friendDetail = array_map(function ($i) {
            return $i === null ? '' : $i;
        }, $friendDetail);

        if (isset($friendDetail['city_id']) && !empty($friendDetail['city_id']) && $friendDetail['city_id'] != null) {
            $cityModel = new City ();
            $cityDetails = $cityModel->cityDetails($friendDetail['city_id']);
            $cityName = $cityDetails[0]['city_name'];
        } else {
            $cityName = '';
        }


        $userFrExist = $userFriendModel->getFriendStatus(array(
            'where' => array(
                'user_id' => $userId1,
                'friend_id' => $userId,
                'status' => 1
            )
        ));


        $friendData = array();
        if ($userFrExist) {
            $friendData['is_friend'] = 'true';
            $friendData['friend_on'] = $userFrExist['created_on'];
            $friendData['friend_request_sent'] = null;
            $friendData['friend_request_sent_date'] = null;
            $friendData['friend_request_identifier'] = null;
            $friendData['friend_request_id'] = (int)$userFrExist['id'];
        } else {
            $friendData['is_friend'] = 'false';
            $friendData['friend_on'] = null;
            $userFriendInvitations = new UserFriendsInvitation();
            $pendingInvitation = $userFriendInvitations->isUserInvited($userId1, $friendDetail['email']);
            if ($pendingInvitation) {
                $friendData['friend_request_sent'] = 'true';
                $friendData['friend_request_identifier'] = 'inviter';
                $friendData['friend_request_id'] = (int)$pendingInvitation[0]['id'];
                $friendData['friend_request_sent_date'] = $pendingInvitation[0]['created_on'];
            } else {
                $pendingInvitation = $userFriendInvitations->isUserInvited($userId, $userEmail);
                if ($pendingInvitation) {
                    $friendData['friend_request_sent'] = 'true';
                    $friendData['friend_request_identifier'] = 'invitee';
                    $friendData['friend_request_id'] = (int)$pendingInvitation[0]['id'];
                    $friendData['friend_request_sent_date'] = $pendingInvitation[0]['created_on'];
                } else {
                    $friendData['friend_request_sent'] = 'false';
                    $friendData['friend_request_sent_date'] = null;
                    $friendData['friend_request_id'] = (int)0;
                }
            }
        }
        $friendDetail['points'] = $friendDetail['my_points'];
        $friendDetail['friend_info'] = $friendData;

        $userEatingHabits = new UserEatingHabits();
        $eatingHabitResponse = $userEatingHabits->findUserEatingHabits($userId);
        if ($eatingHabitResponse) {
            $friendProfile_attribute['favorite_beverage'] = isset($eatingHabitResponse->favorite_beverage) ? $eatingHabitResponse->favorite_beverage : NULL;
            $friendProfile_attribute['where_do_you_go'] = isset($eatingHabitResponse->where_do_you_go) ? $eatingHabitResponse->where_do_you_go : NULL;
            $friendProfile_attribute['comfort_food'] = isset($eatingHabitResponse->comfort_food) ? $eatingHabitResponse->comfort_food : NULL;
            $friendProfile_attribute['favorite_food'] = isset($eatingHabitResponse->favorite_food) ? $eatingHabitResponse->favorite_food : NULL;
            $friendProfile_attribute['dinner_with'] = isset($eatingHabitResponse->dinner_with) ? $eatingHabitResponse->dinner_with : NULL;
        } else {
            $friendProfile_attribute = NULL;
        }

        $friendDetail['profile_attributes'] = $friendProfile_attribute;
        $mymuncher = $userFunctionModel->getMyLastEarnedMuncher($userId);
        $friendDetail['munchers'] = isset($mymuncher['total_earned']) ? (int) $mymuncher['total_earned'] : (int) 0;
        $friendDetail['city'] = $cityName;
        $friendDetail['checkins'] = $friendDetail['total_checkin'];
        unset($friendDetail['total_checkin']);
        $optRestaurantImages = array(
            'columns' => array('total_images' => new Expression("count(id)")),
            'where' => array('user_id' => $userId)
        );
        $UserRestaurantImageModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $myRestaurantImages = $UserRestaurantImageModel->find($optRestaurantImages)->toArray();
        $myRestaurantTotalImage = $userModel->userTotalImages($userId);
        $friendDetail['total_photos'] = (int) $myRestaurantImages[0]['total_images'] + (int) $myRestaurantTotalImage;
        $friendDetail['join_date'] = StaticOptions::getFormattedDateTime($friendDetail['created_at'], 'M d, Y', 'Y-m-d H:i:s');
        $friendDetail['background_img'] = (isset($friendDetail['wallpaper']) && $friendDetail['wallpaper'] != "") ? WEB_URL . USER_IMAGE_WALLPAPER . DS . $userId . DS . $friendDetail['wallpaper'] : Null;
        $friendDetail['last_muncher_earned'] = array('title' => 'General Muncher', 'identifier' => 'generalMuncher');
        $friendDetail['group_reservation_count'] = $friendDetail['reservation_with_count'];
        unset($friendDetail['my_points'], $friendDetail['created_at'], $friendDetail['reservation_with_count'], $friendDetail['display_pic_url'], $friendDetail['display_pic_url_normal'], $friendDetail['display_pic_url_large']);
        return $friendDetail;
    }

    /**
     * Send Friends Invitations
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::create()
     */
    public function create($data) {
        $userModel = new User();
        $userFunctions = new UserFunctions();
        $mailText = "";
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $userEmail = $session->getUserDetail('email');
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        if ($isLoggedIn)
            $userId = $session->getUserId();
            $userDetails = $userModel->getUserDetail(array(
            'column' => array(
                'first_name','last_name','email'
            ),
            'where' => array(
                'id' => $userId
            )
        ));
        if (!empty($userDetails) && $userDetails != null) {
            $user = $userDetails->getArrayCopy();
            $userName = $user['first_name'].' '.$user['last_name'];
            $userEmail = $user['email'];
        } else {
            $userEmail = explode("@", $userEmail);
            $userName = $userEmail[0];
        }
        if (empty($data['friendsEmailAddress'])) {
            throw new \Exception('Invalid Paramters ', 404);
        }

        $friendMessage = (isset($data['friendsMessage']) && !empty($data['friendsMessage'])) ? $data['friendsMessage'] : '';
        $emailids = explode(',', $data['friendsEmailAddress']);
        //pr($emailids,1);
        // $emailids[] = $data['friendsEmailAddress'];

        foreach ($emailids as $email) {
            if (!empty($email) && ($userEmail != $email)) {
                $invitation = $this->inviteFriends($userId, $userName, $currentDate, $email, $friendMessage);
            }
        }
        if (count($emailids) == 1) {
            $response = array('success' => true, 'invitation_id' => $invitation);
        } else {
            $response = array('success' => true, 'invitation_id' => "0");
        }
        return $response;
    }

    /**
     * Create Friends Invitations And Send Mail To Friends
     *
     * @param unknown $userId            
     * @param unknown $userName            
     * @param unknown $currentDate            
     * @param unknown $email            
     * @param unknown $data            
     * @return True
     */
    private function inviteFriends($userId, $userName, $currentDate, $email, $data) {
        $userModel = new User();
        $frequestId = 0;
        $userFriendsInvitationModel = new UserFriendsInvitation();
        $date = strtotime($currentDate);
        $expiredOn = strtotime("+7 day", $date);
        $expiredOn = date('Y-m-d H:i:s', $expiredOn);
        $userDetails = $userModel->getUserDetail(array(
            'column' => array(
                'first_name','last_name',
                'id'
            ),
            'where' => array(
                'email' => $email,
                'status' => 1
            )
        ));
        $friendOptions = array(
            'columns' => array('id'),
            'where' => array('user_id' => $userId, 'email' => $email, 'invitation_status' => array(0, 1))
        );
        $getFriendExists = $userFriendsInvitationModel->find($friendOptions)->toArray();

        $checkUnfriendOptions = array(
            'columns' => array('id'),
            'where' => array('user_id' => $userId, 'email' => $email, 'invitation_status' => array(2, 3))
        );
        $getUnFriendExists = $userFriendsInvitationModel->find($checkUnfriendOptions)->toArray();
        if (count($getUnFriendExists) > 0) {
            $user = '';
            $inDBFriend = false;
            if (!empty($userDetails) && $userDetails != null) {
                $user = $userDetails->getArrayCopy();
                $friendName = $user['first_name'].' '.$user['last_name'];
                $inDBFriend = true;
            } else {
                $userEmail = explode("@", $email);
                $friendName = $userEmail[0];
                $inDBFriend = false;
            }
            /**
             * Send to Pubnub For All Friends
             */
            $userNotificationModel = new UserNotification();
            if ($inDBFriend == true) {
                $notificationMsg = ucfirst($userName) . ' would like to be friends with you and share in the common quest to find the best eats in town.';
                $channel = "mymunchado_" . $userDetails['id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $userDetails['id'],
                    'curDate' => $currentDate,
                    'type' => 'invite_friends',
                    "user_id" => $userId,
                    "restaurantId" => '',
                    "friend_request_id" => $userDetails['id'],
                    'username' => ucfirst($userName)
                );
                $notificationJsonArray = array("user_id" => $userId, "friend_request_id" => $userDetails['id'], 'username' => ucfirst($userName));
                $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
            }

            $where = array('id' => $getUnFriendExists[0]['id']);
            $insertData = $userFriendsInvitationModel->updateUserInvitation($where);
            
            $frequestId = $getUnFriendExists[0]['id'];

            $mailText = $data;
            $config = $this->getServiceLocator()->get('Config');
            $webUrl = PROTOCOL . $config['constants']['web_url'];
            $acceptLink = $webUrl . DS . 'friendInvitation' . DS . $insertData;
            //$acceptLink = $this->getBaseUrl() . DS . 'wapi' . DS . 'user' . DS . 'accepted' . DS . $insertData . '?token=' . $this->getUserSession()->token;
            $template = "friends-Invitation";
            $layout = 'email-layout/default_new';
            $variables = array(
                'username' => $userName,
                'friendname' => $friendName,
                'mailtext' => $mailText,
                'acceptlink' => $acceptLink,
                'hostname' => $webUrl
            );
            $subject = ucfirst($userName) . ' Gave Us Your Email.That’s Cool, Right?';

            // #################
            $emailData = array(
                'receiver' => array(
                    $email
                ),
                'variables' => $variables,
                'subject' => $subject,
                'template' => $template,
                'layout' => $layout
            );
            // #################
            $user_function = new UserFunctions();
            //$user_function->sendMails($emailData);
        } else if (count($getFriendExists) == 0) {
            $user = '';
            $inDBFriend = false;
            if (!empty($userDetails) && $userDetails != null) {
                $user = $userDetails->getArrayCopy();
                $friendName = $user['first_name'].' '.$user['last_name'];;
                $inDBFriend = true;
            } else {
                $userEmail = explode("@", $email);
                $friendName = $userEmail[0];
                $inDBFriend = false;
            }
            /**
             * Send to Pubnub For All Friends
             */
            $userNotificationModel = new UserNotification();
            if ($inDBFriend == true) {
                $notificationMsg = ucfirst($userName) . ' would like to be friends with you and share in the common quest to find the best eats in town.';
                $channel = "mymunchado_" . $userDetails['id'];
                $notificationArray = array(
                    "msg" => $notificationMsg,
                    "channel" => $channel,
                    "userId" => $userDetails['id'],
                    "type" => 'invite_friends',
                    "restaurantId" => '',
                    'curDate' => $currentDate,
                    "user_id" => $userId,
                    "friend_request_id" => $userDetails['id'],
                    'username' => ucfirst($userName)
                );
                $notificationJsonArray = array("user_id" => $userId, "friend_request_id" => $userDetails['id'], 'username' => ucfirst($userName));
                $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
                $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
            }

            $insertdata = array(
                'user_id' => $userId,
                'email' => $email,
                'source' => 'munch',
                'created_on' => $currentDate,
                'token' => $this->getUserSession()->token,
                'expired_on' => $expiredOn,
                'status' => '1'
            );
            $insertData = $userFriendsInvitationModel->createUserInvitation($insertdata);
            $frequestId = $insertData;
            $mailText = $data;
            $config = $this->getServiceLocator()->get('Config');
            $webUrl = PROTOCOL . $config['constants']['web_url'];
            $acceptLink = $webUrl . DS . 'friendInvitation' . DS . $insertData;
            //$acceptLink = $this->getBaseUrl() . DS . 'wapi' . DS . 'user' . DS . 'accepted' . DS . $insertData . '?token=' . $this->getUserSession()->token;
            $template = "friends-Invitation";
            $layout = 'email-layout/default_new';
            $variables = array(
                'username' => $userName,
                'friendname' => $friendName,
                'mailtext' => $mailText,
                'acceptlink' => $acceptLink,
                'hostname' => $webUrl
            );
            $subject = ucfirst($userName) . ' Gave Us Your Email.That’s Cool, Right?';

            // #################
            $emailData = array(
                'receiver' => array(
                    $email
                ),
                'variables' => $variables,
                'subject' => $subject,
                'template' => $template,
                'layout' => $layout
            );

            // #################
            $user_function = new UserFunctions();
            //$user_function->sendMails($emailData);
        }
        return $frequestId;
    }

    public function update($id, $data) {
        $userFriendModel = new UserFriends();
        $userModel = new User();
        $pointSourceModel = new PointSourceDetails();
        $userPointsModel = new UserPoint();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();

        if ($isLoggedIn)
            $userId = $session->getUserId();

        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $frindsStatus = isset($config['constants']['user_friends_status']) ? $config['constants']['user_friends_status'] : array();

        $unFrinds = $frindsStatus['unfriend'];
        $pintSourceDetail = isset($config['constants']['point_source_detail']) ? $config['constants']['point_source_detail'] : array();

        $inviteFriend = $pintSourceDetail['inviteFriends'];
        $type = $data['type'];
        if ($type == 'unfriend') {
            /**
             * UnFrind in User Friend
             */
            $userModel = new \User\Model\User();
            $userInvitationModel = new \User\Model\UserFriendsInvitation();
            $getFriendDetails = $userFriendModel->getUserInvitationId($id, $userId);

            if (count($getFriendDetails) > 0) {
                $unfriendInvitation = $userInvitationModel->unfriendInvitation($getFriendDetails[0]['invitation_id']);
            } else {
                $getFriendDetails = $userModel->getUserEmail($userId);
                $friendEmail = isset($getFriendDetails['email']) && $getFriendDetails['email'] != '' ? $getFriendDetails['email'] : '';
                $unfriend = $userInvitationModel->unfriendUserInvitation($id, $friendEmail);
            }
            $userfriends = $userFriendModel->unFriend($id, $userId, $unFrinds);

            if ($userfriends) {
                /**
                 * Get User Points
                 */
                $userData = $userModel->getUserDetail(array(
                    'column' => array(
                        'points'
                    ),
                    'where' => array(
                        'id' => $userId
                    )
                ));
                $points = $userData['points'];
                /**
                 * Get Invite Friends Points
                 */
                $pointSource = $pointSourceModel->getPointSource(array(
                    'column' => array(
                        'points'
                    ),
                    'where' => array(
                        'id' => $inviteFriend
                    )
                ));

                $inviteFrinedPoints = $pointSource[0]['points'];
                $userPoint = (int) $points - (int) $inviteFrinedPoints;
                /**
                 * Update User Points
                 */
                $updatePoint = $userModel->updateUserPoint($userId, $userPoint);
                /**
                 * Update User Ref
                 */
                $refPoint = $userPointsModel->updateUserRef($userId, $inviteFriend, $id, $unFrinds);

                return array(
                    'success' => 'true'
                );
            } else {
                return array(
                    'error' => 'Friend detail not found'
                );
            }
        }
    }

    private function getUserReferralOrderRemainingCount($user_id) {
        $ur = new UserReferrals();
        $placed_count = intval($ur->getUserReferralOrderPlacedCount($user_id)) % 3;
        $remaining = ($placed_count > 2) ? 0 : (3 - $placed_count);
        return strval($remaining);
    }

    /**
     * Returns user earning and cycles considering $30 credit=1 cycle
     * @param int $user_id
     * @return array with keys earning and cycles
     */
    private function getUserReferralEarningAndCycles($user_id) {
        //$user_id = 1;
        $ut = new UserTransactions();
        $earning = floatval($ut->getUserReferralEarning($user_id));
        $ur = new UserReferrals();
        $count = $ur->getTotalReferredUsersWithAmountCredited($user_id);
        return array('earning' => $earning, 'cycles' => (intval($count / 3)));
    }

    private function getUserReferralCode($user_id) {
        $user_model = new User();
        $isMob = $this->isMobile();        
        return $user_model->getUserReferralCode($user_id,$isMob);
    }

    private function hasPlacedOrder($user_id) {
        $uo = new UserOrder();
        $count = $uo->getTotalPlacedOrder($user_id);
        if ($count > 0) {
            return 1;
        }
        return 0;
    }

}
