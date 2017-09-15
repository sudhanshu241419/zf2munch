<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\DashboardOrder;

class DashboardPubnubNotificationController extends AbstractRestfulController {
    public function getList() {
        $dashboardFunctions = new DashboardFunctions();
        $notification = new \Dashboard\Model\UserNotification();
        $restaurantId = $dashboardFunctions->getRestaurantId();
        $channel = "dashboard_".$restaurantId;
        $options = array('restaurant_id'=>$restaurantId,'channel'=>$channel);
        $notificationData = $notification->restaurantNotification($options);
        return $notificationData;
        
    }
    
    public function update($id, $data) {
        $dashboardFunctions = new DashboardFunctions();
        $notification = new \Dashboard\Model\UserNotification();
        $restaurantId = $dashboardFunctions->getRestaurantId();        
        $notification->notificationStatusChange($restaurantId);
        return array("message"=>"success");
    }
}