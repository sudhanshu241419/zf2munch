<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;

class WebUserContactDetailController extends AbstractRestfulController {

    public function getList() {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception('User unavailable', 400);
        }

        $userModel = new User ();
        $response = $userModel->getUser(array(
            'columns' => array('id', 'first_name', 'last_name', 'phone'),
            'where' => array('id' => $user_id)
        ));

        return $response;
    }

    public function update($id, $data) {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();        
        $userModel = new User();
        if ($isLoggedIn) {
            $userModel->id = $session->getUserId();
        } else {
            throw new \Exception('User unavailable', 400);
        }
        
        $options = array('columns'=>array('email'),'where'=>array('id'=>$userModel->id));
        $userEmail = $userModel->getAUser($options);
        $userFunctions = new \User\UserFunctions();
      
        if (!isset($data ['first_name']) && empty($data ['first_name'])) {
            throw new \Exception("Sorry, We don't talk to strangers", 400);
        }
        if (!isset($data ['phone']) && empty($data ['phone'])) {
            throw new \Exception("We promise; No prank calls", 400);
        }
        $userdetails = array ("first_name" => $data ['first_name'], "last_name" => $data ['last_name'], "phone" => $data ['phone']);
        unset($data['token']);
        $userModel->update($userdetails);
        $salesData = array('phone' => $data ['phone'], 'email' => $userEmail[0]['email'], 'owner_email' => 'no-reply@munchado.com', 'identifier' => 'phone');
        //$userFunctions->createQueue($salesData, 'Salesmanago');
        return $data;
    }

}
