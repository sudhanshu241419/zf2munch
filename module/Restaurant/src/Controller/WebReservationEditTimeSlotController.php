<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Restaurant\ReservationFunctions;
use User\Model\UserReservation;
use Restaurant\Model\RestaurantDineinCalendars;
use Restaurant\Model\RestaurantAccounts;

class WebReservationEditTimeSlotController extends AbstractRestfulController {

    public function get($id) {
        $type = $this->getQueryParams('type');
        $date = $this->getQueryParams('date');
        $requested_seat = $this->getQueryParams('partysize', false);
        $reservation_id = $this->getQueryParams('reservationid', false);
        if (!isset($date)) {
            throw new \Exception('Please provide the date');
        }
        if (!isset($type)) {
            throw new \Exception('Please provide the type');
        }

        if ($type == 'reservation') {


            $reservationFinal ['timeslots'] = StaticOptions::getRestaurantReservationTimeSlots($id, $date);
            foreach ($reservationFinal ['timeslots'] as &$final) {
                $final ['time'] = $final ['slot'];
                unset($final ['slot']);
            }
            $reservationFunction = new ReservationFunctions();
            $userReservationModel = new UserReservation();
            $restaurantDineinCalendars = new RestaurantDineinCalendars();
            $restaurantAccount = new RestaurantAccounts ();
            $options = array("where" => array("restaurant_id" => $id));
            $restaurantAccountDetails = $restaurantAccount->getRestaurantAccountDetail(array('where' => array(
                'restaurant_id' => $id,
                'status' => '1'
                )
            ));
            $dineinCalendarsDetail = $restaurantDineinCalendars->findRestaurantDineinDetail($options);
            
            //get current reservation detail that user want to modify

            if ($reservation_id) {
                $options = array("where" => array('id' => $reservation_id));
                $pre_reservation = $userReservationModel->getUserReservation($options);
                $previeousReservedSeat = $pre_reservation[0]['reserved_seats'];
                $previeousTimeSlot = strtotime($pre_reservation[0]['time_slot']); //2014-10-20 11:00:00
            }
           
            if (isset($dineinCalendarsDetail) && !empty($dineinCalendarsDetail) && $restaurantAccountDetails) {
                /* SELECT * FROM `user_reservations` WHERE `restaurant_id` =49404 AND `time_slot` LIKE '%2014-10-09%' LIMIT 0 , 30 */
                $restaurantAllocatedSeat = 0;
                $totalSeatCount = 0;
                $date = date("Y-m-d", strtotime($date));
                //echo "<pre>";	
                //print_R($reservationFinal);		
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
                    } else{
                        $restaurantAllocatedSeat = $dineinCalendarsDetail['dinner_seats'];
                    }
                    
                    
//                    elseif (($dst < $det) && $dst <= $requested_time && $requested_time <= $det) {
//                        $restaurantAllocatedSeat = $dineinCalendarsDetail['dinner_seats'];
//                    } elseif ($dst > $det) {
//                        $det += 86400;
//                        if ($dst <= $requested_time && $requested_time <= $det) {
//                            $restaurantAllocatedSeat = $dineinCalendarsDetail['dinner_seats'];
//                        }
//                    }
                    
                    
                    
//                    elseif (strtotime($dineinCalendarsDetail['dinner_start_time']) <= $requested_time && $requested_time <= strtotime($dineinCalendarsDetail['dinner_end_time'])) {
//                        $restaurantAllocatedSeat = $dineinCalendarsDetail['dinner_seats'];
//                    }

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
                        if (strtotime($time_slot) == $previeousTimeSlot) {
                            $totalSeatCount = ($seatCount + $requested_seat) - $previeousReservedSeat;
                        } else {
                            $totalSeatCount = $seatCount + $requested_seat;
                        }
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
                        //echo $totalBackWordSeatCount."=>".$totalForWordSeatCount."=>Requested Time".$reservationFinal['timeslots'][$timeslotkey]['time'];
                        //echo "\n";
                        if ($totalBackWordSeatCount > $restaurantAllocatedSeat) {
                            $reservationFinal['timeslots'][$timeslotkey]['status'] = 0;
                        } elseif ($totalForWordSeatCount > $restaurantAllocatedSeat) {
                            $reservationFinal['timeslots'][$timeslotkey]['status'] = 0;
                        }
                    }
                }//end of foreach
            }//end of dineinCalandarDetail			
           
            return $reservationFinal;
        } elseif ($type == 'order') {
            $orderFinal ['timeslots'] = array();
            foreach (StaticOptions::getRestaurantOrderTimeSlots($id, $date) as $t) {
                if ($t ['status'] == 1) {
                    $orderFinal ['timeslots'] [] = $t ['slot'];
                }
            }

            return $orderFinal;
        } elseif ($type == 'takeout') {
            $orderFinal ['timeslots'] = array();
            foreach (StaticOptions::getRestaurantTakeoutTimeSlots($id, $date) as $t) {
                if ($t ['status'] == 1) {
                    $orderFinal ['timeslots'] [] = $t ['slot'];
                }
            }

            return $orderFinal;
        } else {
            throw new \Exception('Invalid type provided');
        }
    }

}
