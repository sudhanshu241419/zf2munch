<?php

namespace Restaurant\Controller;
use MCommons\Controller\AbstractRestfulController;
class WebRestaurantNameLoyalityCodeController extends AbstractRestfulController {
    public function getlist() {
        $loyaltyCode = $this->getQueryParams('code', false);
        if ($loyaltyCode) {
            $userFunctions = new \User\UserFunctions();
            if ($userFunctions->parseLoyaltyCode($loyaltyCode)) {
                $commonFunctions = new \MCommons\CommonFunctions();
                $restaurantName = $commonFunctions->modifyRestaurantName($userFunctions->restaurant_name);
                return array(
                    'success' => true,
                    'restaurant_name' => $restaurantName,
                    'restaurant_id' => $userFunctions->restaurantId,
                    'message' => "You'll join " . $restaurantName . " Loyalty Program on registration. Well done!"
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
