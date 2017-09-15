<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\User;

class FriendshipDeclineController extends AbstractRestfulController {

    public function get($id) {
        $userFunctions = new UserFunctions();
        $status = $userFunctions->invitationDecline($id);        
        return array('result'=>$status);
    }

}
