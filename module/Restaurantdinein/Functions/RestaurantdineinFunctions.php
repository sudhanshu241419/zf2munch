<?php

namespace Restaurantdinein;

class RestaurantdineinFunctions {

    public function dashboardCmsNotification($userId, $data, $reservationid, $currentDate) {
        ##Dashboard Notification
        $userNotificationModel = new \User\Model\UserNotification ();
        if(isset($data['last_name'])){
            $lastname = $data['last_name'];
        }else{
            $lastname="";
        }
        
        $userFullName = $data['first_name'] . ' ' . $lastname;
        $channel = "dashboard_" . $data ['restaurant_id'];
        $notificationArray = array(
            "msg" => "You have a new reservation! Make some space.",
            "channel" => $channel,
            "userId" => $userId,
            "type" => 'snag-a-spot',
            "restaurantId" => $data ['restaurant_id'],
            'curDate' => $currentDate,
            'is_friend' => 0,
            'username' => ucfirst($data['first_name']),
            'reservation_id' => $reservationid,
            'user_id' => $userId,
            'reservation_status' => 0,
            'first_name' => ucfirst($data['first_name'])
        );
        $notificationJsonArray = array('first_name' => ucfirst($data['first_name']), 'is_friend' => 0, 'username' => ucfirst($userFullName), 'reservation_id' => $reservationid, 'user_id' => $userId, 'reservation_status' => 0);
        $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
        \MCommons\StaticOptions::pubnubPushNotification($notificationArray);

        #cms notification
        $notificationMsg = "You have received a new reservation request.";
        $channel = "cmsdashboard";
        $notificationArray = array(
            "msg" => $notificationMsg,
            "channel" => $channel,
            "userId" => $userId,
            "type" => 'snag-a-spot',
            "restaurantId" => $data ['restaurant_id'],
            'curDate' => $currentDate,
            'is_friend' => 0,
            'username' => ucfirst($userFullName),
            'reservation_id' => $reservationid,
            'user_id' => $userId,
            'reservation_status' => 0,
            'first_name' => ucfirst($data['first_name'])
        );
        $notificationJsonArray = array('first_name' => ucfirst($data['first_name']), 'is_friend' => 0, 'username' => ucfirst($data['first_name']), 'reservation_id' => $reservationid, 'user_id' => $userId, 'reservation_status' => 0);
        $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
        \MCommons\StaticOptions::pubnubPushNotification($notificationArray);
    }

    public function bookmarkRestaurant($userId, $data, $currentDate) {
      
        $restaurantBookmark = new \Bookmark\Model\RestaurantBookmark();
        $bookmarkData = array(
            'restaurant_id' => $data ['restaurant_id'],
            'restaurant_name' => $data ['restaurant_name'],
            'user_id' => $userId,
            'created_on' => $currentDate,
            'type' => 'bt'
        );
        $restaurantBookmark->insertBookmark($bookmarkData);
        
    }
    
    public function getFriendList($userId){
        $friendModel = new \User\Model\UserFriends();
        return $friendModel->getFriendListForCurrentUser($userId);
    }
    
    public function holdTableDateTime($holdTime,$currentDateTime){
        if($holdTime>0){
            $holdTime = 60*$holdTime;
        return date('Y-m-d h:i:s', strtotime($currentDateTime) + $holdTime);
        }else{
            return $currentDateTime;                    
        }
    }
    
    public function getUserPastActivities($userId, $restId, $email) {
        $restaurantDinein = new Model\Restaurantdinein();        
        $totalReservations = $restaurantDinein->getTotalUserReservations($restId, $userId, $email);
        $orderModel = new \Dashboard\Model\DashboardOrder();
        $reviewModel = new \Dashboard\Model\UserReview();
        $checkinModel = new \User\Model\UserCheckin();
        $orders = $orderModel->getTotalUserOrder($userId, $email,$restId);
        $totalOrders = ($orders) ? $orders[0]['total_order'] : 0;
        $checkins = $checkinModel->getTotalUsercheckin($userId, $restId);
        $totalCheckins = ($checkins) ? $checkins[0]['total_checkin'] : 0;
        $totalevReviews = $reviewModel->getTotalUserReviews($restId, $userId);
        $data['totalorder'] = $totalOrders;
        $data['totalreservation'] = $totalReservations;
        $data['totalcheckin'] = $totalCheckins;
        $data['totalreview'] = $totalevReviews;
        return $data;
    }

}
