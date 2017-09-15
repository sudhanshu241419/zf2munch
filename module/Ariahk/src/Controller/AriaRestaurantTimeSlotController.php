<?php

namespace Ariahk\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Restaurant\Model\RestaurantDineinCalendars;
use User\Model\UserReservation;
use Restaurant\ReservationFunctions;
use Restaurant\Model\RestaurantAccounts;

class AriaRestaurantTimeSlotController extends AbstractRestfulController {

    public function get($id) {
        $response = null;
        $type = $this->getQueryParams('type');
        $date = $this->getQueryParams('date');
        $requested_seat = $this->getQueryParams('partysize', false);
        $reservation_id = $this->getQueryParams('reservation_id', false);
        $ariaFunction = new \Ariahk\AriaFunctions();
        $selectedLocation = $this->getUserSession()->getUserDetail('selected_location', array());
        $stateCode = isset($selectedLocation ['state_code']) ? $selectedLocation ['state_code'] : 'NY';
        if (!isset($date) || empty($date)) {
            $date = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $id,
                        'state_code' => $stateCode
                    ))->format('Y-m-d');
        }
        
        if (!isset($type)) {
            throw new \Exception('Please provide the type');
        }
        if (($type == 'null')||(empty($type))) {
            $type ='takeout';
        }
        
        if ($requested_seat) {
            $requested_seat = $this->getQueryParams('partysize');
        } else {
            $requested_seat = 5;
        }
        if ($type == 'reservation') {
            $reservationFinal ['timeslots'] = $ariaFunction->getAriaReservationTimeSlots($id, $date);
            foreach ($reservationFinal ['timeslots'] as &$final) {
                $final ['time'] = $final ['slot'];
                unset($final ['slot']);
            }

            $reservationFunction = new ReservationFunctions();
            $userReservationModel = new UserReservation();
            $restaurantDineinCalendars = new RestaurantDineinCalendars();
            $restaurantAccount = new RestaurantAccounts ();
            $options = array("where" => array("restaurant_id" => $id));
            $dineinCalendarsDetail = $restaurantDineinCalendars->findRestaurantDineinDetail($options);
            $restaurantAccountDetails = $restaurantAccount->getRestaurantAccountDetail(array('where' => array(
                    'restaurant_id' => $id,
                )
            ));
            if (isset($dineinCalendarsDetail) && !empty($dineinCalendarsDetail) && $restaurantAccountDetails) {
                $restaurantAllocatedSeat = 0;
                $totalSeatCount = 0;
                $date = date("Y-m-d", strtotime($date));
                foreach ($reservationFinal['timeslots'] as $timeslotkey => $val) {
                    $totalSeatCount = 0;
                    $seatCount = 0;
                    $requested_time = strtotime($reservationFinal['timeslots'][$timeslotkey]['time']);
                    $time_slot = $date . " " . $reservationFinal['timeslots'][$timeslotkey]['time']; //	"2014-10-08 11:00";
                    $data = array('reserved_seats' => $requested_seat, 'time' => $reservationFinal['timeslots'][$timeslotkey]['time'], 'date' => $date, 'time_slot' => $time_slot, 'restaurant_id' => $id);
                    $dst = strtotime($dineinCalendarsDetail['dinner_start_time']);
                    $det = strtotime($dineinCalendarsDetail['dinner_end_time']);
                    if (strtotime($dineinCalendarsDetail['breakfast_start_time']) <= $requested_time && $requested_time <= strtotime($dineinCalendarsDetail['breakfast_end_time'])) {
                        $restaurantAllocatedSeat = $dineinCalendarsDetail['breakfast_seats'];
                    } elseif (strtotime($dineinCalendarsDetail['lunch_start_time']) <= $requested_time && $requested_time <= strtotime($dineinCalendarsDetail['lunch_end_time'])) {
                        $restaurantAllocatedSeat = $dineinCalendarsDetail['lunch_seats'];
                    } else {
                        $restaurantAllocatedSeat = $dineinCalendarsDetail['dinner_seats'];
                    }
                    ## If Allocated seat is less than Requested seat--Decline the reservation HERE ##
                    if ($restaurantAllocatedSeat < $requested_seat) {
                        $reservationFinal['timeslots'][$timeslotkey]['status'] = 0;
                    }
                    ## Get Occupied Seat ##
                    $getExistingReservation = array('restaurant_id' => $id, 'time_slot' => $time_slot, 'reservation_id' => $reservation_id);
                    $existingReservation = $userReservationModel->getUserReservationToCheckSeat($getExistingReservation);
                    if (!empty($existingReservation)) {
                        foreach ($existingReservation as $key => $val) {
                            $seatCount = $seatCount + $val['reserved_seats'];
                        }
                        $totalSeatCount = $seatCount + $requested_seat;
                    }
                    ## If Occupied Seat is greater or equel to Restaurant allocated seat then decline the reservation HERE ##
                    if ($totalSeatCount > $restaurantAllocatedSeat) {
                        $reservationFinal['timeslots'][$timeslotkey]['status'] = 0;
                    } else {

                        $smallGroupBackwordSeatCount = 0;
                        $smallGroupForwordSeatCount = 0;
                        $largeGroupBackwordSeatCount = 0;
                        $largeGroupForwordSeatCount = 0;
                        $largeGroupBackwordImpactSeatCount = 0;

                        if ($dineinCalendarsDetail['dinningtime_small'] > TIME_INTERVAL) {
                            $smallGroupBackwordSeatCount = $reservationFunction->checkSmallBackword($dineinCalendarsDetail, $data, $reservation_id);
                            $smallGroupForwordSeatCount = $reservationFunction->checkSmallForword($dineinCalendarsDetail, $data, $reservation_id);
                        }
                        //calculate total seat occupied by large group
                        if ($dineinCalendarsDetail['dinningtime_large'] > TIME_INTERVAL) {
                            $largeGroupBackwordSeatCount = $reservationFunction->checkLargeBackword($dineinCalendarsDetail, $data, $reservation_id);
                            $largeGroupForwordSeatCount = $reservationFunction->checkLargeForword($dineinCalendarsDetail, $data, $reservation_id);
                        }
                        //caclculate carry forward reservation on future time slots
                        if ($dineinCalendarsDetail['dinningtime_large'] > (2 * TIME_INTERVAL)) {
                            $largeGroupBackwordImpactSeatCount = $reservationFunction->checkLargeBackwordImpact($dineinCalendarsDetail, $data, $reservation_id);
                        }
                        $totalBackWordSeatCount = $smallGroupBackwordSeatCount + $largeGroupBackwordSeatCount + $requested_seat;
                        $reservationFinal['timeslots'][$timeslotkey]['time'];
                        $totalForWordSeatCount = $smallGroupForwordSeatCount + $largeGroupForwordSeatCount + $requested_seat + $largeGroupBackwordImpactSeatCount;
                        ## if Occupied seat by small, large and Requested seat is greater by Restaurant Allocated seat then decline the reservation ##
                        if ($totalBackWordSeatCount > $restaurantAllocatedSeat) {
                            $reservationFinal['timeslots'][$timeslotkey]['status'] = 0; //time slot issue
                        } elseif ($totalForWordSeatCount > $restaurantAllocatedSeat) {
                            $reservationFinal['timeslots'][$timeslotkey]['status'] = 0;
                        }
                    }
                }//end of foreach
            }
            $response = $reservationFinal;
        } elseif ($type == 'delivery') {
            $orderFinal ['timeslots'] = array();
            $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus($id, $date);
            if ($currentDayDelivery) {
                foreach (StaticOptions::getRestaurantOrderTimeSlots($id, $date) as $t) {
                    if ($t ['status'] == 1) {
                        $slotHour = strtotime($t['slot']);
                        $orderFinal ['timeslots'] [] = $t ['slot'];
                    }
                }
            }
            $response = $orderFinal;
        } elseif ($type == 'takeout') {
            $orderFinal ['timeslots'] = array();
            $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus($id, $date);
            if ($currentDayDelivery) {
                foreach (StaticOptions::getRestaurantOrderTimeSlots($id, $date) as $t) {
                //foreach ($ariaFunction->getAriaTakeoutTimeSlots($id, $date) as $t) {
                    if ($t ['status'] == 1) {
                        $slotHour = strtotime($t['slot']);
                        $orderFinal ['timeslots'] [] = $t ['slot'];
                    }
                }
            }
            $response = $orderFinal;
        } else {
            throw new \Exception('Invalid type provided');
        }
        if (count($response['timeslots']) == 0) {
            $response['timeslots'] = null;
        }
        return $response;
    }

    public function create($data) {
        $date = $data['date'];
        $id = $data['restaurant_id'];

        $orderFinal ['timeslots'] = array();
        foreach (StaticOptions::getRestaurantOrderTimeSlots($id, $date) as $t) {
            if ($t ['status'] == 1) {
                $orderFinal ['timeslots'] [] = $t ['slot'];
            }
        }
        return $orderFinal;
    }

}
