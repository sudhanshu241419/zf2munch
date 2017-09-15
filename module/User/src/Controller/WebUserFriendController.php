<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\UserFriends;
use User\Model\User;
use MCommons\StaticOptions;
use User\Model\UserFriendsInvitation;
use User\Model\UserOrder;
use User\Model\PointSourceDetails;
use User\Model\UserPoints;
use User\Model\UserPoint;
use User\Model\UserNotification;
use User\Model\UserReferrals;
use User\Model\UserTransactions;

class WebUserFriendController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $userFriendModel = new UserFriends();
        $userFunctions = new UserFunctions();
        $pointSourceModel = new PointSourceDetails();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $data1 = "";
        $userPoints = new \User\Model\UserPoint();
        //$data1 = $userPoints->countUserPoints($userId);
        $totalPoints = $userPoints->countUserPoints($userId);
        $redeemPoint = $totalPoints[0]['redeemed_points'];
        $data1 = strval($totalPoints[0]['points'] - $redeemPoint);
        $pintSourceDetail = isset($config['constants']['point_source_detail']) ? $config['constants']['point_source_detail'] : array();
        $inviteFriend = $pintSourceDetail['inviteFriends'];
        $orderby = $this->getQueryParams('orderby', 'date');
        $page = $this->getQueryParams('page', 1);
        $limit = $this->getQueryParams('limit', 50);
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * (SHOW_PER_PAGE);
        }

        $type = $this->getQueryParams('type');
        $response = array();
        $ur_model = new \User\Model\UserReferrals();
        $friends = $userFriendModel->getUserFriendList($userId, $orderby);
        switch ($type) {
            case 'friend_list'://Get Usre Friends List
                $currentUserOrderPlaced = $this->hasPlacedOrder($userId);
                
                $referredUsersList = $ur_model->getReferredUsersArr($userId);
                //pr($referredUsersList);
                if (!empty($friends) && $friends != null) {
                    foreach ($friends as $key => $value) {                       
                        $value['id'] = trim($value['friend_id']);
                        $value['created_at'] = StaticOptions::getFormattedDateTime($value['created_at'], 'Y-m-d H:i:s', 'M d, Y');
                        $value['display_pic_url'] = $userFunctions->findImageUrlNormal($value['display_pic_url'], $value['id']);
                        $value['order_placed'] =  $this->hasPlacedOrder($value['friend_id']);
                        $value['is_referred'] = intval(in_array(intval($value['friend_id']), $referredUsersList));
                        $data[] = $value;
                    }
                }
                $response = isset($data) ? $data : array();
                break;
            case 'count':
                $pointSource = $pointSourceModel->getPointSource(array(
                    'column' => array(
                        'points'
                    ),
                    'where' => array(
                        'id' => $inviteFriend
                    )
                ));
                $inviteFrinedPoints = $pointSource[0]['points'];
                $friendsCount = count($friends); //$userFriendModel->getTotalFriends($userId);

                $total_friends = 0;
                $invite_friend =  $inviteFrinedPoints;
                $total_points = isset($data1) ? $data1 : 0;
                $referral_left = 0;
                $referral_earning = 0;
                $referral_cycles = 1;
                $referral_code = $this->getUserReferralCode($userId);
                $order_placed = $this->hasPlacedOrder($userId);
                $total_order_place = 0;
            //pr($friends,1);
                $referredUsersList = $ur_model->getReferredUsersArr($userId);
                if (!empty($friendsCount) && $friendsCount != 0) {
                    foreach ($friends as $key => $value) {
                        if ($this->hasPlacedOrder($value['friend_id']) && in_array(intval($value['friend_id']), $referredUsersList)) {
                            $total_order_place = $total_order_place + 1;
                        }
                    }
                    //$totalFreinds = $friendsCount->getArrayCopy();                   
                    $earningAndCycles = $this->getUserReferralEarningAndCycles($userId);
                    $total_friends = $friendsCount; 
                }
                $response = array(
                    'total_friends' => $total_friends, //$totalFreinds['total_friends'],
                    'invite_friend' => $invite_friend,
                    'total_points' => $total_points,  
                    'referral_code' => $referral_code,
                    'order_placed' => $order_placed,
                    'total_order_place'=>$total_order_place,
                );
                break;
            case "pending_invitation":
                $user = new User();
                $userEmail=$user->getUserDetail(array('columns' => array('email'),'where' => array('id' => $userId)));
                $friendInvitation = new \User\Model\UserFriendsInvitation();                
                $invitationList = $friendInvitation->getUserInvitationList($userEmail['email'], 'created_on');
                //pr($invitationList,1);
                if (!empty($invitationList)) {
                    foreach ($invitationList as $key => $value) {
                        $value1['invitation_id'] =$value['id'];                        
                        $value1['display_pic_url'] = $userFunctions->findImageUrlNormal($value['display_pic_url'], $value['user_id']);
                        $value1['inviter_id'] = $value['user_id'];
                        $value1['first_name'] = $value['first_name'];
                        $value1['last_name'] = $value['last_name'];
                        $value1['email'] = $value['email'];
                        $data[] = $value1;
                    }
                }
                $response = isset($data) ? $data : array();
               
            break;
            default:
                throw new \Exception("User Friends Not Found", 404);
                break;
        }
        return $response;
    }

    /**
     * get user detail with statistics
     */
    public function get($userId) {
        $friendDetail = array();
        $userModel = new User();
        $userFunctionModel = new UserFunctions();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId1 = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctionModel->userCityTimeZone($locationData);
        $friendDetail = $userModel->getUserDetailWithStatistics($userId, $userId1);
        if ($friendDetail) {
            if ($friendDetail['last_login']) {
                $friendDetail['last_login'] = $userFunctionModel->formatLastLoginDate($currentDate, $friendDetail['last_login'], 'ago');
            }
            $friendDetail = array_map(function ($i) {
                return $i === null ? '' : $i;
            }, $friendDetail);
        }

        $friendDetail['display_pic_url'] = $userFunctionModel->findImageUrlNormal($friendDetail['display_pic_url'], $userId);
        $friendDetail = array_map(function ($i) {
            return $i === null ? '' : $i;
        }, $friendDetail);
        return $friendDetail;
    }

    /**
     * Send Friends Invitations
     *
     * @see \Zend\Mvc\Controller\AbstractRestfulController::create()
     */
    public function create($data) {
        
        if (empty($data['friendsEmailAddress']))
            throw new \Exception('Invalid Paramters ', 404);
        
        if(isset($data['reqtype']) && $data['reqtype'] === "send_ref_mail"){
            $data['data'] = $data['friendsEmailAddress'];
            $sl = \MCommons\StaticOptions::getServiceLocator();     
            $userReferralController = $sl->get("User\Controller\WebUserReferralController");
            return $userReferralController->create($data);
        }
        $userModel = new User();
        $userFunctions = new UserFunctions();
        $mailText = "";
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();        
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        if ($isLoggedIn)
            $userId = $session->getUserId();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'ua' => 'user_account'
            ),
            'on' => 'users.id = ua.user_id',
            'columns' => array(
                'first_name',
            ),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'first_name','email'
            ),
            'where' => array('users.id' => $userId),
            'joins' => $joins,
        );
        $userDetails = $userModel->getUserDetail($options);
        if (!empty($userDetails) && $userDetails != null) {
            $user = $userDetails->getArrayCopy();
            $userName = $user['first_name'];
            $userEmail = $user['email'];
        } else {
            $userEmail = explode("@", $userEmail);
            $userName = $userEmail[0];
        }
        
        $emailids = explode(',', $data['friendsEmailAddress']);
        // $emailids[] = $data['friendsEmailAddress'];
        
        $invitation=array();
        $baseUrl = $this->getBaseUrl();
        $data['friendsMessage'] = "";
        foreach ($emailids as $email) { 
            if (!empty($email) && ($userEmail != $email)) { 
            $invitation = $userFunctions->inviteFriends($userId, $userName, $currentDate, $email, $data['friendsMessage'], $baseUrl);
            }
        }
        return $invitation;
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
                'curDate' => $currentDate, 'username' => ucfirst($userName)
            );
            $notificationJsonArray = array('username' => ucfirst($userName), "user_id" => $userDetails['id']);
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
        return array(
            'success' => true
        );
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
        $type = isset($data['type'])?$data['type']:false;
        if ($type == 'unfriend') {
            /**
             * UnFrind in User Friend
             */
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
        }else{
            $invitationModel = new UserFriendsInvitation ();
            $invitationModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
            $data=array('invitation_status'=>'1');
            $predicate=array('id' => $id);
            $invitationDetails = $invitationModel->abstractUpdate($data, $predicate);
            return array(
                   'success' => 'true'
            );
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
        $ut = new UserTransactions();
        $earning = floatval($ut->getUserReferralEarning($user_id));
        $ur = new UserReferrals();
        $count = $ur->getTotalReferredUsersWithAmountCredited($user_id);
        return array('earning' => $earning, 'cycles' => (intval($count / 3)));
    }

    private function getUserReferralCode($user_id) {
        $user_model = new User();
        return $user_model->getUserReferralCode($user_id);
    }

    private function hasPlacedOrder($user_id) {
        $uo = new UserOrder();
        $count = $uo->getTotalPlacedOrder($user_id);
        if ($count > 0) {
            return (int)1;
        }
        return (int)0;
    }

    public function getBaseUrl() {
        $uri = $this->getRequest()->getUri();
        return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
    }

}
