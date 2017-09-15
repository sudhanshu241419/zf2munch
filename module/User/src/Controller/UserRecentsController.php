<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class UserRecentsController extends AbstractRestfulController {

    public function getList() {
        $response = array();
        $session = $this->getUserSession();
        if (!$session->isLoggedIn()) {
            throw new \Exception('No Active Login Found');
        }
        $user_id = $session->getUserId();
        //$user_id = 359;      
        $triedPlace = $this->getUserTriedPlaces($user_id);
        $recentPlace = $this->getUserRecentPlaces($user_id);
        $response['tried_places'] = (!empty($triedPlace))?$triedPlace:null;
        $response['recent_places'] = (!empty($recentPlace))?$recentPlace:null;
        
        return $response;
    }
    
    private function getUserTriedPlaces($user_id){
        $userOrder = new \User\Model\UserOrder();
        $userOrder->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'r' => 'restaurants'
            ),
            'on' => 'r.id = user_orders.restaurant_id',
            'columns' => array(
                'restaurant_name'
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'order_type',
                'order_createtime' => 'created_at'
            ),
            'where' => array(
                'user_orders.user_id' => $user_id,
                'user_orders.is_reviewed'=>0
            ),
            'joins' => $joins
        );
        return $userOrder->find($options)->toArray();
    }
    
    private function getReservationRestaurant($user_id){
        $userReservation = new \User\Model\UserReservation();
        $userReservation->getDbTable()->setArrayObjectPrototype('ArrayObject');
       
        $options = array(
            'columns' => array(
                'restaurant_name',
                'order_createtime' => 'reserved_on'
            ),
            'where' => array(
                'user_id' => $user_id
            ),
           
        );
        return $userReservation->find($options)->toArray();
    }
    
    private function getUserRecentPlaces($user_id){
        $userCheckin = new \User\Model\UserCheckin();
        $userCheckin->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'r' => 'restaurants'
            ),
            'on' => 'r.id = user_checkin.restaurant_id',
            'columns' => array(
                'restaurant_name' => 'restaurant_name',
                'restaurant_address' => 'address',
                'restaurant_image' => 'restaurant_image_name ',
                'res_delivery' => 'delivery',
                'res_takeout' => 'takeout',
                'res_dinein' => 'dining',
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'message' => 'message'
            ),
            'where' => array(
                'user_checkin.user_id' => $user_id
            ),
            'joins' => $joins
        );
        $rawData = $userCheckin->find($options)->toArray();
        
        $response = array();
        foreach($rawData as $record){
            $response[] =  array(
                'restaurant_name' => $record['restaurant_name'],
                'restaurant_address' => $record['restaurant_address'],
                'restaurant_image' => (string)$record['restaurant_image'],
                'res_delivery' => (int)$record['res_delivery'],
                'res_takeout' => (int)$record['res_takeout'],
                'res_dinein' => (int)$record['res_dinein'],
            );
        }
        return $response;
    }
}
