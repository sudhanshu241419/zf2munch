<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;

class RestaurantNameLoyalityCodeController extends AbstractRestfulController {

    public function getlist() {
        $loyaltyCode = $this->getQueryParams('code', false);
        $restaurant_id = $this->getQueryParams('restaurant_id', false);
        if ($loyaltyCode) {
            $userFunctions = new \User\UserFunctions();
            if ($userFunctions->parseLoyaltyCode($loyaltyCode,$restaurant_id)) {
                $commonFunctions = new \MCommons\CommonFunctions();
                $modifiedRestName = $commonFunctions->modifyRestaurantName($userFunctions->restaurant_name);
                return array(
                    'success' => true,
                    'restaurant_name' => $userFunctions->restaurant_name,
                    'restaurant_id' => $userFunctions->restaurantId,
                    'message' => "You'll join " . $modifiedRestName . " Dine & More rewards program on registration. Well done!"
                );
            }else{
               return array(
                    'success' => false,
                    'restaurant_name' => '',
                    'restaurant_id' => '',
                    'message' => "Sorry, we could not recognize this code. Re-enter and try again or Proceed to register without the code"
                );
            }
        }

        return array(
            'success' => false,
            'restaurant_name' => '',
            'restaurant_id' => '',
            'message' => "Sorry, we could not recognize this code. Re-enter and try again or Proceed to register without the code"
        );
    }

}
