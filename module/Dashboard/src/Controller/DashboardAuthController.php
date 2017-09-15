<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Dashboard\Model\User;
use Dashboard\DashboardFunctions;

class DashboardAuthController extends AbstractRestfulController {

    public function create($data) {
               
        if (!StaticOptions::$_dashboardToken) {
            throw new \Exception("Invalid user. User may have expired", 403);
        }
        if (empty($data['dashboard_username']) || empty($data['dashboard_password'])) {
            throw new \Exception("Invalid Parameters", 400);
        }

        $restaudrantAccount = new \Dashboard\Model\RestaurantAccounts();
        $restaurantAccountDetail = $restaudrantAccount->authRestaurantAccount($data);

        if ($restaurantAccountDetail) {
            $city = new \Dashboard\Model\City();
            $cityDetail = $city->cityDetails($restaurantAccountDetail[0]['city_id']);
            //pr($cityDetail,1);
            $accountDetail = array(
                'dashboard_restaurant_id' => $restaurantAccountDetail[0]['restaurant_id'],
                'dashboard_user_name' => $restaurantAccountDetail[0]['user_name'],
                'dashboard_email' => $restaurantAccountDetail[0]['email'],
                'dashboard_id' => $restaurantAccountDetail[0]['id'],
                'created_on' => $restaurantAccountDetail[0]['created_on'],
                'timezone' => $cityDetail[0]['time_zone'],
                'token' => StaticOptions::$_dashboardToken,
                'state_code' => $cityDetail[0]['state_code'],
                'city_id' => $cityDetail[0]['id'],
                'city_name' => $cityDetail[0]['city_name']
            );

            $dashboardFunctions = new \Dashboard\DashboardFunctions();
            $dashboardFunctions->token=$data['token'];
            if ($dashboardFunctions->saveDashboard($accountDetail)) {
                unset($accountDetail['token']);
                return array("message" => true, 'restaurant_id' => $restaurantAccountDetail[0]['restaurant_id']);
            } else {
                throw new \Exception("Invalid token", 400);
            }
        } else {
            throw new \Exception("Invalid email or password", 400);
        }
    }

    public function update($id,$data) {       
        if (!isset($data['useremail']) || empty($data['useremail'])) {
            return ['status' => false, 'message' => 'Invalid email'];
        } else {
            $restAccount = new \Dashboard\Model\RestaurantAccounts();
            $response = $restAccount->checkEmailExist($data['useremail']);
            if ($response) {
                $this->sendForgotPasswordMail($data['useremail'],$response);
                return ['status' => true, 'message' => 'success'];
            } else {
                return ['status' => false, 'message' => "We couldn't find that email in our database. Maybe it ran off with another email, got married and changed its name to .net, .org. or some other crazy thing."];
            }
        }
    }

    public function sendForgotPasswordMail($email,$response) {
        $dashboardFunctions = new DashboardFunctions();
        $newPassword = $this->generatePassword(8);
        $restAccount = new \Dashboard\Model\RestaurantAccounts();        
        $restAccount->update($response['id'],array('user_password'=>$newPassword));        
        $layout = "email-layout/default_new";
        if (!empty($email)) {
            $variables = array(
                'restaurant_name' => iconv('CP1252', 'UTF-8', $response['restaurant_name']),
                'password' => $newPassword,
            );
            $data = array(
                'to' => $email, //'wecare@munchado.com',
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => FORGOT_PASSWORD_MAIL,
                'layout' => $layout,
                'subject' => SUBJECT_FORGOT_PASSWORD_MAIL,
                'variables' => $variables
            );
            $dashboardFunctions->sendMails($data);
        }
    }
    function generatePassword($length = 8) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr(str_shuffle($chars), 0, $length);
        return $password;
    }

}
