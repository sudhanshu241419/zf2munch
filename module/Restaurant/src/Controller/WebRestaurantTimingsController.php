<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Restaurant\RestaurantDetailsFunctions;
use MCommons\StaticOptions;
use Home\Model\City;
use Restaurant\Model\Calendar;
class WebRestaurantTimingsController extends AbstractRestfulController {

    public function get($id) {
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
        if (isset($id)) {
            if (empty($id)) {
                throw new \Exception("Invalid Restaurant Id");
            } else {
                $masterResponse['timings'] ['city_date_time'] = $cityDateTime->format('D M d Y H:i:s');
                $masterResponse['timings'] ['city_reservation_date_time'] = $this->getCityReservationTimeSlots($cityDateTime);
                $masterResponse['timings'] ['city_order_date_time'] = $this->getCityOrderTimeSlots($cityDateTime);
                $masterResponse['timings'] ['city_takeout_date_time'] = $masterResponse['timings'] ['city_order_date_time'];
                $today = $cityDateTime->format('Y-m-d H:i:s');
                $day = strtolower(substr(date('D', strtotime($today)), 0, 2));

                $days = StaticOptions::$days;
                $restaurantDetailsFunctions = new RestaurantDetailsFunctions();
                $finalTimings = $restaurantDetailsFunctions->getRestaurantDisplayTimings($id);
                $masterResponse ['calendar'] = $finalTimings;
                $calendarModel = new Calendar();
                $masterResponse['timings']['restuarant_open_close'] = $calendarModel->isRestaurantOpen($id);
                $openDateTimeOfRestaurant = $this->getRestaurantTimeSlots($id, $cityDateTime);
               
                $masterResponse['timings']['restaurant_reservation_date_time'] = $openDateTimeOfRestaurant['restaurant_reservation_date_time'];
                $masterResponse['timings']['restaurant_date_time'] = $openDateTimeOfRestaurant['restaurant_date_time'];
                if (isset($openDateTimeOfRestaurant['restaurant_order_date_time']))
                    $masterResponse['timings']['restaurant_order_date_time'] = $openDateTimeOfRestaurant['restaurant_order_date_time'];
                if (isset($openDateTimeOfRestaurant['restaurant_takeout_date_time']))
                    $masterResponse['timings']['restaurant_takeout_date_time'] = $openDateTimeOfRestaurant['restaurant_takeout_date_time'];
                
                $restaurntDate = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $id
                ))->format('D M d Y H:i:s');       
                $masterResponse['timings']['restaurant_open_at']=$restaurantDetailsFunctions::getRestaurantOpenAt($id, $restaurntDate, $openDateTimeOfRestaurant['restaurant_reservation_date_time']);
            }
        } else {
            $data ['restaurant_date_time'] = "";
            $data ['restaurant_reservation_timeslots'] = "";
            $data ['restaurant_order_timeslots'] = "";
            $data ['restaurant_takeout_timeslots'] = "";
        }
        
        return $masterResponse;
    }

    private function getRestaurantTimeSlots($restaurant_id, $date) {
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

        $reservationTimeSlots = StaticOptions::getRestaurantReservationTimeSlots($restaurant_id, $calenderDate, 'Y-m-d H:i:s');
         $filteredReservationTimeSlots = array_filter($reservationTimeSlots, function ($reservationTimeSlots) {
            return $reservationTimeSlots ['status'] == 1;
        });
        if (empty($filteredReservationTimeSlots)) {
            $reserveDateTime = clone $date;
            $reserveDateTime->add(new \DateInterval('P1D'));
            $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $reserveDateTime);
            $getCalenderDate->setTime(0, 0, 0);
            $reservationTimeSlots = StaticOptions::getRestaurantReservationTimeSlots($restaurant_id, $getCalenderDate->format('Y-m-d H:i:s'), 'Y-m-d H:i:s');
        }

        $restTimeSlots = array();
        $restTimeSlots ['restaurant_date_time'] = '';
        $restTimeSlots ['restaurant_date_time'] = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $restaurant_id
                ))->format('D M d Y H:i:s');

        foreach ($reservationTimeSlots as $key => $val) {
            if ($val ['status'] == 1) {
                $dateTime = new \DateTime($getCalenderDate->format('Y-m-d') . " " . $val ['slot']);
                $restTimeSlots ['restaurant_reservation_date_time'] = $dateTime->format('D M d Y H:i');
                break;
            }
        }

        $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $date);
        $orderTimeSlots = StaticOptions::getRestaurantOrderTimeSlots($restaurant_id, $calenderDate, 'Y-m-d H:i:s');
        $filteredOrderTimeSlots = array_filter($orderTimeSlots, function ($orderTimeSlot) {
            return $orderTimeSlot ['status'] == 1;
        });
        if (empty($filteredOrderTimeSlots)) {
            $orderDateTime = clone $date;
            $orderDateTime->add(new \DateInterval('P1D'));
            $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $orderDateTime);
            $getCalenderDate->setTime(0, 0, 0);
            $orderTimeSlots = StaticOptions::getRestaurantOrderTimeSlots($restaurant_id, $getCalenderDate->format('Y-m-d H:i:s'), 'Y-m-d H:i:s');
        }

        foreach ($orderTimeSlots as $key => $val) {
            if ($val ['status'] == 1) {
                $dateTime = new \DateTime($getCalenderDate->format('Y-m-d') . " " . $val ['slot']);
                $restTimeSlots ['restaurant_order_date_time'] = $dateTime->format('D M d Y H:i');
                break;
            }
        }

        $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $date);
        $takeoutTimeSlots = StaticOptions::getRestaurantTakeoutTimeSlots($restaurant_id, $calenderDate, 'Y-m-d H:i:s');
        $filteredTakeoutTimeSlots = array_filter($takeoutTimeSlots, function ($takeoutTimeSlot) {
            return $takeoutTimeSlot ['status'] == 1;
        });
        if (empty($filteredTakeoutTimeSlots)) {
            $takeoutDateTime = clone $date;
            $takeoutDateTime->add(new \DateInterval('P1D'));
            $getCalenderDate = $this->getRestaurantWorkingDate($calenderDay, $takeoutDateTime);
            $getCalenderDate->setTime(0, 0, 0);
            $takeoutTimeSlots = StaticOptions::getRestaurantOrderTimeSlots($restaurant_id, $getCalenderDate->format('Y-m-d H:i:s'), 'Y-m-d H:i:s');
        }
        foreach ($takeoutTimeSlots as $key => $val) {
            if ($val ['status'] == 1) {
                $dateTime = new \DateTime($getCalenderDate->format('Y-m-d') . " " . $val ['slot']);
                $restTimeSlots ['restaurant_takeout_date_time'] = $dateTime->format('D M d Y H:i');
                break;
            }
        }
        // $restTimeSlots['date_time'] = $date->format('D M d Y H:i:s');
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

    private function getCityReservationTimeSlots($currentDateTime) {
        $currentMinute = (int) $currentDateTime->format('i');
        $currentHour = (int) $currentDateTime->format('H');
        if ($currentMinute >= 0 && $currentMinute < 30) {
            $currentDateTime->setTime($currentHour, 30, 0);
        } else {
            $currentDateTime->setTime($currentHour + 1, 0, 0);
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

}
