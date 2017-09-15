<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserNotification;
use User\Model\UserOrder;
use User\Model\UserReservation;
use MCommons\StaticOptions;
use User\UserFunctions;
use Auth\Model\Auth;

class WebUserNotificationController extends AbstractRestfulController {

    public function getList() {
        $notificationModel = new UserNotification();
        $UserReservationModel = new UserReservation();
        $UesrOrderModel = new UserOrder();
        $user_function = new UserFunctions();
        $type = $this->getQueryParams('type');
        $token = $this->getQueryParams('token');
        $authModel = new Auth();
        $session = $this->getUserSession();
        //  print_r($session->user_details); die();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $user_function->userCityTimeZone($locationData);
        
        if ($type == 'count') {
            $notification = $notificationModel->countUserNotification($user_id,$currentDate);
            $count = $notification[0]['notifications'];
            return array(
                'count' => $count
            );
        } else {            

            $limit = 1;
            $dd = array();
            $ee = array();
            $user_current_notification = $notificationModel->getCurrentNotification($user_id, $limit, 'one', $currentDate);

            // ######## In Case Of : Threre is notification ##########
            if (!empty($user_current_notification)) {
                $today = time();
                $str_time = "";
                $creation_time = strtotime($user_current_notification['created_on']);
                $creation_date = StaticOptions::getFormattedDateTime($user_current_notification['created_on'], 'Y-m-d H:i:s', 'Y-m-d H:i:s'); // date('Y-m-d',strtotime($user_current_notification['created_date']));

                $diff_msec = $today - $creation_time;
                // $str_time = $creation_time ;
                $str_time = $notificationModel->getDayDifference($creation_date, $currentDate);

                $user_current_notification['msg_time'] = $str_time;
                // $user_current_notification['date_diff'] = $diff_msec;

                if ($user_current_notification['type'] == 1 || $user_current_notification['type'] == 2) {
                    $current_notifications = $UesrOrderModel->getCurrentNotificationOrder($user_id, $currentDate);
                } else
                if ($user_current_notification['type'] == 3) {

                    $current_notifications = $UserReservationModel->getCurrentNotificationReservation($user_id, $currentDate);
                }
                if (!empty($user_current_notification)) {

                    $notification_one[] = $user_current_notification;
                } else {
                    return array();
                    // $notification_one[] = $this->noNotification($currentDate);
                }
            } else {
                return array();
                //$notification_one[] = $this->noNotification($currentDate);
            }

            $limit = 10;
            $user_all_notification = $notificationModel->getCurrentNotification($user_id, $limit, 'all', $currentDate);
            if (!empty($user_all_notification)) {
                $notification_all = $user_all_notification;
                $notifications = array_merge($notification_one, $notification_all);
            } else {
                $notifications = $notification_one;
            }
           
            $restaurant = new \Restaurant\Model\Restaurant();
            foreach($notifications as $key =>$val){
                if($val['restaurant_id']!=0){
                     $options = array(
                        'columns' => array(
                            'restaurant_name'
                        ),
                        'where' => array(
                            'id' => $val['restaurant_id'],
                         )
            
                    );
                $restaurant->getDbTable()->setArrayObjectPrototype('ArrayObject');
                $restName = $restaurant->find($options)->toArray();
                $notifications[$key]['restaurant_name']=$restName[0]['restaurant_name'];
            }
           }
            return $notifications;
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

    public function update($id) {
        $userId = $this->getUserSession()->getUserId();
        $userNotification = new UserNotification();
        if (isset($id)) {
            $options = array(
                'where' => array(
                    'user_id' => $userId,
                    'read_status' => 0,
                    'channel' => 'mymunchado_' . $userId
                )
            );

            $unreadNotification = $userNotification->getNotification($options);
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
