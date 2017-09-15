<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Calendar;

class ClosedDateController extends AbstractRestfulController {
	public function get($restaurant_id = 0) {
		$outputDatetimeFormat = $this->getQueryParams ( 'output_datetime_format', 'Y-m-d' );
		$calendarModel = new Calendar ();
		$closedDates = $calendarModel->getClosedDate ( $restaurant_id, $outputDatetimeFormat );
		
		return array (
				'closed_date' => $closedDates 
		);
	}
}