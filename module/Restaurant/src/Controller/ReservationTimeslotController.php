<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Home\Model\City;
use Restaurant\Model\Calendar;

class ReservationTimeslotController extends AbstractRestfulController {
	public function get($restaurant_id) {
		$session = $this->getUserSession ();
		$selectedLocation = $session->getUserDetail ( 'selected_location', array () );
		$cityId = isset ( $selectedLocation ['city_id'] ) ? $selectedLocation ['city_id'] : 18848;
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
		$data = array ();
		$dateTimeObject = clone $cityDateTime;		
		if (isset ( $restaurant_id )) {
			if (empty ( $restaurant_id )) {
				throw new \Exception ( "Invalid Restaurant Id" );
			} else {
				$data = $this->getRestaurantTimeSlots ( $restaurant_id, $dateTimeObject );
			}
		}
		return $data;
	}
	private function getRestaurantTimeSlots($restaurant_id, $date) {
		$calender = new Calendar ();
		$restaurantData = $calender->getRestaurantCalender ( $restaurant_id );
		foreach ( $restaurantData as $key => $val ) {
			$calenderDay [] = $val ['calendar_day'];
		}
		$currentTime = $date->format ( 'H:i' );
		$currentDay = strtolower ( substr ( $date->format ( 'D' ), 0, 2 ) );
		$getCalenderDate = $this->getRestaurantWorkingDate ( $calenderDay, $date );
		$revMappedDay = array_flip ( StaticOptions::$dayMapping );
		$day = $revMappedDay [$getCalenderDate->format ( 'D' )];
		if ($currentDay != $day) {
			$getCalenderDate->setTime ( 0, 0, 0 );
		}
		$calenderDate = $getCalenderDate->format ( 'Y-m-d H:i:s' );
		$orderTimeSlots = StaticOptions::getRestaurantReservationTimeSlots ( $restaurant_id, $calenderDate, 'Y-m-d H:i:s' );
		$filteredOrderTimeSlots = array_filter ( $orderTimeSlots, function ($orderTimeSlot) {
			return $orderTimeSlot ['status'] == 1;
		} );
		if (empty ( $filteredOrderTimeSlots )) {
			$orderDateTime = clone $date;
			$orderDateTime->add ( new \DateInterval ( 'P1D' ) );
			$getCalenderDate = $this->getRestaurantWorkingDate ( $calenderDay, $orderDateTime );
			$getCalenderDate->setTime ( 0, 0, 0 );
			$orderTimeSlots = StaticOptions::getRestaurantReservationTimeSlots ( $restaurant_id, $getCalenderDate->format ( 'Y-m-d H:i:s' ), 'Y-m-d H:i:s' );
		}
		
		$restTimeSlots = array ();
		foreach ( $orderTimeSlots as $key => $val ) {
			if ($val ['status'] == 1) {
				$dateTime = new \DateTime ( $val ['date'] . $val ['time'] );
				$restTimeSlots ['restaurant_reservation_date_time'] = $dateTime->format ( 'D M d Y H:i A' );
				break;
			}
		}
		return $restTimeSlots;
	}
	private function getRestaurantWorkingDate($calenderDay, $date) {
		$day = $date->format ( 'D' );
		$day = substr ( strtolower ( $day ), 0, 2 );
		if (in_array ( $day, $calenderDay )) {
		} else {
			$newDate = $date->setDate ( $date->format ( 'Y' ), $date->format ( 'm' ), $date->format ( 'd' ) + 1 );
			$this->getRestaurantWorkingDate ( $calenderDay, $newDate );
		}
		return $date;
	}
}
