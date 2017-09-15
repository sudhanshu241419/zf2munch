<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;

class WebUserNameController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function update($id, $data) {
        $session = $this->getUserSession();
        $userModel = new User ();
        $userModel->id = $session->getUserId();
        if (!isset($data ['first_name']) && empty($data ['first_name'])) {
            throw new \Exception("Sorry, We don't talk to strangers", 400);
        } else {
            $userModel->first_name = $data ['first_name'];
        }
        if (isset($data ['last_name']) && !empty($data ['last_name'])) {
            $userModel->last_name = $data ['last_name'];
        }
        $data = array(
            'first_name' => $userModel->first_name,
            'last_name' => isset($userModel->last_name) ? $userModel->last_name : ''
        );
        $userModel->update($data);
        return $data;
    }

}
