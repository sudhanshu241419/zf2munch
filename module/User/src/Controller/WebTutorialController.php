<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;

class WebTutorialController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function update($id, $data) {
        $id = $this->getUserSession()->getUserId();
        $user = new User();
        if (is_array($data)) {
            $user->id = $id;
            unset($data['token']);
            $user->update(array('tutorial' => serialize($data)));
            return $data;
        } else {
            throw new \Exception('data send needs to be an array');
        }
    }

}
