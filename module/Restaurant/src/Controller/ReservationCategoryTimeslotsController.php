<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Home\Model\City;
use Restaurant\Model\Calendar;

class ReservationCategoryTimeslotsController extends AbstractRestfulController {
	public function get($restaurant_id) {
		$session = $this->getUserSession ();
		$selectedLocation = $session->getUserDetail ( 'selected_location', array () );
		$cityId = isset ( $selectedLocation ['city_id'] ) ? $selectedLocation ['city_id'] : 23637;
		
		$type = $this->getQueryParams ( 'type' );
		if (empty ( $cityId )) {
			throw new \Exception ( "Invalid City Id" );
		}
		$cityModel = new City ();
		$cityDetails = $cityModel->cityDetails ( $cityId );
		if (! empty ( $cityDetails )) {
			$timeZone = $cityDetails [0] ['time_zone'];
			$cityDateTime = StaticOptions::getRelativeCityDateTime ( array (
					'state_code' => $cityDetails [0] ['state_code'] 
			) );
		}
		$calender = new Calendar ();
		$restaurantData = $calender->getRestaurantCalender ( $restaurant_id );
		foreach ( $restaurantData as $key => $val ) {
			$restaurant_calender [] = $val ['calendar_day'];
		}
		
		$revMappedDay = array_flip ( StaticOptions::$dayMapping );
		$day = $revMappedDay [$cityDateTime->format ( 'D' )];
		$isRestaurantOpen = $calender->isRestaurantOpen ( array (
				'columns' => array (
						'calendar_day',
						'open_time',
						'close_time',
						'open_close_status' 
				),
				'where' => array (
						'status' => 1,
						'restaurant_id' => $restaurant_id,
						'calendar_day' => $day 
				) 
		), $restaurant_id );
		$data = array ();
		$dateTimeObject = clone $cityDateTime;
		$dateArray = $this->getRestaurantDate ( $cityDateTime, $restaurant_id, $restaurant_calender );
		$i = 0;
		foreach ( $dateArray as $key => $date ) {
			$data [$i] ['date'] = $date ['date'];
			$data [$i] ['day'] = $date ['day'];
			if ($isRestaurantOpen && $date ['date'] == $dateTimeObject->format ( 'Y-m-d' )) {
				$newDate = $dateTimeObject->format ( 'Y-m-d H:i:s' );
			} else {
				$newDateTime = new \DateTime ( $date ['date'] );
				$newDateTime->setTime ( 0, 0, 0 );
				$newDate = $newDateTime->format ( 'Y-m-d H:i:s' );
			}
			
			$timeSlots = StaticOptions::getRestaurantReservationTimeSlots ( $restaurant_id, $newDate, 'Y-m-d H:i:s' );
			$breakfastTimeSlots = StaticOptions::getRestaurantBreakfastReservationTimeSlots ( $restaurant_id, $newDate, 'Y-m-d H:i:s' );
			$lunchTimeSlots = StaticOptions::getRestaurantLunchReservationTimeSlots ( $restaurant_id, $newDate, 'Y-m-d H:i:s' );
			$dinnerTimeSlots = StaticOptions::getRestaurantDinnerReservationTimeSlots ( $restaurant_id, $newDate, 'Y-m-d H:i:s' );
			$restTimeSlots = array ();
			$breakfastRestTimeSlots = array ();
			$lunchRestTimeSlots = array ();
			$dinnerRestTimeSlots = array ();
			if (empty ( $breakfastTimeSlots )) {
				foreach ( $timeSlots as $key => $val ) {
					if ($val ['status'] == 1) {
						$dateTime = new \DateTime ( $val ['date'] . $val ['time'] );
						$breakfastRestTimeSlots [] = $dateTime->format ( 'h:i A' );
					}
				}
			} else {
				foreach ( $breakfastTimeSlots as $key => $val ) {
					if ($val ['status'] == 1) {
						$dateTime = new \DateTime ( $val ['date'] . $val ['time'] );
						$breakfastRestTimeSlots [] = $dateTime->format ( 'h:i A' );
					}
				}
			}
			if (empty ( $lunchTimeSlots )) {
				foreach ( $timeSlots as $key => $val ) {
					if ($val ['status'] == 1) {
						$dateTime = new \DateTime ( $val ['date'] . $val ['time'] );
						$lunchRestTimeSlots [] = $dateTime->format ( 'h:i A' );
					}
				}
			} else {
				foreach ( $lunchTimeSlots as $key => $val ) {
					if ($val ['status'] == 1) {
						$dateTime = new \DateTime ( $val ['date'] . $val ['time'] );
						$lunchRestTimeSlots [] = $dateTime->format ( 'h:i A' );
					}
				}
			}
			if (empty ( $dinnerTimeSlots )) {
				foreach ( $timeSlots as $key => $val ) {
					if ($val ['status'] == 1) {
						$dateTime = new \DateTime ( $val ['date'] . $val ['time'] );
						$dinnerRestTimeSlots [] = $dateTime->format ( 'h:i A' );
					}
				}
			} else {
				foreach ( $dinnerTimeSlots as $key => $val ) {
					if ($val ['status'] == 1) {
						$dateTime = new \DateTime ( $val ['date'] . $val ['time'] );
						$dinnerRestTimeSlots [] = $dateTime->format ( 'h:i A' );
					}
				}
			}
			$data [$i] ['breakfast_timeslots'] = $breakfastRestTimeSlots;
			$data [$i] ['lunch_timeslots'] = $lunchRestTimeSlots;
			$data [$i] ['dinner_timeslots'] = $dinnerRestTimeSlots;
			$i ++;
		}
		return $data;
	}
	private function getRestaurantDate($dateTimeObject, $restaurant_id, $restaurant_calender) {
		if (! empty ( $restaurant_id ) && ! empty ( $restaurant_calender )) {
			$dayDate = array ();
			$i = 0;
			$count = 0;
			for($i; $i < 50; $i ++) {
				if (in_array ( strtolower ( substr ( $dateTimeObject->format ( 'D' ), 0, 2 ) ), $restaurant_calender )) {
					$dayDate [$i] ['date'] = $dateTimeObject->format ( 'Y-m-d' );
					$dayDate [$i] ['day'] = $dateTimeObject->format ( 'D' );
					$dateTimeObject->setDate ( $dateTimeObject->format ( 'Y' ), $dateTimeObject->format ( 'm' ), $dateTimeObject->format ( 'd' ) + 1 );
					$count ++;
				} else {
					$dateTimeObject->setDate ( $dateTimeObject->format ( 'Y' ), $dateTimeObject->format ( 'm' ), $dateTimeObject->format ( 'd' ) + 1 );
				}
				if ($count == 7) {
					break;
				}
			}
		}
		return $dayDate;
	}
}
