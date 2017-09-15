<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Restaurant\RestaurantDetailsFunctions;
use Restaurant\Model\Calendar;
use Restaurant\Model\Menu;
use MCommons\StaticOptions;
use MCommons\CommonFunctions;
use Restaurant\OverviewFunctions;

class RestaurantDealsController extends AbstractRestfulController {

    private $cityData = array();
    private $restaurantId;
    private $restCode;
    private $userId = '';

    public function get($restaurant_id = 0) {
        $overviewFunction = new OverviewFunctions;
        $overviewFunction->restaurantId = $restaurant_id;
         $overviewFunction->isMobile = $this->isMobile();
       $this->restaurantId = $restaurant_id;
        $menuModel = new Menu ();
        $response = array();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        ########### Get Restaurant Deal ########
        $restaurantFunctions = new RestaurantDetailsFunctions();
        $response ['deals']=array();
        $response['dine-more-register']=false;
        
        if ($this->getUserSession()->user_id) {
            $this->userId = $this->getUserSession()->user_id;
            $deals = array_values($restaurantFunctions->getDealsForRestaurant($this->restaurantId,$this->userId));
            $restaurantDetailModel = new Restaurant();
            $resDetails = $restaurantDetailModel->findRestaurant(array('where' => array('id' => $this->restaurantId)))->toArray();
            $response ['deals'] = $deals; 
            $response['offer_path'] =  USER_REVIEW_IMAGE.strtolower($resDetails['rest_code']).'/offer/'; 
        }
        ##
        

        return $response;
    }


}
