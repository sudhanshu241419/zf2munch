<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\UserReservation;
use User\Model\User;
use Restaurant\Model\Restaurant;
use MCommons\StaticOptions;

class WebPointPastReservationController extends AbstractRestfulController {

    public function update($id, $data) {
        $userFunction = new UserFunctions();
        $restaurants = new Restaurant();
        $user = new User();
        $userId = $this->getUserSession()->getUserId();
        $result = array();
        $confirm = StaticOptions::$confirmed;
        if ($userId && $id) {
            $userReservationModel = new UserReservation();
            // check existing order in user order
            $response = $userReservationModel->getUserReservation(array(
                'columns' => array(
                    'id',
                    'user_id',
                    'status',
                    'restaurant_id',
                    'order_id'
                ),
                'where' => array('id' => $id)
            ));

            if ($response) {
                $point = $userFunction->getAllocatedPoints('reserveTable')->getArrayCopy();

                if ($response[0]['user_id'] == 0 || $response[0]['user_id'] == NULL || empty($response[0]['user_id'])) {
                    //Associate user with Reservation
                    $userReservationModel->id = $id;
                    $udata = array('user_id' => $userId);
                    $userReservationModel->update($udata);
                    
                    if(isset($response[0]['order_id']) && !empty($response[0]['order_id']) && $response[0]['order_id']>0){
                        $userOrderModel = new \User\Model\UserOrder();
                        $userOrderModel->id = $response[0]['order_id'];
                        $odata = array('user_id'=>$userId);                        
                        $userOrderModel->update($odata);	
                    } 

                    if ($response[0]['status'] == $confirm) {
                        $points = array('id' => '3', 'points' => $point['points']);
                        $message = "You have upcoming plans! This calls for a celebration, here are " . $point['points'] . " points!";
                        $userFunction->givePoints($points, $userId, $message, $id);
                    }
                    $options = array('columns' => array('points'), 'where' => array('id' => $userId));
                    $userPointsModelNew = new \User\Model\UserPoint();
                    $totalPoints = $userPointsModelNew->countUserPoints($userId);
                    $userPoints = $user->getUserDetail($options);
                    $result = array("points" => $totalPoints[0]['points'], 'reservationpoints' => $point['points']);
                } else {
                    $options = array('columns' => array('points'), 'where' => array('id' => $userId));
                    $userPoints = $user->getUserDetail($options);
                    $userPointsModelNew = new \User\Model\UserPoint();
                    $totalPoints = $userPointsModelNew->countUserPoints($userId);
                    $result = array("points" => $totalPoints[0]['points'], 'reservationpoints' => $point['points']);
                }
            } else {
                $result = array("points" => '0', 'reservationpoints' => '0');
            }
        } else {
            $result = array("points" => '0', 'reservationpoints' => '0');
        }
        return $result;
    }

}
