<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;

class CheckTwitterAccountController extends AbstractRestfulController {

    public function create($data) {

        $uid = (isset($data['uid']) && !empty($data['uid'])) ? $data['uid'] : false;
        $userDetail = array("is_exist" => false, "email" => "");
        if ($uid) {
            $userModel = new User ();
            $joins = array();
            $joins [] = array(
                'name' => array(
                    'ua' => 'user_account'
                ),
                'on' => 'users.id = ua.user_id',
                'columns' => array(
                    'user_source',
                    'access_token',
                    'session_token'
                ),
                'type' => 'inner'
            );

            $options = array(
                'columns' => array(
                    'email'
                ),
                'where' => array('ua.session_token' => $uid),
                'joins' => $joins,
            );
            $response = $userModel->getUserDetail($options);

            if ($response) {
                $userDetail['email'] = $response['email'];
                $userDetail['is_exist'] = true;
                return $userDetail;
            } else {
                $userDetail['email'] = "";
                return $userDetail;
            }
        }
        return $userDetail;
    }

}
