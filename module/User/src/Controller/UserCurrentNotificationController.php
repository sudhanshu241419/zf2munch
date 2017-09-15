<?php
namespace User\Controller;
use MCommons\Controller\AbstractRestfulController;
use User\Model\UserNotification;
use User\Model\UserOrder;
use User\Model\UserReservation;
use MCommons\StaticOptions;
use User\UserFunctions;
class UserCurrentNotificationController extends AbstractRestfulController {

        public function getList() {
        $notificationModel = new UserNotification();
        $UserReservationModel = new UserReservation();
        $UesrOrderModel = new UserOrder();
        $user_function = new UserFunctions();
        $type = $this->getQueryParams('type');
        $token = $this->getQueryParams('token');
        $session = $this->getUserSession();
        //  print_r($session->user_details); die();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $notificationModel = new UserNotification();
        $UserReservationModel = new UserReservation();
        $UesrOrderModel = new UserOrder();
        $type = $this->getQueryParams('type');
        $locationData = $session->getUserDetail('selected_location');
        $user_function = new UserFunctions();
        $currentDate = $user_function->userCityTimeZone($locationData);
            
        if ($type == 'count') {
            $notification = $notificationModel->countUserNotification($user_id,$currentDate);
            $count = $notification[0]['notifications'];
            return array('count' => $count);
        } else {
            try{
            $limit = 1;
            $dd = array();
            $ee = array();
            $notification_one=array();
            $user_current_notification = $notificationModel->getCurrentNotification($user_id, $limit, 'one', $currentDate);
            
            // ######## In Case Of : Threre is notification ##########
            if (!empty($user_current_notification)) {
                $today = time();
                $str_time = "";
                $creation_time = strtotime($user_current_notification['created_on']);
                $creation_date = StaticOptions::getFormattedDateTime($user_current_notification['created_on'], 'Y-m-d H:i:s', 'Y-m-d H:i:s'); // date('Y-m-d',strtotime($user_current_notification['created_date']));
                //$diff_msec = $today - $creation_time;
                // $str_time = $creation_time ;
                $str_time = $notificationModel->getDayDifference($creation_date, $currentDate);

                $user_current_notification['msg_time'] = $str_time;
                // $user_current_notification['date_diff'] = $diff_msec;

                if ($user_current_notification['type'] == 1 || $user_current_notification['type'] == 2) {
                    $current_notifications = $UesrOrderModel->getCurrentNotificationOrder($user_id, $currentDate);
                } else{
                if ($user_current_notification['type'] == 3) {

                    $current_notifications = $UserReservationModel->getCurrentNotificationReservation($user_id, $currentDate);
                }
                }
                
                if (!empty($user_current_notification)) {
                    $notification_one[] = $user_current_notification;
                } 
            } 
            $limit = 10;
            $user_all_notification = $notificationModel->getCurrentNotification($user_id, $limit, 'all', $currentDate);
            if (!empty($user_all_notification)) {
                $notification_all = $user_all_notification;
                $notifications = array_merge($notification_one, $notification_all);
            } else {
                $notifications = $notification_one;
            }
            return $notifications;
            }catch (\Exception $e) {
                \MUtility\MunchLogger::writeLog($e, 1,'Something went Wrong In Notification Api');
                throw new \Exception($e->getMessage(),400);
             }
        }
    }

    public function noNotification($todayDate) {
        $user_current_notification = array();
        $diff = 0;
        $user_current_notification['id'] = '';
        $user_current_notification['type'] = 0;
        $user_current_notification['classes'] = 'i_nonotification';
        $user_current_notification['notification_msg'] = "Welcome to Munch Ado! From now on, we’ll be helping you get from hungry to satisfied and living in your pocket. No skinny jeans please.";
        $user_current_notification['created_on'] = $todayDate;
        $user_current_notification['msg_time'] = '';
        $user_current_notification['restaurant_id'] = '';

        return $user_current_notification;
    }

    public function update($id,$data) {
        $userId = $this->getUserSession()->getUserId();
        $userNotification = new UserNotification();
        $ids = $data['ids']; //array('98','876','94');
        if (isset($id)) {
            $options = array(
                'where' => array(
                    'user_id' => $userId,
                    'read_status' => 0,
                    'channel' => 'mymunchado_' . $userId
                )
            );

            $unreadNotification = $userNotification->getNotification($options);
            $notificationMsg = '';
            $channel = "mymunchado_" . $userId;
            $notificationArray = array(
                "msg" => $notificationMsg,
                "channel" => $channel,
                "userId" => $userId,
                "type" => 'other',
                "restaurantId" => 0,
                "sendcountzero" => 0,
                'curDate' => date('y-m-d h:i:s')
            );
            $notificationJsonArray = array();
            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);

            if (!empty($unreadNotification)) {
                $userNotification->user_id = $userId;
                $data = array(
                    'read_status' => 1
                );
                $userNotification->update($data);
                return array(
                    'success' => true,
                    'count' => 0
                );
            } else {
                throw new \Exception("Status is already updated.");
            }
        }
    }

}
