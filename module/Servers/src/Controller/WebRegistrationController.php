<?php

namespace Servers\Controller;

use MCommons\Controller\AbstractRestfulController;
use Servers\Model\Servers;
use User\UserFunctions;
use Restaurant\Model\Restaurant;
use Restaurant\Model\Tags;

class WebRegistrationController extends AbstractRestfulController {

    public function create($data) {
        $serverModel = new Servers();
        $userFunctions = new UserFunctions();
        $session = $this->getUserSession();
        $serverModel->first_name = isset($data ['first_name']) ? $data ['first_name'] : false;
        $serverModel->last_name = (isset($data ['last_name'])) ? $data ['last_name'] : '';
        $serverModel->restaurant_id = isset($data ['restaurant_id']) ? $data ['restaurant_id'] : false;
        $serverModel->email = isset($data ['email']) ? $data ['email'] : false;
        $serverModel->phone = isset($data ['phone']) ? $data ['phone'] : false;
        if (isset($data ['password']) && $data ['password'] != null) {
            $serverModel->password = md5($data ['password']);
        }
        $loyaltyCode = (isset($data['loyality_code']) && !empty($data['loyality_code'])) ? $data['loyality_code'] : "";
        $serverModel->code = ucfirst($loyaltyCode);
        $serverModel->status = 1;
        if (!$serverModel->first_name) {
            throw new \Exception("First name can not be empty.", 400);
        }

        if (!$serverModel->restaurant_id) {
            throw new \Exception("Restaurant Name can not be empty.", 400);
        }
        if (!$serverModel->email) {
            throw new \Exception("Email can not be empty.", 400);
        }

        if (!$serverModel->password) {
            throw new \Exception("Password can not be empty.", 400);
        }
        ############## Loyality Program Registration code validation #############
        if ($loyaltyCode) {
            if (!$userFunctions->parseLoyaltyCode($loyaltyCode,$serverModel->restaurant_id)) {
                throw new \Exception("Sorry we could not detect a valid code. Re-enter and try again.", 400);
                //return false;
            }
        }
        //if (!isset($data ['accept_toc']) || $data ['accept_toc'] != 1) {
            //throw new \Exception("Required to accept term & condition.", 400);
        //}
        $options = array(
            'where' => array(
                'email' => $serverModel->email
            )
        );
        $options1 = array(
            'where' => array(
                'code' => $serverModel->code
            )
        );
        $serverDetail = $serverModel->getServerDetail($options);
        if (!empty($serverDetail)) {
            throw new \Exception("Email is already registered.", 400);
        }
        $serverCode = $serverModel->getServerDetail($options1);
        if (!empty($serverCode)) {
            throw new \Exception("User is already registered with this code.", 400);
        }
        $responseRegistration = $serverModel->serverRegistration();
        if (!$responseRegistration) {
            throw new \Exception("Registration failed.", 400);
        }
        $session->setUserId($serverModel->id);
        $data = array(
            'server_email' => $serverModel->email,
            'server_restaurant_id' => $serverModel->restaurant_id,
            'server_code' => $serverModel->code
        );
        $session->setUserDetail('server_user_detail',$data);
        $session->save();
        $config = $this->getServiceLocator()->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $template = 'server-registration';
            $layout = 'email-layout/default_server_register';
            $variables = array('username' => $serverModel->first_name, 'hostname' => $webUrl);
            $mailData = array('recievers' => $serverModel->email, 'layout' => $layout, 'template' => $template, 'variables' => $variables);
            $userFunctions->sendServerRegistrationEmail($mailData);
        return array('success' => true,'code'=>$serverModel->code);
    }
    
    public function getList() {
        $restaurants = new Restaurant();
        $tags = new Tags();
        $tagDetails = $tags->getTagDetailByName('dine-more');
        if(!empty($tagDetails)){
            $tagId = $tagDetails[0]['tags_id'];
        }
        $restaurantData = $restaurants->getDineAndMoreTaggedRestaurants($tagId);
        return $restaurantData;
    }

}
