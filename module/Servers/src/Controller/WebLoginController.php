<?php

namespace Servers\Controller;

use MCommons\Controller\AbstractRestfulController;
use Servers\Model\Servers;

class WebLoginController extends AbstractRestfulController {

    public function create($data) {
        $serverLoginModel = new Servers();
        $session = $this->getUserSession();
        $serverLoginModel->email = isset($data ['email']) ? $data ['email'] : false;
        if (isset($data ['password']) && $data ['password'] != null) {
            $serverLoginModel->password = md5($data ['password']);
        }
        if (!$serverLoginModel->email) {
            throw new \Exception("Email can not be empty.", 400);
        }

        if (!$serverLoginModel->password) {
            throw new \Exception("Password can not be empty.", 400);
        }
        $options = array(
            'where' => array(
                'email' => $serverLoginModel->email
            )
        );
        $serverDetail = $serverLoginModel->getServerDetail($options);
        if (!$serverDetail) {
            throw new \Exception("We couldn't find that email in our database. Maybe it ran off with another email, got married and changed its name to .net, .org. or some other crazy thing.");
        }
        if ($serverDetail ['password'] != $serverLoginModel->password) {
            throw new \Exception("That's not your current password, are you sure you're you?", 400);
        }
        if ($serverDetail ['status'] != 1) {
            throw new \Exception("Not allowed to login, contact to administrator.", 400);
        }
        if (!$serverDetail) {
            throw new \Exception("Login failed.", 400);
        }
        $session->setUserId($serverDetail['id']);
        $data = array(
            'server_email' => $serverDetail['email'],
            'server_restaurant_id' => $serverDetail['restaurant_id'],
            'server_code' => $serverDetail['code'],
            'username' => $serverDetail['first_name']. " " .$serverDetail['last_name'],
            'date' => $serverDetail['date']
        );
        $session->setUserDetail('server_user_detail',$data);
        $session->save();
        return array('success' => true,'code'=>$serverDetail['code']);
    }
    public function getList() {
        $session = $this->getUserSession();
        if ($session) {
            $session->setUserId(null);
            $session->save();
            return array('success' => true);
        } else {
            throw new \Exception('Something went wrong. User not logged out.');
        }
    }
}
