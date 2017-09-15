<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Calendar;
use MCommons\StaticOptions;

class RestaurantTimeSlotController extends AbstractRestfulController {
	public function get($restaurant_id = 0) {
		$response = array ();
		if (! $restaurant_id)
			throw new \Exception ( "Restaurant not available", 400 );
		
		$currentDateTime = StaticOptions::getDateTime ()->format ( StaticOptions::MYSQL_DATE_FORMAT );
		$currentDay = StaticOptions::getFormattedDateTime ( $currentDateTime, 'Y-m-d H:i:s', 'D' );
		$currentDate = StaticOptions::getFormattedDateTime ( $currentDateTime, 'Y-m-d H:i:s', 'd-m-Y' );
		$date_selected = $this->getQueryParams ( 'date', $currentDate );
		
		if (strtotime ( $date_selected ) < strtotime ( $currentDate ))
			throw new \Exception ( "Invalid date", 400 );
			
			// Get opening and closing hours
		$calendarModel = new Calendar ();
		$restaurant_calender = $calendarModel->getOpeningHours ( array (
				'columns' => array (
						'calendar_day',
						'open_time',
						'close_time',
						'open_close_status' 
				),
				'where' => array (
						'status' => 1,
						'restaurant_id' => $restaurant_id 
				)
				 
		) );
		
		$dayDate = array ();
		
		if (! empty ( $restaurant_calender )) {
			foreach ( $restaurant_calender as $day ) {
				
				$days [] = $day ['display_day'];
				$open_close_time [$day ['display_day']] ['open_time'] = $day ['open_time'];
				$open_close_time [$day ['display_day']] ['close_time'] = $day ['close_time'];
			}
		} else {
			throw new \Exception ( "Restaurant calender not available", 400 );
		}
		
		for($i = 0; $i < 7; $i ++) {
			
			if (in_array ( date ( "D", strtotime ( $date_selected . '+' . $i . ' day' ) ), $days )) {
				
				$dayDate [$i] ['date'] = date ( "D, M d", strtotime ( $date_selected . '+' . $i . ' day' ) );
				$day = date ( "D", strtotime ( $date_selected . '+' . $i . ' day' ) );
				$dayDate [$i] ['open_time'] = $open_close_time [$day] ['open_time'];
				$dayDate [$i] ['close_time'] = $open_close_time [$day] ['close_time'];
			} else {
				$dayDate [$i] ['date'] = date ( "D, M d", strtotime ( $date_selected . '+' . $i . ' day' ) );
				$dayDate [$i] ['open_time'] = '';
				$dayDate [$i] ['close_time'] = '';
			}
		}
		return $dayDate;
	}
}
