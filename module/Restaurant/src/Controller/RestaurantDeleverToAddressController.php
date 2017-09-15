<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\RestaurantDistanceCalculationFunction;

class RestaurantDeleverToAddressController extends AbstractRestfulController {
	public function get($restaurant_id = 0) {
		try {
			if (! $restaurant_id) {
				throw new \Exception ( "Invalid restaurant detail", 400 );
			} else {
				$delivery_address = $this->getQueryParams ( 'delivery_address' );
				
				if (! $delivery_address)
					throw new \Exception ( "Delivery address is required", 400 );
					
					/*
				 * Get Restaurant Distance
				 */
				$distanceCalculation = new RestaurantDistanceCalculationFunction ();
				$restaurantDistance = $distanceCalculation->checkRestaurantDistance( $restaurant_id, $delivery_address );
			}
		} catch ( \Exception $ex ) {
			return $this->sendError ( array (
					'error' => $ex->getMessage () 
			), $ex->getCode () );
		}
		if ($restaurantDistance ['delivery_status'])
			return array (
					'delivery_status' => 1 
			);
		else
			return array (
					'delivery_status' => 0 
			);
	}
}