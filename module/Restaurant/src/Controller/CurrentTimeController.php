<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;

class CurrentTimeController extends AbstractRestfulController {
	public function get($restaurant_id = 0) {
		$response = array ();
		$datetimeFormat = $this->getQueryParams ( 'output_datetime_format', 'Y-m-d H:i:s' );
		$response ['current_datetime'] = StaticOptions::getRelativeCityDateTime ( array (
				'restaurant_id' => $restaurant_id 
		) )->format ( $datetimeFormat );
		return $response;
	}
}
