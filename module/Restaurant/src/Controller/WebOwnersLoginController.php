<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\RestaurantAccounts;
class WebOwnersLoginController extends AbstractRestfulController {

    public function create($data) {
        $status = false;
        if (!isset($data['email']) || empty($data['email'])) {
            throw new \Exception('Email id does not exists', 400);
        }
        if (!isset($data['password']) || empty($data['password'])) {
            throw new \Exception('Password does not exists', 400);
        }
        if (!isset($data['restaurant_id']) || empty($data['restaurant_id'])) {
            throw new \Exception('Restaurant id does not exists', 400);
        }
        $restAccountModel = new RestaurantAccounts();
        $total = $restAccountModel->fetchCount($data['restaurant_id'], $data['email'], $data['password']);
        $session = $this->getUserSession();
        $session->setUserDetail('owner_restaurant_id', $data['restaurant_id']);
        if ((int) $total['count']) {
            // authenticated successfully
            $status = true;
            $session->setUserDetail('is_owner_logged_in', true);
        } else {
            // authentication failed
            $session->setUserDetail('is_owner_logged_in', false);
            throw new \Exception('Authentication failed', 400);
        }
        $session->save();

        return array(
            'success' => $status
        );
    }

    public function getList() {
        try {
            $session = $this->getUserSession();
            $session->setUserDetail('owner_restaurant_id', null);
            $session->setUserDetail('is_owner_logged_in', false);
            $session->save();
            return array(
                'success' => true,
                'message' => 'successfully logged out'
            );
        } catch (\Exception $ex) {
                return array(
                'success' => false,
                'message' => $ex->getMessage()
            );
        }
    }

}
