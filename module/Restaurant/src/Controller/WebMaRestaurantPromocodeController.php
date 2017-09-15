<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;

class WebMaRestaurantPromocodeController extends AbstractRestfulController {
	public function getList() {
        $restaurantId = $this->getQueryParams ( 'restaurantid', false );
        $restaurant = new \Restaurant\Model\Restaurant();       
        if($restaurant->isRestaurantExists($restaurantId)>0){        
        $userFunctions = new \User\UserFunctions();
            return $userFunctions->getMaPromocodelist($restaurantId);	
        }else{
            return array("have_promo"=>false,"message"=>"Restaurant not exist","promocode"=>array());
        }
	}
}