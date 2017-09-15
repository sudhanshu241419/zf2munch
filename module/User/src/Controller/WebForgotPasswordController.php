<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;

class WebForgotPasswordController extends AbstractRestfulController {

    public function update($id, $data) {
        $userFunctions = new UserFunctions ();
        $data['user_source'] = "ws";
        $userFunctions->changePassword($data);
        return array(
            'success' => 'true'
        );
    }

}
