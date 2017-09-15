<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Home\Model\City;
use Restaurant\Model\Calendar;

class TimeslotsController extends AbstractRestfulController {
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
			if ($type == 'order') {
				$timeSlots = StaticOptions::getRestaurantOrderTimeSlots ( $restaurant_id, $newDate, 'Y-m-d H:i:s' );
			} else {
				$timeSlots = StaticOptions::getRestaurantReservationTimeSlots ( $restaurant_id, $newDate, 'Y-m-d H:i:s' );
			}
			$restTimeSlots = array ();
			foreach ( $timeSlots as $key => $val ) {
				if ($val ['status'] == 1) {
					$dateTime = new \DateTime ( $val ['date'] . $val ['time'] );
					$restTimeSlots [] = $dateTime->format ( 'h:i A' );
				}
			}
			$data [$i] ['timeslots'] = $restTimeSlots;
			$i ++;
		}
		return $data;
	}
	public function getList() {
		$session = $this->getUserSession ();
		$selectedLocation = $session->getUserDetail ( 'selected_location', array () );
		$cityId = isset ( $selectedLocation ['city_id'] ) ? $selectedLocation ['city_id'] : 18848;
        $dateQueryParameter = $this->getQueryParams('date',false);  
        $rfTimeslot = $this->getQueryParams('refine',false);//1
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
		$currentCityDateTime = $cityDateTime->format ( 'Y-m-d H:i');        
		$data = array ();       
		$dateArray = $this->getCityDate ( $cityDateTime );
         if($rfTimeslot){
             if($dateQueryParameter === false || strtotime($dateQueryParameter) < strtotime($currentCityDateTime)){ 
                $dateQueryParameterArray = explode(" ",$currentCityDateTime);
                $dateQueryParameter = $dateQueryParameterArray[0];
            }     
            $data ['city_currentdatetime'] = $currentCityDateTime;   
            $explode = explode(" ", $currentCityDateTime);
            
            //echo date('Y-m-d H:i', strtotime($explode[0]." 23:59"));
            if(strtotime($currentCityDateTime) >= strtotime($explode[0]." 23:00") && strtotime($currentCityDateTime) <= strtotime($explode[0]." 23:59")){
            	$data['current_first_slot']=date("Y-m-d H:i",strtotime("+1 day"));   
            }else{
            	$data['current_first_slot']=$currentCityDateTime;   
            }  
               
            if ($type == 'order') {
                $data ['timeslots'] = $this->getTimeSlotsOrder ( $dateQueryParameter, $cityDetails [0] ['state_code'], true );
            } else {
                $data ['timeslots'] = $this->getTimeSlotsReservation ( $dateQueryParameter, $cityDetails [0] ['state_code'], true );
            }                   
         }else{        
            foreach ( $dateArray as $key => $date ) {		           
                $data [$key] ['date'] = $date ['date'];
                $data [$key] ['day'] = $date ['day'];  
                if ($type == 'order') {
                    $data [$key] ['timeslots'] = $this->getTimeSlotsOrder ( $date ['date'], $cityDetails [0] ['state_code'] );
                } else {
                    $data [$key] ['timeslots'] = $this->getTimeSlotsReservation ( $date ['date'], $cityDetails [0] ['state_code'] );
                }
            }
         }
        
		return $data;
	}
	private function getTimeSlotsOrder($date, $state_code , $apiFor=false) {
		$timeSlots = StaticOptions::getAllTimeSlots ( $date, $state_code );        
        if($apiFor){
            foreach ( $timeSlots ['slots'] as $key => $val ) {     
                $dateTime = new \DateTime ( $timeSlots ['date'] . $val );
                $Slots [] = $dateTime->format ( 'Y-m-d H:i' );
            }
        }else{		
            foreach ( $timeSlots ['slots'] as $key => $val ) {
                $dateTime = new \DateTime ( $timeSlots ['date'] . $val );
                $Slots [] = $dateTime->format ( 'h:i A' );
            }
            unset ( $timeSlots ['date'] );
            unset ( $timeSlots ['day'] );
        }
		return $Slots;
	}
	private function getTimeSlotsReservation($date, $state_code, $apiFor=false) {
		$timeSlots = StaticOptions::getAllTimeSlotsReservation ( $date, $state_code );
		if($apiFor){
            foreach ( $timeSlots ['slots'] as $key => $val ) {     
                $dateTime = new \DateTime ( $timeSlots ['date'] . $val );
                $Slots [] = $dateTime->format ( 'Y-m-d H:i' );
            }
        }else{	
            foreach ( $timeSlots ['slots'] as $key => $val ) {           
                $dateTime = new \DateTime ( $timeSlots ['date'] . $val );
                $Slots [] = $dateTime->format ( 'h:i A' );
            }
            unset ( $timeSlots ['date'] );
            unset ( $timeSlots ['day'] );
        }
		return $Slots;
	}
	private function getCityDate($dateTimeObject) {
		$dayDate = array ();
		$currentTime = $dateTimeObject->format ( 'H:i' );
		$dayDate [0] ['date'] = $dateTimeObject->format ( 'Y-m-d' );
		$dayDate [0] ['day'] = $dateTimeObject->format ( 'D' );
		if (strtotime ( $currentTime ) >= strtotime ( "23:00:00" )) { // check if time is above 23:00 hours add one to date and show next day slots
			$dateTimeObject->setDate ( $dateTimeObject->format ( 'Y' ), $dateTimeObject->format ( 'm' ), $dateTimeObject->format ( 'd' ) + 1 );
			$dayDate [0] ['date'] = $dateTimeObject->format ( 'Y-m-d' );
			$dayDate [0] ['day'] = $dateTimeObject->format ( 'D' );
		}
		$i = 1;
		for($i; $i < 7; $i ++) {
			$dateTimeObject->setDate ( $dateTimeObject->format ( 'Y' ), $dateTimeObject->format ( 'm' ), $dateTimeObject->format ( 'd' ) + 1 );
			$dayDate [$i] ['date'] = $dateTimeObject->format ( 'Y-m-d' );
			$dayDate [$i] ['day'] = $dateTimeObject->format ( 'D' );
		}
		return $dayDate;
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
