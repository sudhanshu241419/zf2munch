<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;

class MaCarrierController extends AbstractRestfulController {

    public function create($data) {
        if (!empty($data)) {
            if (isset($data ['name']) && empty($data ['name'])) {
                throw new \Exception("Name field can't empty", 400);
            }
            if (isset($data ['email']) && empty($data ['email'])) {
                throw new \Exception("Email field can't empty", 400);
            }
            if (isset($data ['to_email']) && empty($data ['to_email'])) {
                throw new \Exception("To email field can't empty", 400);
            }
            if (isset($data ['phone']) && empty($data ['phone'])) {
                throw new \Exception("Phone field can't empty", 400);
            }
            if(isset($data['rest_name']) && $data['rest_name'] == 'alberto'){
                $this->sendMAAlbertoEnquiry($data);
            }else{
                $this->sendMACarrier($data);
            }
            return ['status' => 'success'];
        }
    }

    public function sendMACarrier($data) {
        $restModel = new \Restaurant\Model\Restaurant();
        $restaurant = $restModel->findByRestaurantId(array(
            'column' => array('restaurant_name', 'restaurant_logo_name','facebook_url','instagram_url','twitter_url'),
            'where' => array('id' => $data['restaurant_id'])
        ));
        $userFunctions = new \User\UserFunctions();
        $template = "microsite_carrier";
        $layout = "email-layout/ma_default";
        //$template = CONFIRM_RESERVATION;
        //$subject = sprintf(SUBJECT_CONFIRM_RESERVATION, $reservationDetails['restaurant_name']);
        $subject = "Spice Job Application";
        if (!empty($data)) {
            $variables = array(
                'name' => $data['name'],
                'website' => $data['website'],
                'email' => $data['email'],
                'message' => $data['message'],
                'restaurant_logo' => $restaurant->restaurant_logo_name,
                'restaurant_name' => $restaurant->restaurant_name,
                'rest_code' => strtolower($restaurant->rest_code),
                'template_img_path' => MAIL_IMAGE_PATH,
                'facebook_url' => $restaurant->facebook_url,
                'instagram_url' => $restaurant->instagram_url,  
                'twitter_url' => $restaurant->twitter_url,
            );
            $to_mails = explode(',', $data['to_email']);
            foreach ($to_mails as $value) {
                $data = array(
                'receiver' => array(trim($value)),
                'template' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
                $userFunctions->sendMails($data);
            }
            
        }
    }
    public function sendMAAlbertoEnquiry($data) {
        $restModel = new \Restaurant\Model\Restaurant();
        $restaurant = $restModel->findByRestaurantId(array(
            'column' => array('restaurant_name', 'restaurant_logo_name','facebook_url','instagram_url','twitter_url'),
            'where' => array('id' => $data['restaurant_id'])
        ));
        $userFunctions = new \User\UserFunctions();
        $template = "microsite_alberto_enquiry";
        $layout = "email-layout/ma_alberto_default";
        $subject = $restaurant->restaurant_name." Enquiry Form";
        if (!empty($data)) {
            $variables = array(
                'name' => $data['name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
                'best_time' => $data['best_time'],
                'best_way' => $data['best_way'],
                'describe_event' => $data['describe_event'],
                'food_type' => $data['food_type'],
                'evant_venue' => $data['evant_venue'],
                'restaurant_logo' => $restaurant->restaurant_logo_name,
                'restaurant_name' => $restaurant->restaurant_name,
                'rest_code' => strtolower($restaurant->rest_code),
                'template_img_path' => MAIL_IMAGE_PATH,
                'facebook_url' => $restaurant->facebook_url,
                'instagram_url' => $restaurant->instagram_url,  
                'twitter_url' => $restaurant->twitter_url,
            );
            $to_mails = explode(',', $data['to_email']);
            foreach ($to_mails as $value) {
                $data = array(
                'receiver' => array($value),//$data['to_email'],
                'template' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
                $userFunctions->sendMails($data);
            }
            
        }
    }

}
