<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\Avatar;
use User\Model\UserAvatar;

class MyMuncherController extends AbstractRestfulController {

    // Review Type array for temporary basis we need to move it to some common place
    public function get($id) {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        //$userId = $this->getUserSession()->user_id;
        $friendId = $this->getQueryParams('friendid',false);
        if ($isLoggedIn) {
            if ($friendId) {
                $userId = $friendId;
            } else {
                $userId = $session->getUserId();
            }
        } else {
            throw new \Exception('Not a valid user', 404);
        }
        
//        if (!$userId) {
//            throw new \Exception('Not a valid user');
//        }

        $userFunctions = new \User\UserFunctions();
        $myMuncher = array();

        $allAvatar = $userFunctions->getAllAvatar($id);
        if ($allAvatar) {
            $myMuncher = $userFunctions->getMyAvatar($allAvatar, $userId);
        }
        return $myMuncher;
    }

    public function getList() {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $friendId = $this->getQueryParams('friendid',false);
        if ($isLoggedIn) {
            if ($friendId) {
                $userId = $friendId;
            } else {
                $userId = $session->getUserId();
            }
        } else {
            throw new \Exception('Not a valid user', 404);
        }
        $userFunctions = new \User\UserFunctions();
        $myMuncher = array();

        $allAvatar = $userFunctions->getAllAvatar();
        if ($allAvatar) {
            $myMuncher = $userFunctions->getMyAvatar($allAvatar, $userId);
        }
        return $myMuncher;
    }

}
