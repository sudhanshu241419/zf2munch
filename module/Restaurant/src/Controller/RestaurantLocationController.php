<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;

class RestaurantLocationController extends AbstractRestfulController {
	public function get($restaurant_id = 0) {
		$restaurantModel = new Restaurant ();
		$restaurantLocation = $restaurantModel->getRestaurantLocation ( $restaurant_id )->toArray ();
		
		$restaurantLocation [0] ['image_path'] = IMAGE_PATH . strtolower ( $restaurantLocation [0] ['rest_code'] ) . DS . $restaurantLocation [0] ['restaurant_image_name'];
		if (! $this->isMobile ()) {
			$state = ($restaurantLocation [0] ['state'] == 'California') ? "CA" : '';
			$restaurantLocation [0] ['full_address'] = $restaurantLocation [0] ['address'] . " " . $restaurantLocation [0] ['city_name'] . ", " . $state;
		}
		return $restaurantLocation;
	}
}
