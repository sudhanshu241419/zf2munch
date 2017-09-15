<?php

namespace Restaurant;

use User\Model\User;
use User\Model\UserReservation;

class ReservationFunctions {

    public function generateReservationReceipt() {
        $timestamp = date('mdhis');
        $keys = rand(0, 9);
        $randString = 'M' . $timestamp . $keys;
        return $randString;
    }

    public function checkSmallBackword($dineinCalendarsDetail, $data, $reservation_id = false) {
        $smallGroupSeatCount = 0;
        $smallGroupBackwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        $userReservationModel = new UserReservation();
        $noOfSlot = ($dineinCalendarsDetail['dinningtime_small'] - TIME_INTERVAL) / TIME_INTERVAL;

        if (is_float($noOfSlot)) {

            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }
        // print_r($noOfSlot);
        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60;
        //print_r($calculateHourToBack);
        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);
            // print_r($cHBArray);
            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60);

            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }
        //print_r($calculateHourToBackMinute); die();
        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute - $calculateHourToBackMinute) / 60; //60 is minute
        //  if(!is_int($timeSlotToCheckFrom)){
        if (strpos($timeSlotToCheckFrom, '.') !== false) {

            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            ///print_r($timeSlotToCheckFromArray); die();
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }

        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;


        $getSmallGroupReservation = array(
            "restaurant_id" => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'backword',
            "time_slot" => $data['time_slot'],
            "groupType" => "small",
            "smallGroupValue" => SMALL_GROUP_VALUE,
            "reservation_id" => $reservation_id
        );
        $existingSmallGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getSmallGroupReservation);

        if (count($existingSmallGroupReservation) > 0) {
            foreach ($existingSmallGroupReservation as $key => $val) {
                $smallGroupBackwordSeatCount += $val['reserved_seats'];
            }
        }

        return $smallGroupBackwordSeatCount;
    }

    public function checkSmallForword($dineinCalendarsDetail, $data, $reservation_id = false) {
        $userReservationModel = new UserReservation();
        $smallGroupSeatCount = 0;
        $smallGroupForwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        $noOfSlot = ($dineinCalendarsDetail['dinningtime_small'] - TIME_INTERVAL) / TIME_INTERVAL;

        if (is_float($noOfSlot)) {

            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }

        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60;

        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);

            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60); //+TIME_INTERVAL;

            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }

        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute + $calculateHourToBackMinute) / 60; //60 is minute
        // print_r($timeSlotToCheckFrom);
        /// print_r($timeSlotToCheckFromArray); die();
        //if(is_float($timeSlotToCheckFrom)){
        if (strpos($timeSlotToCheckFrom, '.') !== false) {
            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }

        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;


        $getSmallGroupReservation = array(
            "restaurant_id" => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'forword',
            "time_slot" => $data['time_slot'],
            "groupType" => "small",
            "smallGroupValue" => SMALL_GROUP_VALUE,
            "reservation_id" => $reservation_id
        );
        $existingSmallGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getSmallGroupReservation);
        // echo "checkSmallForword"; print_r($existingSmallGroupReservation); die();
        if (count($existingSmallGroupReservation) > 0) {
            foreach ($existingSmallGroupReservation as $key => $val) {
                $smallGroupForwordSeatCount +=$val['reserved_seats'];
            }
        }
        return $smallGroupForwordSeatCount;
    }

    public function checkLargeBackword($dineinCalendarsDetail, $data, $reservation_id = false) {
        $userReservationModel = new UserReservation();
        $largeGroupSeatCount = 0;
        $largeGroupBackwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        $noOfSlot = ($dineinCalendarsDetail['dinningtime_large'] - TIME_INTERVAL) / TIME_INTERVAL;
        if (is_float($noOfSlot)) {
            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }
        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60; //60 is minute
        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);
            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60); //+TIME_INTERVAL;
            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }


        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute - $calculateHourToBackMinute) / 60;
        if (strpos($timeSlotToCheckFrom, '.') !== false) {
            // if(is_float($timeSlotToCheckFrom)){
            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }


        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;

        $getLargeGroupReservation = array(
            'restaurant_id' => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'backword',
            "time_slot" => $data['time_slot'],
            "groupType" => "large",
            "smallGroupValue" => SMALL_GROUP_VALUE,
            "reservation_id" => $reservation_id
        );
        $existingLargeGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getLargeGroupReservation);
        // echo "checkLargeBackword"; print_r($existingLargeGroupReservation); die();
        if (count($existingLargeGroupReservation) > 0) {
            foreach ($existingLargeGroupReservation as $key => $val) {
                $largeGroupBackwordSeatCount += $val['reserved_seats'];
            }
        }

        return $largeGroupBackwordSeatCount;
    }

    //this function checks the impact of backward reservation slots on future slots
    public function checkLargeBackwordImpact($dineinCalendarsDetail, $data, $reservation_id = false) {
        $userReservationModel = new UserReservation();
        $largeGroupSeatCount = 0;
        $largeGroupBackwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        $noOfSlot = $dineinCalendarsDetail['dinningtime_large'] / TIME_INTERVAL;
        if (is_float($noOfSlot)) {
            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }
        $noOfSlot = $noOfSlot - 2;

        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60; //60 is minute
        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);
            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60); //+TIME_INTERVAL;
            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }

        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute - $calculateHourToBackMinute) / 60;
        if (strpos($timeSlotToCheckFrom, '.') !== false) {
            //if(is_float($timeSlotToCheckFrom)){
            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }


        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;

        $timeSlotToCheckUpto = ($requestedTimeInMinute - TIME_INTERVAL) / 60; //adding time interval in time slot for going to next time slot
        if (is_float($timeSlotToCheckUpto)) {
            $timeSlotToCheckUptoArray = explode(".", $timeSlotToCheckUpto);
            if ($timeSlotToCheckUptoArray[1] > 5) {
                $$timeSlotToCheckUpto = ($timeSlotToCheckUptoArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckUptoArray[1] <= 5) {
                $timeSlotToCheckUpto = $timeSlotToCheckUptoArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckUpto = $timeSlotToCheckUpto . ":00";
        }

        $timeSlotToCheckUpto = $data ['date'] . " " . $timeSlotToCheckUpto;

        $getLargeGroupReservation = array(
            'restaurant_id' => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'backword',
            "time_slot" => $timeSlotToCheckUpto,
            "groupType" => "large",
            "smallGroupValue" => SMALL_GROUP_VALUE,
            "reservation_id" => $reservation_id
        );

        $existingLargeGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getLargeGroupReservation);
        //print_r($existingLargeGroupReservation); die();
        if (count($existingLargeGroupReservation) > 0) {
            foreach ($existingLargeGroupReservation as $key => $val) {
                $largeGroupBackwordSeatCount += $val['reserved_seats'];
            }
        }

        return $largeGroupBackwordSeatCount;
    }

    public function checkLargeForword($dineinCalendarsDetail, $data, $reservation_id = false) {
        $userReservationModel = new UserReservation();
        $largeGroupSeatCount = 0;
        $largeGroupForwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        if ($data['reserved_seats'] > SMALL_GROUP_VALUE) {
            $noOfSlot = ($dineinCalendarsDetail['dinningtime_large'] - TIME_INTERVAL) / TIME_INTERVAL;
        } else {
            $noOfSlot = ($dineinCalendarsDetail['dinningtime_small'] - TIME_INTERVAL) / TIME_INTERVAL;
        }

        if (is_float($noOfSlot)) {
            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }
        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60; //60 is minute
        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);
            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60); //+TIME_INTERVAL;
            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }


        $requestedTimeArray = explode(":", $data['time']);

        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];

        $timeSlotToCheckFrom = ($requestedTimeInMinute + $calculateHourToBackMinute) / 60;
        //if(is_float($timeSlotToCheckFrom)){
        if (strpos($timeSlotToCheckFrom, '.') !== false) {
            $timeSlotToCheckFromArray = explode(".", $timeSlotToCheckFrom);
            if ($timeSlotToCheckFromArray[1] > 5) {
                $timeSlotToCheckFrom = ($timeSlotToCheckFromArray[0] + 1) . ":00";
            } elseif ($timeSlotToCheckFromArray[1] <= 5) {
                $timeSlotToCheckFrom = $timeSlotToCheckFromArray[0] . ":" . TIME_INTERVAL;
            }
        } else {
            $timeSlotToCheckFrom = $timeSlotToCheckFrom . ":00";
        }


        $timeSlotToCheckFrom = $data ['date'] . " " . $timeSlotToCheckFrom;

        $getLargeGroupReservation = array(
            'restaurant_id' => $data['restaurant_id'],
            "checkfrom" => $timeSlotToCheckFrom,
            "type" => 'forword',
            "time_slot" => $data['time_slot'],
            "groupType" => "large",
            "smallGroupValue" => SMALL_GROUP_VALUE,
            "reservation_id" => $reservation_id
        );

        $existingLargeGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getLargeGroupReservation);
        ///echo "checkLargeForword"; print_r($existingLargeGroupReservation); die();
        if (count($existingLargeGroupReservation) > 0) {
            foreach ($existingLargeGroupReservation as $key => $val) {
                $largeGroupForwordSeatCount += $val['reserved_seats'];
            }
        }

        return $largeGroupForwordSeatCount;
    }
    
}
