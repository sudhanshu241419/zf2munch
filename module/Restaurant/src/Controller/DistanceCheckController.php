<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\RestaurantDistanceCalculationFunction;

class DistanceCheckController extends AbstractRestfulController {
	
	/*
	 * this function returns delivery status of address
	 */
	public function get($restaurant_id = 0) {
		$response = array ();
		$delAddress = $this->getQueryParams ( 'address' );
		
		if (empty ( $delAddress )) {
			return $this->sendError ( 'Input delivery address', 404 );
		}
		
		$distanceCalculation = new RestaurantDistanceCalculationFunction ();
		$response ['locationData'] = $distanceCalculation->checkRestaurantDistance ( $restaurant_id, $delAddress );
		return $response;
	}
}//End of class
