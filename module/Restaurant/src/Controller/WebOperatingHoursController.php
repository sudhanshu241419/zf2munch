<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Calendar;
use MCommons\StaticOptions;
use Restaurant\RestaurantDetailsFunctions;

class WebOperatingHoursController extends AbstractRestfulController {

    public function get($id) {
        $operatioHours = array();
        $date = $this->getQueryParams('date',false);
        if(!$date || empty($date)){
            $timestamp = strtotime(StaticOptions::getRelativeCityDateTime(array('restaurant_id' => $id))->format(StaticOptions::MYSQL_DATE_FORMAT));
            $date = date("Y-m-d", $timestamp);
        }
       
        $calendar = new Calendar();
        $inputDate = StaticOptions::getAbsoluteCityDateTime(array(
                    'restaurant_id' => $id
                        ), $date, 'Y-m-d');
        $slots = $calendar->getOrderOpenCloseSlots($id, $inputDate->format('Y-m-d H:i'));
        //print_r($slots);
        $finalSlots = array();
        $orderSlots = "";
        if (!empty($slots) && !empty($slots['slotFromYesterday'])) {
            $finalSlots[] = $slots['slotFromYesterday']['open']->format('g:i A') . ' - ' . $slots['slotFromYesterday']['close']->format('g:i A');
        }
        if (!empty($slots['slotsFromToday'])) {
            foreach ($slots['slotsFromToday'] as $slot) {
               $finalSlots[] = $slot['open']->format('g:i A') . ' - ' . $slot['close']->format('g:i A');               
            }
        }
        $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus ( $id, $date );
       
        if($currentDayDelivery){
         $operatioHours['delivery'][] = implode(",",array_unique($finalSlots));
        }else{
         $operatioHours['delivery'][] = '';
        }
        //$operatioHours['delivery'] = array_unique($finalSlots);

        //reservation timings
        // Current Date
        $flippedMapping = array_flip(StaticOptions::$dayMapping);
        $currDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                    'restaurant_id' => $id
                        ), $date, 'Y-m-d');
        $currDateString = $currDateTime->format('Y-m-d');
        $currDay = $currDateTime->format('D');
        $currDayAbbr = $flippedMapping[$currDay];
        $options = array(
            'columns' => array(
                'operation_hours' => 'operation_hrs_ft'
            ),
            'where' => array(
                'restaurant_id' => $id,
                'status' => 1,
                'open_close_status > ?' => 1,
                'calendar_day' => $currDayAbbr
            )
        );
        $restaurantCalendar = new Calendar();
        $restaurantCalendar->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $restaurantCalendar->find($options)->current();
        if($response){
            $responseDetail = $response->getArrayCopy();
            if (isset($responseDetail['operation_hours'])) {
                $operatioHours['reservation'][] = trim($responseDetail['operation_hours']);
            }
        }else{
            $operatioHours['reservation']=array();
        }
        return $operatioHours;
    }

}
