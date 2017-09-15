<?php

namespace Ariahk\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReservation;
use Restaurant\ReservationFunctions;
use Ariahk\AriaFunctions;
use MCommons\StaticOptions;
use Restaurant\Model\Restaurant;
use Restaurant\Model\RestaurantAccounts;
use Restaurant\Model\RestaurantDetail;
use Restaurant\Model\RestaurantDineinCalendars;
use Ariahk\Model\UserReservationCardDetails;
use User\Model\UserNotification;

class AriaReservationController extends AbstractRestfulController {

    public function create($data) {
        
        if (!isset($data ['restaurant_id'])) {
            return array('error'=>1,'msg'=>'Please provide restaurant id');
        }
        if (!isset($data ['reserved_seats'])) {
            return array('error'=>1,'msg'=>'Please provide party size');
        }
        if (!isset($data ['time_slot'])) {
            return array('error'=>1,'msg'=>'Please provide timeslot');
        }
        if (!isset($data ['first_name'])) {
            return array('error'=>1,'msg'=>'First name can not be empty');
        }
        if (!isset($data ['email'])) {
            return array('error'=>1,'msg'=>'Email can not be empty');
        }
        if (!isset($data ['phone'])) {
            return array('error'=>1,'msg'=>'Phone can not be empty');
        }
        if (!isset($data ['restaurant_name'])) {
            return array('error'=>1,'msg'=>'Restaurant name can not be empty');
        }
        if (!isset($data ['card_no'])) {
            throw new \Exception('Card details not sent');
        }
        if (!isset($data ['expiry_month'])) {
            throw new \Exception('Expiry month not sent');
        }
        if (!isset($data ['billing_zip'])) {
            throw new \Exception('Billing zip not sent');
        }
        if (!isset($data ['expiry_year'])) {
            throw new \Exception('Expiry year not sent');
        }
        if (!isset($data ['name_on_card'])) {
            throw new \Exception('Name on card not sent');
        }
        if (!isset($data ['cvc'])) {
            throw new \Exception('CVC not sent');
        }
        $restaurantDetail = new RestaurantDetail ();
        $restaurantModel = new Restaurant ();
        $reservationcarddetails = new UserReservationCardDetails();
        $orderFunctions = new \Restaurant\OrderFunctions();
        $reservationFunctions = new ReservationFunctions ();
        $userReservationModel = new UserReservation ();
        $restaurantAccount = new RestaurantAccounts ();
        $restaurantDineinCalendars = new RestaurantDineinCalendars();
        $ariaFunction = new AriaFunctions ();
        $userNotificationModel = new UserNotification ();
        $selectedLocation = $this->getUserSession()->getUserDetail('selected_location', array());
        $cityId = isset($selectedLocation ['city_id']) ? $selectedLocation ['city_id'] : 18848;
        $stateCode = isset($selectedLocation ['state_code']) ? $selectedLocation ['state_code'] : 'NY';
        $reservedOn = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $data ['restaurant_id'],
                    'state_code' => $stateCode
                ))->format(StaticOptions::MYSQL_DATE_FORMAT);
        $resTimeDiff = $ariaFunction->resTimeDiff($data ['restaurant_id'], $reservedOn,$data ['time_slot']);
        if(strtotime($reservedOn) > strtotime($data ['time_slot'])){
            return array('error'=>1,'msg'=>'Reservation time has been expired! Try again');
        }
        ############ Get user IP Address ##############           
        $ipAddress = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        ###############################################
        $options = array("where" => array("restaurant_id" => $data['restaurant_id']));
        $restaurantAccountDetails = $restaurantAccount->getRestaurantAccountDetail(array('where' => array(
                'restaurant_id' => $data ['restaurant_id'],
                'status' => '1'
            )
        ));
        $dineinCalendarsDetail = $restaurantDineinCalendars->findRestaurantDineinDetail($options);
        if (isset($dineinCalendarsDetail) && !empty($dineinCalendarsDetail) && $restaurantAccountDetails) {
            $requested_time = strtotime($data['time']);
            $requested_seat = $data ['reserved_seats'];
            $restaurantAllocatedSeat = 0;
            $totalSeatCount = 0;
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
                return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
            }

            ## Get Occupied Seat ##
            $getExistingReservation = array('restaurant_id' => $data['restaurant_id'], 'time_slot' => $data['time_slot']);
            $existingReservation = $userReservationModel->getUserReservationToCheckSeat($getExistingReservation);
           
            $seatCount = 0;
            foreach ($existingReservation as $key => $val) {
                $seatCount = $seatCount + $val['reserved_seats'];
            }
            $totalSeatCount = $seatCount + $requested_seat;

            ## If Occupied Seat is greater or equal to Restaurant allocated seat then decline the reservation HERE ##
            if ($totalSeatCount > $restaurantAllocatedSeat) {
                return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
            } else {

                //calculate total seat occupied by small group
                $smallGroupBackwordSeatCount = 0;
                $smallGroupForwordSeatCount = 0;
                $largeGroupBackwordSeatCount = 0;
                $largeGroupForwordSeatCount = 0;
                $largeGroupBackwordImpactSeatCount = 0;
                if ($dineinCalendarsDetail['dinningtime_small'] > TIME_INTERVAL) {
                    $smallGroupBackwordSeatCount = $this->checkSmallBackword($dineinCalendarsDetail, $data);
                    $smallGroupForwordSeatCount = $this->checkSmallForword($dineinCalendarsDetail, $data);
                }

                //calculate total seat occupied by large group
                if ($dineinCalendarsDetail['dinningtime_large'] > TIME_INTERVAL) {
                    $largeGroupBackwordSeatCount = $this->checkLargeBackword($dineinCalendarsDetail, $data);
                    $largeGroupForwordSeatCount = $this->checkLargeForword($dineinCalendarsDetail, $data);
                }

                //caclculate carry forward reservation on future time slots
                if ($dineinCalendarsDetail['dinningtime_large'] > (2 * TIME_INTERVAL)) {
                    $largeGroupBackwordImpactSeatCount = $this->checkLargeBackwordImpact($dineinCalendarsDetail, $data);
                }

                $totalBackWordSeatCount = $smallGroupBackwordSeatCount + $largeGroupBackwordSeatCount + $requested_seat;
                $totalForWordSeatCount = $smallGroupForwordSeatCount + $largeGroupForwordSeatCount + $requested_seat + $largeGroupBackwordImpactSeatCount;
                ## if Occupied seat by small, large and Requested seat is greater by Restaurant Allocated seat then decline the reservation ##
                if ($totalBackWordSeatCount > $restaurantAllocatedSeat) {
                    return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
                } elseif ($totalForWordSeatCount > $restaurantAllocatedSeat) {
                    return array('error' => 1, 'msg' => 'Your reservation request is declined. Not enough seats are available to fulfill your request at the requested time slot. Please try another slot.');
                }
            }
        }
        $cardDetails = array(
                        'number' => $data ['card_no'],
                        'exp_month' => $data ['expiry_month'],
                        'exp_year' => $data ['expiry_year'],
                        'name' => $data ['name_on_card'],
                        'cvc' => $data ['cvc'],
                        'address_zip' => $data['billing_zip'],
                        );
            try {
                $validate_card = $ariaFunction->validateCardFromStripe($cardDetails);
            } catch (\Exception $e) {
                throw new \Exception("We could not charge your credit card, please ensure all fields are entered correctly.", 400);
            }
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $token = $data ['token'];
        $userReservationModel->city_id = $cityId;
        $userReservationModel->order_id = isset($data['order_id']) ? $data['order_id'] : NULL;
        $userReservationModel->restaurant_id = $data ['restaurant_id'];
        $userReservationModel->time_slot = trim($data ['time_slot']);
        $userReservationModel->party_size = $data ['reserved_seats'];
        $userReservationModel->reserved_seats = $data ['reserved_seats'];
        $userReservationModel->user_instruction = isset($data ['user_instruction']) ? $data ['user_instruction'] : '';
        $userReservationModel->first_name = $data ['first_name'];
        $userReservationModel->last_name = isset($data ['last_name']) ? $data ['last_name'] : '';
        $userReservationModel->phone = $data ['phone'];
        $userReservationModel->email = $data ['email'];
        $userReservationModel->restaurant_name = $data ['restaurant_name'];
        $userReservationModel->receipt_no = $reservationFunctions->generateReservationReceipt();
        $userReservationModel->reserved_on = $reservedOn;
        $userReservationModel->user_ip = $ipAddress;
        $userReservationModel->status = 1;
        $userReservationModel->host_name = 'aria';
        $reservation = $userReservationModel->reserveTable();
        $reservationcarddetails->reservation_id = $reservation['id'];
        $reservationcarddetails->card_number = substr($data ['card_no'], - 4);
        $reservationcarddetails->encrypt_card_number  = $orderFunctions->aesEncrypt($data ['card_no'] . "-" . $data ['cvc']);
        $reservationcarddetails->name_on_card = $data['name_on_card'];
        $reservationcarddetails->card_type = isset($data ['card_type']) ? $data ['card_type'] : 'cc';
        $reservationcarddetails->expired_on = $data ['expiry_month'] . '/' . $data['expiry_year'];
        $reservationcarddetails->billing_zip = $data['billing_zip'];
        $reservationcarddetails->created_on = $reservedOn;
        $reservationcarddetails->stripe_card_id = $validate_card['id'];
        $reservationcarddetails->stripe_cus_id = $validate_card['customer'];
        $resCardDetails = $reservationcarddetails->reserveTableCardDetails();
        $options = array("where" => array('id' => $data ['restaurant_id']));
        $restaurantDetail = $restaurantModel->findRestaurant($options);
        $manager_phone = ($restaurantDetail->phone_no2)? $restaurantDetail ->phone_no2:'';
        $manager_email = ($restaurantAccountDetails['memail'])? $restaurantAccountDetails['memail']:'';
        //send sms to User
        $ariaFunction->reservationSmsAlert($data['phone']);
        //send sms to Aria Managers
        $ariaFunction->ariaStaffSmsAlert($data['restshortkey'],$manager_phone);
        //get footer data like image, alt etc
        $footerData = $ariaFunction->ariaFooterData($data['restshortkey']);
        //send pubnub alert to ARIA 
            $notificationMsg = "You have a new reservation! Make some space.";
            $channel = "dashboard_" . $data ['restaurant_id'];
            $notificationArray = array(
                "msg" => $notificationMsg,
                "channel" => $channel,
                "type" => 'reservation',
                "userId" => '999999',
                "restaurantId" => $data ['restaurant_id'],
                'curDate' => StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $data ['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT),
                'restaurant_name' => ucfirst($data['restaurant_name']),
                'reservation_id' => $reservation ['id']
            );
            $notificationJsonArray = array('user_id' => '999999', 'reservation_id' => $reservation ['id'], 'restaurant_id' => $data ['restaurant_id'], 'restaurant_name' => ucfirst($data['restaurant_name']));
            $response = $userNotificationModel->createPubNubNotification($notificationArray, $notificationJsonArray);
            $pubnub = StaticOptions::pubnubPushNotification($notificationArray);
            
        $sendAlertMailToManagerArray = array(
                    'variables' => array(
                        'ownername' => ucfirst($data ['first_name']),
                        'restshrotkey'=>$data['restshortkey'],
                        'restaurant_name' => $data['restaurant_name'],
                        'facebook_url' => $restaurantDetail->facebook_url,
                        'instagram_url' => $restaurantDetail->instagram_url,
                        'reservationDate' => StaticOptions::getFormattedDateTime($userReservationModel->time_slot, 'Y-m-d H:i', 'D, M d, Y'),
                        'reservationtime' => StaticOptions::getFormattedDateTime($userReservationModel->time_slot, 'Y-m-d H:i', 'h:i A'),
                        'receipt_no' => $userReservationModel->receipt_no,
                        'reserved_seats' => $data ['reserved_seats'],
                        'manager_email' => $manager_email,
                        'image1' =>$footerData['image_one'],
                        'alt1' =>$footerData['alt_one'],
                        'image2' =>$footerData['image_two'],
                        'alt2' =>$footerData['alt_two'],
                        
                    ),
            'template' => 'email_manager',
            'subject' => sprintf("New %s Reservation!", $data['restaurant_name']),
                );
         $ariaFunction->sendMailsToRestaurant($sendAlertMailToManagerArray);    
        $instruction = "";
        if (!empty($data ['user_instruction'])) {
            $instruction = str_replace('||', '<br>', $data ['user_instruction']);
        }
        return array(
            'reservation_id' => $reservation ['id'],
            'receipt_no' => $userReservationModel->receipt_no,
            'reserved_on' => $userReservationModel->reserved_on,
            'reservation_status' => $userReservationModel->status,
        );
    }
    
    private function checkSmallBackword($dineinCalendarsDetail, $data) {
        $smallGroupSeatCount = 0;
        $smallGroupBackwordSeatCount = 0;
        $timeSlotToCheckFromArray = array();
        $userReservationModel = new UserReservation();
        $noOfSlot = ($dineinCalendarsDetail['dinningtime_small'] - TIME_INTERVAL) / TIME_INTERVAL;

        if (is_float($noOfSlot)) {

            $floatNumber = explode(".", $noOfSlot);
            $noOfSlot = $floatNumber[0] + 1;
        }
        $calculateHourToBack = ($noOfSlot * TIME_INTERVAL) / 60;
        if (is_float($calculateHourToBack)) {
            $cHBArray = explode(".", $calculateHourToBack);
            if ($cHBArray[1] <= TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60);

            if ($cHBArray[1] > TIME_INTERVAL)
                $calculateHourToBackMinute = ($calculateHourToBack * 60) + 60; //60 is minute
        }else {
            $calculateHourToBackMinute = $calculateHourToBack * 60; //60 is minute
        }
 
        $requestedTimeArray = explode(":", $data['time']);
        $requestedTimeInMinute = ($requestedTimeArray[0] * 60) + $requestedTimeArray[1];
        $timeSlotToCheckFrom = ($requestedTimeInMinute - $calculateHourToBackMinute) / 60; //60 is minute
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
            "type" => 'backword',
            "time_slot" => $data['time_slot'],
            "groupType" => "small",
            "smallGroupValue" => SMALL_GROUP_VALUE
        );

        $existingSmallGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getSmallGroupReservation);

        if (count($existingSmallGroupReservation) > 0) {
            foreach ($existingSmallGroupReservation as $key => $val) {
                $smallGroupBackwordSeatCount += $val['reserved_seats'];
            }
        }

        return $smallGroupBackwordSeatCount;
    }

    private function checkSmallForword($dineinCalendarsDetail, $data) {
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
            "smallGroupValue" => SMALL_GROUP_VALUE
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

    private function checkLargeBackword($dineinCalendarsDetail, $data) {
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
            "smallGroupValue" => SMALL_GROUP_VALUE
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
    private function checkLargeBackwordImpact($dineinCalendarsDetail, $data) {
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
            "smallGroupValue" => SMALL_GROUP_VALUE
        );

        $existingLargeGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getLargeGroupReservation);
        if (count($existingLargeGroupReservation) > 0) {
            foreach ($existingLargeGroupReservation as $key => $val) {
                $largeGroupBackwordSeatCount += $val['reserved_seats'];
            }
        }

        return $largeGroupBackwordSeatCount;
    }

    private function checkLargeForword($dineinCalendarsDetail, $data) {
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
            "smallGroupValue" => SMALL_GROUP_VALUE
        );

        $existingLargeGroupReservation = $userReservationModel->getUserReservationToCheckSeat($getLargeGroupReservation);
        if (count($existingLargeGroupReservation) > 0) {
            foreach ($existingLargeGroupReservation as $key => $val) {
                $largeGroupForwordSeatCount += $val['reserved_seats'];
            }
        }

        return $largeGroupForwordSeatCount;
    }
    
}
