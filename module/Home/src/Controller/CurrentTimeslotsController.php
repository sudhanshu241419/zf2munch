<?php
namespace Home\Controller;
use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Home\Model\City;
use Restaurant\Model\Calendar;
use Restaurant\Model\Restaurant;
class CurrentTimeslotsController extends AbstractRestfulController {
    public function get($restaurant_id) {
        $this->response->setHeaders(StaticOptions::getExpiryHeaders());
        $session = $this->getUserSession();
        $selectedLocation = $session->getUserDetail('selected_location', array());

        $cityId = isset($selectedLocation ['city_id']) ? $selectedLocation ['city_id'] : false;

        if (!$cityId) {
            $restaurantCityId = StaticOptions::getRestaurantCityId($restaurant_id);                
            $cityId = $restaurantCityId['city_id'];
        }
        $cityModel = new City ();
        $cityDetails = $cityModel->cityDetails($cityId);
        if (!empty($cityDetails)) {
            $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
                        'state_code' => $cityDetails [0] ['state_code']
                    ));
        }
        $data = array();       
        
        $dateTimeObject = clone $cityDateTime;
        $data ['city_date_time'] = $cityDateTime->format('D M d Y H:i:s');
        $data ['city_reservation_date_time'] = $this->getCityReservationTimeSlots($cityDateTime);
        $data ['city_order_date_time'] = $this->getCityOrderTimeSlots($cityDateTime);
        $data ['city_takeout_date_time'] = $data ['city_order_date_time'];
        if (isset($restaurant_id)) {
            if (empty($restaurant_id)) {
                throw new \Exception("Invalid Restaurant Id");
            } else {
                $data = @array_merge_recursive($data, $this->getRestaurantTimeSlots($restaurant_id, $dateTimeObject));
            }
        } else {
            $data ['restaurant_date_time'] = "";
            $data ['restaurant_reservation_timeslots'] = "";
            $data ['restaurant_order_timeslots'] = "";
            $data ['restaurant_takeout_timeslots'] = "";
        }
        
            return $data;        
    }

    public function getList() { 
        $this->response->setHeaders(StaticOptions::getExpiryHeaders());
        $session = $this->getUserSession();
        $selectedLocation = $session->getUserDetail('selected_location', array());

        $cityId = isset($selectedLocation ['city_id']) ? $selectedLocation ['city_id'] : false;

        if (empty($cityId)) {
            throw new \Exception("Invalid City Id");
        }
        $cityModel = new City ();
        $cityDetails = $cityModel->cityDetails($cityId);
        if (!empty($cityDetails)) {
            $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
                        'state_code' => $cityDetails [0] ['state_code']
                    ));
        }
        $data = array();
        //$dateTimeObject = clone $cityDateTime;
        $data ['city_date_time'] = $cityDateTime->format('D M d Y H:i:s');           
        $data ['city_reservation_date_time'] = $this->getCityReservationTimeSlots($cityDateTime);
        $data ['city_order_date_time'] = $this->getCityOrderTimeSlots($cityDateTime);        
        $data ['restaurant_date_time'] = '';
        $data ['restaurant_order_date_time'] = '';
        $data ['restaurant_reservation_date_time'] = '';
        $data ['restaurant_takeout_date_time'] = '';

        return $data;
    }

    private function getCityReservationTimeSlots($currentDateTime) {
        $currentMinute = (int) $currentDateTime->format('i');
        $currentHour = (int) $currentDateTime->format('H');
        if ($currentMinute >= 0 && $currentMinute < 30) {
            $currentDateTime->setTime($currentHour, 30, 0);
        } else {            
            $currentDateTime->setTime($currentHour +1, 00, 0);
        }

        $reservationTimeslots = $currentDateTime->format('D M d Y H:i');
        return $reservationTimeslots;
    }

    private function getCityOrderTimeSlots($currentDateTime) {
        $currentMinute = (int) $currentDateTime->format('i');
        $currentHour = (int) $currentDateTime->format('H');
        if ($currentMinute >= 0 && $currentMinute < 30) {
            $currentDateTime->setTime($currentHour, 30, 0);
        } else {
            $currentDateTime->setTime($currentHour + 1, 00, 0);
        }

        $orderTimeslots = $currentDateTime->format('D M d Y H:i');

        return $orderTimeslots;
    }

    private function getRestaurantTimeSlots($restaurant_id, $date) {
        $restaurantModel = new Restaurant();
        $opts = array('columns'=>array('accept_cc_phone'), 'where'=>array('id'=>$restaurant_id));
        $restAcceptCcPhone = $restaurantModel->findRestaurant($opts)->toArray();      
        $acceptCcPhone = $restAcceptCcPhone['accept_cc_phone'];      
        
        $calender = new Calendar ();
        $restaurantData = $calender->getRestaurantCalender($restaurant_id);
        foreach ($restaurantData as $key => $val) {
            $calenderDay [] = $val ['calendar_day'];
        }
        $currentTime = $date->format('H:i');
        
        $currentDay = strtolower(substr($date->format('D'), 0, 2));
        $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $date);
        $revMappedDay = array_flip(StaticOptions::$dayMapping);
        $day = $revMappedDay [$getCalenderDate->format('D')];
        
        if ($currentDay != $day) {
            $getCalenderDate->setTime(0, 0, 0);
        }
        $calenderDate = $getCalenderDate->format('Y-m-d H:i:s');
        
        $reservationTimeSlots = StaticOptions::getRestaurantReservationTimeSlotsForCurrenttime($restaurant_id, $calenderDate, 'Y-m-d H:i:s');
       
        $filteredReservationTimeSlots = array_filter($reservationTimeSlots, function ($reservationTimeSlots) {
            return $reservationTimeSlots ['status'] == 1;
        });

        if (empty($filteredReservationTimeSlots)) {
            $reserveDateTime = clone $date;
            for($i=1;$i<=7;$i++){
                $reserveDateTime->add(new \DateInterval('P1D'));
                $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $reserveDateTime);
                $getCalenderDate->setTime(0, 0, 0);
                $reservationTimeSlots = StaticOptions::getRestaurantReservationTimeSlotsForCurrenttime($restaurant_id, $getCalenderDate->format('Y-m-d H:i:s'), 'Y-m-d H:i:s');
                if(!empty($reservationTimeSlots)){
                    break;
                }          
            }            
        }
        
        $restTimeSlots = array();
        $restTimeSlots ['restaurant_date_time'] = '';
        $restTimeSlots ['restaurant_date_time'] = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $restaurant_id
                ))->format('D M d Y H:i:s');

        foreach ($reservationTimeSlots as $key => $val) {
            if ($val ['status'] == 1) {
                $dateTime = new \DateTime($getCalenderDate->format('Y-m-d') ." " . $val ['slot']);
                $restTimeSlots ['restaurant_reservation_date_time'] = $dateTime->format('D M d Y H:i');
                break;
            }
        }
        $takeoutDate = $date;
        $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $date);
        $orderTimeSlots = StaticOptions::getRestaurantOrderTimeSlots($restaurant_id, $calenderDate, 'Y-m-d H:i:s');
        $filteredOrderTimeSlots = array_filter($orderTimeSlots, function ($orderTimeSlot) {
            return $orderTimeSlot ['status'] == 1;
        });
        if (empty($filteredOrderTimeSlots)) {
            for($i=0;$i<=7;$i++){ 
                $orderDateTime = clone $date;
                $orderDateTime->add(new \DateInterval('P1D'));
                $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $orderDateTime);
                $getCalenderDate->setTime(0, 0, 0);
                $orderTimeSlots = StaticOptions::getRestaurantOrderTimeSlots($restaurant_id, $getCalenderDate->format('Y-m-d H:i:s'), 'Y-m-d H:i:s');
                    if(!empty($orderTimeSlots)){
                        break;
                    }
                $date = $getCalenderDate;
            }            
        }
         foreach ($orderTimeSlots as $key => $val) {
            if ($val ['status'] == 1) {
                $dateTime = new \DateTime($getCalenderDate->format('Y-m-d'). " " . $val ['slot']);
                $orderTimeSlot = explode("-", ORDER_TIME_SLOT);
                $cDate = $dateTime->format("Y-m-d")." ".$orderTimeSlot[0].":00";
                
                if(strtotime($dateTime->format('Y-m-d H:i')) < strtotime($cDate)){
                    $restTimeSlots ['restaurant_order_date_time'] = $cDate;
                }else{
                    $restTimeSlots ['restaurant_order_date_time'] = $dateTime->format('D M d Y H:i');
                }
                break;
            }
        }
        
        $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $takeoutDate);
        $takeoutTimeSlots = StaticOptions::getRestaurantTakeoutTimeSlots($restaurant_id, $calenderDate, 'Y-m-d H:i:s');
        $filteredTakeoutTimeSlots = array_filter($takeoutTimeSlots, function ($takeoutTimeSlot) {
            return $takeoutTimeSlot ['status'] == 1;
        });
        if (empty($filteredTakeoutTimeSlots)) {
            $takeoutDateTime = clone $takeoutDate;

            $takeoutDateTime->add(new \DateInterval('P1D'));
            $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $takeoutDateTime);
            $getCalenderDate->setTime(0, 0, 0);
            $takeoutTimeSlots = StaticOptions::getRestaurantTakeoutTimeSlots($restaurant_id, $getCalenderDate->format('Y-m-d H:i:s'), 'Y-m-d H:i:s');
        }
        foreach ($takeoutTimeSlots as $key => $val) {
            if ($val ['status'] == 1) {
                $dateTime = new \DateTime($getCalenderDate->format('Y-m-d'). " " . $val ['slot']);
                $orderTimeSlot = explode("-", ORDER_TIME_SLOT);
                $cDate = $dateTime->format("Y-m-d")." ".$orderTimeSlot[0].":00";
                if(strtotime($dateTime->format('Y-m-d H:i')) < strtotime($cDate)){
                    $restTimeSlots ['restaurant_takeout_date_time'] = $cDate;
                }else{
                    $restTimeSlots ['restaurant_takeout_date_time'] = $dateTime->format('D M d Y H:i');
                }
                break;
            }
        }
       $restTimeSlots['accept_cc_phone']= $acceptCcPhone;
       $restTimeSlots['restaurant_open_at'] =  \Restaurant\RestaurantDetailsFunctions::getRestaurantOpenAt($restaurant_id, $restTimeSlots ['restaurant_date_time'],$restTimeSlots ['restaurant_reservation_date_time']);
       return $restTimeSlots;
    }

    private function getRestaurantWorkingDate($calenderDay, $date) {
        $day = $date->format('D');
        $day = substr(strtolower($day), 0, 2);
        if (!in_array($day, $calenderDay)) {
            $newDate = $date->setDate($date->format('Y'), $date->format('m'), $date->format('d') + 1);
            $this->getRestaurantWorkingDate($calenderDay, $newDate);
        }
       
        return $date;
    }
 }
