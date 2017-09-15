<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;

class RestaurantShortAddressController extends AbstractRestfulController {
	public function get($restaurant_id = 0) {
		$restaurantModel = new Restaurant ();
		$resShortAddressAndMiles = $restaurantModel->getRestaurantShortAddress ( $restaurant_id );
		return array (
				$resShortAddressAndMiles 
		);
	}
}