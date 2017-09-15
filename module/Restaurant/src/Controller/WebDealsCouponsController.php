<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\RestaurantDetailsFunctions;
use Restaurant\Model\Menu;

class WebDealsCouponsController extends AbstractRestfulController {

    public function get($id) {
        $response = array();
        $userId = $this->getUserSession()->getUserId();
        $restaurantAccount = new \Restaurant\Model\RestaurantAccounts();
        $isRegisterRestaurant = $restaurantAccount->getRestaurantAccountDetail(array(
                'columns' => array(
                    'restaurant_id'
                ),
                'where' => array(
                    'restaurant_id' => $id,
                    'status'=>1
                )
            ));
        
        if((isset($isRegisterRestaurant['restaurant_id']) && !empty($isRegisterRestaurant['restaurant_id']))){ 
            $restaurantFunctions = new RestaurantDetailsFunctions();
            $response=  array_values($restaurantFunctions->getDealsForRestaurant($id,$userId));
            return $response;
        }else{
            return $response;
        }
    }

}
