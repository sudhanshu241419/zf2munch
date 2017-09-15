<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\UserOrder;
use User\Model\User;
use Restaurant\Model\Restaurant;

class PointPastOrderController extends AbstractRestfulController {

    public function update($id, $data) {
        $userFunction = new UserFunctions();
        $restaurants = new Restaurant();
        $user = new User();
        $userId = $this->getUserSession()->getUserId();
        $result = array();

        if ($userId && $id) {
            $userOrderModel = new UserOrder();
            // check existing order in user order
            $response = $userOrderModel->getUserOrder(array(
                'columns' => array(
                    'id',
                    'user_id',
                    'total_amount',
                    'status',
                    'order_type',
                    'restaurant_id'
                ),
                'where' => array('id' => $id)
            ));

            if ($response) {
                $point = floor($response['total_amount']);

                if ($response['user_id'] == 0 || $response['user_id'] == NULL || empty($response['user_id'])) {
                    //Associate user with order
                    $userOrderModel->id = $id;
                    $udata = array('user_id' => $userId);
                    $userOrderModel->update($udata);
                    if ($response['status'] === 'confirmed') {
                        $options = array('columns' => array('restaurant_name'), 'where' => array('id' => $response['restaurant_id']));
                        $restaurant = $restaurants->findRestaurant($options);
                        $points = array('id' => '', 'points' => $point);
                        $message = "You earned " . $point . " points with your " . $response['order_type'] . " order from " . $restaurant->restaurant_name . "!";
                        $userFunction->givePoints($points, $userId, $message);
                    }
                    $options = array('columns' => array('points'), 'where' => array('id' => $userId));
                    $userPoints = $user->getUserDetail($options);
                    $result = array("points" => $userPoints['points'], 'orderpoints' => "$point");
                } else {
                    $options = array('columns' => array('points'), 'where' => array('id' => $userId));
                    $userPoints = $user->getUserDetail($options);
                    $result = array("points" => $userPoints['points'], 'orderpoints' => "$point");
                }
            } else {
                $result = array("points" => '0', 'orderpoints' => '0');
            }
        } else {
            $result = array("points" => '0', 'orderpoints' => '0');
        }
        return $result;
    }

}
