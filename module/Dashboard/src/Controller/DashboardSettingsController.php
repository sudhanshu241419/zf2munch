<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\RestaurantAccounts;
use Dashboard\Model\RestaurantNotificationSettings;

class DashboardSettingsController extends AbstractRestfulController {

    public function getList() {
        $restAccountModel = new RestaurantAccounts();
        $restNotificationModel = new RestaurantNotificationSettings();
        $dashboardFunctions = new DashboardFunctions();
        $restId = $dashboardFunctions->getRestaurantId();
        $data = [];
        $accountDetails = $restAccountModel->getRestAccountDetail($restId);
        if (!empty($accountDetails)) {
            $data['restaurant_account_details'] = $accountDetails;
            $data['updated_at'] = date("M d, Y", strtotime($accountDetails['updated_at']));
            $data['secondary_accounts'] = $restAccountModel->checkeScondaryAccounts($restId);
            $data['secondary_accounts_detail'] = $restAccountModel->checkeScondaryAccountDetails($restId);
            $data['restaurant_notifications'] = $restNotificationModel->getRestNotificationDetails($restId);
        } else {
            return ['status' => 'details not found'];
        }
        return $data;
    }

    public function update($id, $data) {
        $restAccountModel = new RestaurantAccounts();
        $restNotificationModel = new RestaurantNotificationSettings();
        $dashboardFunctions = new DashboardFunctions();
        $restId = $dashboardFunctions->getRestaurantId();
        $accountDetails = $restAccountModel->getRestAccountDetail($restId);
        $notification = $restNotificationModel->getRestNotificationDetails($restId);
        $type = $data['type'];
        if ($type == 'password') {
            if (!empty($data['current_password'])) {
                $currentPassword = $data['current_password'];
            } else {
                return ['msg' => "Current password can not be blank"];
            }
            if (!empty($data['new_password'])) {
                $newPassword = $data['new_password'];
            } else {
                return ['msg' => "New password can not be blank"];
            }
            if ($accountDetails['user_password'] != $newPassword) {
                if ($accountDetails['user_password'] == $currentPassword) {
                    $updateData = array("user_password" => $newPassword, "updated_at" => date('Y-m-d H:i:s'));
                    if ($restAccountModel->update($accountDetails['id'], $updateData)) {
                        return ['msg' => "success"];
                    } else {
                        return ['msg' => "failure"];
                    }
                } else {
                    return ['msg' => "You entered an invalid password."];
                }
            } else {
                return ['msg' => "New Password And Current Password Can Not Be Same"];
            }
        }
        if ($type == 'notification') {
            $id = (!empty($notification)) ? $notification['id'] : 0 ;
            $updateData = array(
                'restaurant_id' => $restId,
                'new_order_received' => $data['new_order_received'],
                'order_cancellation' => $data['order_cancellation'],
                'new_reservation_received' => $data['new_reservation_received'],
                'reservation_cancellation' => $data['reservation_cancellation'],
                'new_deal_coupon_purchased' => $data['new_deal_coupon_purchased'],
                'new_review_posted' => $data['new_review_posted'],
                'important_system_updates' => $data['important_system_updates']
            );
            if ($restNotificationModel->update($id, $updateData)) {
                $update = array("updated_at" => date('Y-m-d H:i:s'));
                $restAccountModel->update($accountDetails['id'], $update);
                return ['true' => "1"];
            } else {
                return ['true' => "0"];    
            }
        }
    }

}
