<?php

namespace Ariahk;

use MCommons\StaticOptions;
use Restaurant\Model\Calendar;
use User\Model\UserCard;
use MStripe;
use Restaurant\Model\RestaurantAccounts;

class AriaFunctions {

    public function getAriaReservationTimeSlots($restaurant_id, $date, $input_datetime_format = 'Y-m-d', $output_datetime_format = 'H:i') {
        if ($restaurant_id == '') {
            return array();
        }
        $reservation_slot = array();
        $calendar = new Calendar();
        $inputDate = StaticOptions::getAbsoluteCityDateTime(array(
                    'restaurant_id' => $restaurant_id
                        ), $date, $input_datetime_format);
        $currDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $restaurant_id
        ));
        $slots = $calendar->getOpenCloseSlots($restaurant_id, $inputDate->format('Y-m-d H:i'));
        $slotFromYesterday = $slots['slotFromYesterday'];
        $slotsFromToday = $slots['slotsFromToday'];
        $mergedSlots = array_merge_recursive(array($slotFromYesterday), $slotsFromToday);
        $Restaurantdinein = new \Ariahk\Model\RestaurantDineinCloseDays();
        $close_slot = $Restaurantdinein->getOpenCloseReservationslot($restaurant_id, $inputDate->format('Y-m-d H:i'));
        $finalSlots = array();
        foreach (StaticOptions::$AriatimeSlots as $slot) {
            $slotDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                            ), $inputDate->format('Y-m-d') . " " . $slot, 'Y-m-d H:i');
            foreach ($mergedSlots as $ocSlots) {
                if (!empty($ocSlots)) {
                    if ($slotDateTime >= $ocSlots['open'] && $slotDateTime < $ocSlots['close']) {
                        $finalSlots[] = $slotDateTime;
                    }
                }
            }
        }
        $opentimeSlot = array();
        $slots = [];
        foreach ($finalSlots as $slot) {
            if ($slot->getTimeStamp() >= $currDateTime->getTimeStamp()) {
                $slots[] = $slot->format($output_datetime_format);
                $opentimeSlot[] = array(
                    'slot' => $slot->format($output_datetime_format),
                    'status' => 1
                );
            }
        }
        if (!empty($close_slot)) {
            $finalslot = array();
            $opentimeSlot = array();           
            foreach ($close_slot as $slot_interval) {
                if ($slot_interval['whole_day'] == 1) {
                    return $opentimeSlot;
                } else {
                    $slots = $this->getReservationtimeslot($slots, $finalslot, $slot_interval['close_date'], $slot_interval['close_from'], $slot_interval['close_to'], $restaurant_id);
                }               
            }
            foreach ($slots as $key => $v) {
                $opentimeSlot[] = array(
                    'slot' => $v,
                    'status' => 1
                );
            }
            return $opentimeSlot;
        }
        return $opentimeSlot;
    }

    public function getAriaTakeoutTimeSlots($restaurant_id, $date, $input_datetime_format = 'Y-m-d', $output_datetime_format = 'H:i') {
        if ($restaurant_id == '') {
            return array();
        }
        $inputDate = StaticOptions::getAbsoluteCityDateTime(array(
                    'restaurant_id' => $restaurant_id
                        ), $date, $input_datetime_format);
        $currDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $restaurant_id
        ));
        $currDateTime->add(new \DateInterval('PT30M'));
        $calendar = new Calendar();
        $slots = $calendar->getOrderOpenCloseSlots($restaurant_id, $inputDate->format('Y-m-d H:i')); //$this->getOpenCloseTakoutSlots($restaurant_id, $inputDate->format('Y-m-d H:i'));
        $slotFromYesterday = $slots['slotFromYesterday'];
        $slotsFromToday = $slots['slotsFromToday'];

        $mergedSlots = array_merge_recursive(array($slotFromYesterday), $slotsFromToday);
        $finalSlots = array();
        foreach (StaticOptions::$timeSlots as $slot) {
            $slotDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                            ), $inputDate->format('Y-m-d') . " " . $slot, 'Y-m-d H:i');
            foreach ($mergedSlots as $ocSlots) {
                if (!empty($ocSlots)) {
                    if (isset($ocSlots['day']) && $ocSlots['day'] == 'yesterday') {
                        if ($slotDateTime >= $ocSlots['open'] && $slotDateTime <= $ocSlots['close']) {
                            $finalSlots[] = $slotDateTime;
                        }
                    } else {
                        if ($slotDateTime > $ocSlots['open'] && $slotDateTime <= $ocSlots['close']) {
                            $finalSlots[] = $slotDateTime;
                        }
                    }
                }
            }
        }
        $opentimeSlot = array();
        foreach ($finalSlots as $slot) {
            $sArray = array(
                'slot' => $slot->format('H:i'),
                'status' => 1
            );
            if ($slot <= $currDateTime) {
                $sArray['status'] = 0;
            }
            if ($slot->format('H:i') == '00:00') {
                $sArray['status'] = 0;
            }
            $opentimeSlot[] = $sArray;
        }
        return array_values(array_map('unserialize', array_unique(array_map('serialize', $opentimeSlot))));
    }

    public function getOpenCloseTakoutSlots($restaurant_id, $date) {
        if ($date == "") {
            $date = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                    ))->format('Y-m-d H:i');
        } else {
            $tmpDate = new \DateTime($date);
            $date = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                            ), $tmpDate->format('Y-m-d'), 'Y-m-d')->format('Y-m-d H:i');
        }

        $flippedMapping = array_flip(StaticOptions::$dayMapping);

        // Current Date
        $currDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                    'restaurant_id' => $restaurant_id
                        ), $date, 'Y-m-d H:i');
        $currDateString = $currDateTime->format('Y-m-d');
        $currDay = $currDateTime->format('D');
        $currDayAbbr = $flippedMapping[$currDay];

        // Previous Date
        $prevDateTime = clone($currDateTime);
        $prevDateTime->sub(new \DateInterval('P1D'));
        $prevDateString = $prevDateTime->format('Y-m-d');
        $prevDay = $prevDateTime->format('D');
        $prevDayAbbr = $flippedMapping[$prevDay];
        $restaurantDetails = new \Restaurant\RestaurantDetailsFunctions();
        $restaurantCalendarModel = new \Restaurant\Model\Calendar();
        $options = array(
            'columns' => array(
                'operation_hours' => 'operation_hrs_ft',
                'calendar_day'
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,
                'takeout_open' => 1,
                'open_close_status > ?' => 1,
                'calendar_day' => array($prevDayAbbr, $currDayAbbr)
            )
        );
        $restaurantCalendarModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $restaurantCalendarModel->find($options)->toArray();

        $yesterdayOperatingHours = array_filter($response, function($operatingHours) use ($prevDayAbbr) {
            return $operatingHours['calendar_day'] == $prevDayAbbr;
        });
        if (count($yesterdayOperatingHours)) {
            $yesterdayOperatingHours = array_pop($yesterdayOperatingHours);
        }
        if (isset($yesterdayOperatingHours['operation_hours']) && !empty($yesterdayOperatingHours['operation_hours']) && $yesterdayOperatingHours['operation_hours'] != 'CLOSED') {
            $yesterdayOperatingHours = $yesterdayOperatingHours['operation_hours'];
        } else {
            $yesterdayOperatingHours = '';
        }

        $todayOperatingHours = array_filter($response, function($operatingHours) use ($currDayAbbr) {
            return $operatingHours['calendar_day'] == $currDayAbbr;
        });
        if (count($todayOperatingHours)) {
            $todayOperatingHours = array_pop($todayOperatingHours);
        }
        if (isset($todayOperatingHours['operation_hours']) && !empty($todayOperatingHours['operation_hours']) && $todayOperatingHours['operation_hours'] != 'CLOSED') {
            $todayOperatingHours = $todayOperatingHours['operation_hours'];
        } else {
            $todayOperatingHours = '';
        }
        $yesterdayOpenClose = $restaurantDetails->adjustReserveTimings($yesterdayOperatingHours);
        $todayOpenClose = $restaurantDetails->adjustReserveTimings($todayOperatingHours);

        $slotFromYesterday = array();
        foreach ($yesterdayOpenClose as $openClose) {

            $openDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                            ), $prevDateString . " " . $openClose['open'], 'Y-m-d H:i');

            $closeDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                            ), $prevDateString . " " . $openClose['close'], 'Y-m-d H:i');

            if ($closeDateTime <= $openDateTime) {
                $slotFromYesterday = array(
                    'open' => StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                            ), $currDateString . ' 00:00', 'Y-m-d H:i'),
                    'close' => $closeDateTime->add(new \DateInterval('P1D')),
                    'day' => 'yesterday'
                );
            }
        }

        $slotsFromToday = array();
        $midNightCloseDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                    'restaurant_id' => $restaurant_id
                        ), $currDateString . ' 23:59', 'Y-m-d H:i');
        foreach ($todayOpenClose as $openClose) {
            $openDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                            ), $currDateString . " " . $openClose['open'], 'Y-m-d H:i');
            $closeDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                            ), $currDateString . " " . $openClose['close'], 'Y-m-d H:i');
            if ($closeDateTime <= $openDateTime) {
                $slotForToday = array(
                    'open' => $openDateTime,
                    'close' => $midNightCloseDateTime,
                    'day' => 'today'
                );
            } else {
                $slotForToday = array(
                    'open' => $openDateTime,
                    'close' => $closeDateTime,
                    'day' => 'today'
                );
            }
            $slotsFromToday[] = $slotForToday;
        }
        return array(
            'slotFromYesterday' => $slotFromYesterday,
            'slotsFromToday' => $slotsFromToday
        );
    }

    public function ariaStaffSmsAlert($restshortkey, $manager_phone) {
        $ariaSmsData = array();
        $aria_mob_no = false;
        if ((APPLICATION_ENV == "local") || (APPLICATION_ENV == "qa") || (APPLICATION_ENV == "qc") || (APPLICATION_ENV == "demo") || (APPLICATION_ENV == "staging") || (APPLICATION_ENV == "aria")) {
                $aria_mob_no = array("3474535994", $manager_phone);
        } else {
                $aria_mob_no = array("9178870403", $manager_phone);
        }
        if ($aria_mob_no) {
            foreach ($aria_mob_no as $key => $value) {
                $ariaSmsData['user_mob_no'] = $value;
                $ariaSmsData['message'] = "You've received a new transaction from Munch Ado, check your dashboard for details.";
                StaticOptions::sendSmsClickaTell($ariaSmsData, 0);
            }
        }
    }

    public function reservationSmsAlert($phone) {
        $userSmsData = array();
        $userSmsData['user_mob_no'] = $phone;
        $userSmsData['message'] = "We've received your reservation request. It will not be confirmed until you are contacted by an ARIA staff member.";
        StaticOptions::sendSmsClickaTell($userSmsData, 0);
    }

    public function orderSmsAlert($phone) {
        $userSmsData = array();
        $userSmsData['user_mob_no'] = $phone;
        $userSmsData['message'] = "The ARIA staff is reviewing your order. Once confirmed, you'll receive a receipt by email.";
        StaticOptions::sendSmsClickaTell($userSmsData, 0);
    }

    public function resTimeDiff($restaurant_id, $currenttime, $reqTimeslot) {
        $timezoneformat = StaticOptions::getTimeZoneMapped(array('restaurant_id' => $restaurant_id));
        $currentTime = new \DateTime($currenttime, new \DateTimeZone($timezoneformat));
        $resReqSlot = new \DateTime($reqTimeslot, new \DateTimeZone($timezoneformat));
        $difference = $resReqSlot->diff($currentTime);
        if ($difference->h < 4) {
            return array('error' => 1, 'msg' => 'Invalid Timeslot.');
        }
    }

    public function getDeliveryOperationHours($id) {
        $finalSlots = array();
        $operatioHours = array();
        $inputDate = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $id
                ))->format('Y-m-d H:i');

        $currDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                    'restaurant_id' => $id
                        ), $inputDate, 'Y-m-d H:i');

        $calendar = new Calendar();
        $slots = $calendar->getOrderOpenCloseSlots($id, $inputDate);
        $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus($id, $inputDate);

        if (!empty($slots) && !empty($slots['slotFromYesterday'])) {
            //$finalSlots[] = $slots['slotFromYesterday']['open']->format('g:i A') . ' - ' . $slots['slotFromYesterday']['close']->format('g:i A');
        }
        if (!empty($slots['slotsFromToday'])) {
            foreach ($slots['slotsFromToday'] as $slot) {
                    $finalSlots[] = $slot['open']->format('g:i A') . ' - ' . $slot['close']->format('g:i A');
            }
        }

        if ($currentDayDelivery) {
            $operatioHours['delivery_hours'] = implode(",", array_unique($finalSlots));
        } else {
            $operatioHours['delivery_hours'] = '';
        }
        return $operatioHours;
    }

    public function getTakeoutOperationHours($id) {
        $finalSlots = array();
        $operatioHours = array();
        $inputDate = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $id
                ))->format('Y-m-d H:i');

        $currDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                    'restaurant_id' => $id
                        ), $inputDate, 'Y-m-d H:i');

        $calendar = new Calendar();
        $slots = $calendar->getOrderOpenCloseSlots($id, $inputDate);
        $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus($id, $inputDate);

        if (!empty($slots) && !empty($slots['slotFromYesterday'])) {
            //$finalSlots[] = $slots['slotFromYesterday']['open']->format('g:i A') . ' - ' . $slots['slotFromYesterday']['close']->format('g:i A');
        }
        if (!empty($slots['slotsFromToday'])) {
            foreach ($slots['slotsFromToday'] as $slot) {
                    $finalSlots[] = $slot['open']->format('g:i A') . ' - ' . $slot['close']->format('g:i A');
            }
        }

        if ($currentDayDelivery) {
            $operatioHours['takeout_hours'] = implode(",", array_unique($finalSlots));
        } else {
            $operatioHours['takeout_hours'] = '';
        }
        
        return $operatioHours;
    }

    public function userCityTimeZone($locationData) {
        $stateCode = isset($locationData ['state_code']) ? $locationData ['state_code'] : 'NY';
        $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'state_code' => $stateCode
        ));
        return $cityDateTime->format("Y-m-d H:i:s");
    }

    public function validateCardFromStripe($cardDetails) {

        $cust_id = NULL;
        $card_number = NUll;
        $stripeModel = new MStripe ();
        $useCardModel = new UserCard ();
        $userId = StaticOptions::getUserSession()->getUserId();
        $locationData = StaticOptions::getUserSession()->getUserDetail('selected_location');
        $currentDate = strtotime($this->userCityTimeZone($locationData));
        $currentMonth = date("n", $currentDate);
        $currentYear = date("Y", $currentDate);

        $uDetails = $useCardModel->fetchUserCard($userId);

        $card_number = array();

        if (!empty($uDetails)) {
            foreach ($uDetails as $key => $val) {
                $date = explode('/', $val['expired_on']);
                $cardValidate = 0;
                if ($currentYear < $date[1]) {
                    $cardValidate = 1;
                } elseif ($currentYear == $date[1]) {
                    if ($date[0] >= $currentMonth) {
                        $cardValidate = 1;
                    } else {
                        $cardValidate = 0;
                    }
                } else {
                    $cardValidate = 0;
                }
                if ($cardValidate == 1) {
                    $cust_id = $val['stripe_token_id'];
                    $card_number[] = $val['card_number'];
                }
            }
        }

        //Add to card in strip and get token and card detail	
        $fourDigitofCardNo = substr($cardDetails['number'], -4);
        if (in_array($fourDigitofCardNo, $card_number)) {
            $add_card_response = $stripeModel->addCard($cardDetails, $cust_id);
        } else {
            $cust_id = NULL;
            $add_card_response = $stripeModel->addCard($cardDetails, $cust_id);
        }

        return $add_card_response ['response'];
    }

    public function sendMailsToRestaurant($data) {
        $sender = 'grow@munchado.biz';
        $sendername = "Munch Ado";
        $restshortkey = $data ['variables']['restshrotkey'];
        $manager_email = $data ['variables']['manager_email']; 
        if ((APPLICATION_ENV == "local") || (APPLICATION_ENV == "qa") || (APPLICATION_ENV == "qc") || (APPLICATION_ENV == "demo") || (APPLICATION_ENV == "staging") || (APPLICATION_ENV == "aria")) {
               $recievers = array($manager_email, 'dnaswa@Aydigital.com');
        } else {
                $recievers = array($manager_email, 'tanyahira@gmail.com');
        }
        $template = "email-template/" . $data ['template'];
        $layout = 'email-layout/default_aria';
        $subject = $data['subject'];
        $resquedata = array(
            'sender' => $sender,
            'sendername' => $sendername,
            'variables' => $data ['variables'],
            'receivers' => $recievers,
            'template' => $template,
            'layout' => $layout,
            'subject' => $subject
        );
        StaticOptions::resquePush($resquedata, 'SendEmail');
    }

    public function getReservationtimeslot($slots, $finalslot, $close_date, $close_from, $close_to, $restaurant_id) {
        $timezoneformat = StaticOptions::getTimeZoneMapped(array('restaurant_id' => $restaurant_id));
        $close_from = explode(":",$close_from); //H:i:s
        $close_to = explode(":",$close_to); //H:i:s
        $cls_from ='';
        $cls_to ='';
        if(($close_from[1] > 0) && ($close_from[1] <= 29)){
            $cls_from = $close_from[0].':00:'.$close_from[2];
        }else if(($close_from[1] > 30) && ($close_from[1] <= 59)){
            $cls_from = $close_from[0].':30:'.$close_from[2];
        }else{
            $cls_from = $close_from[0].':'.$close_from[1].':'.$close_from[2]; 
        }
        
        if(($close_to[1] > 0) && ($close_to[1] <= 29)){
            $cls_to = $close_to[0].':00:'.$close_to[2];
        }else if(($close_to[1] > 30) && ($close_to[1] <= 59)){
            $cls_to = $close_to[0].':30:'.$close_to[2];
        }else{
            $cls_to = $close_to[0].':'.$close_to[1].':'.$close_to[2]; 
        }
        
        $fromdate = new \DateTime($close_date . '' . $cls_from, new \DateTimeZone($timezoneformat));
        $todate = new \DateTime($close_date . '' . $cls_to, new \DateTimeZone($timezoneformat));
        $interval = $todate->diff($fromdate);
        if ($interval->invert == 1) {
            $ho = $interval->format('%h') * 60;
            $io = $interval->format('%i');
            $total_minute = $ho + $io;
            $total_slot = $total_minute / 30;
            $fdate = $fromdate->sub(new \DateInterval('PT30M'));
            for ($i = 0; $i <= $total_slot; $i++) {
                $fdate->add(new \DateInterval('PT30M'));
                $finalslot[] = $fdate->format('H:i');
            }
        }
        $slots = array_diff($slots, $finalslot);
        return $slots;
    }
//  Mail Order data for Order -Reservation  
    public function makeAriaOrderForMail($itemDetails, $restaurant_id, $status, $subtotal = false) {
        $order_string = '';
        $price_desc = '';

        $restaurantAccount = new RestaurantAccounts();
        $order_string .= '<td bgcolor="#fff0e1" style="padding:10px 30px;">
                            <p style="margin:0;font-family:arial;font-size:16px;font-weight:bold;padding-bottom:9px;">The Order:</p>
                            <table width="100%" border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-family:arial;font-size:16px;">
                            <tr>
                                <td width="40%" align="left" valign="top" style="font-family:arial;">Items</td>
                                <td align="center" valign="top style="font-family:arial;"">Unit Price</td>
                                <td align="center" valign="top" style="font-family:arial;">Quantity</td>
                                <td align="right" valign="top" style="font-family:arial;">Total</td>
                             </tr>';
        foreach ($itemDetails as $item) {
            //pr($item,1);
            $order_string .= '<tr style="font-size:14px;color:#686868;">
                                <td align="left" valign="top" style="padding-top:10px;font-family:arial;">'.utf8_encode($item['item_name']).'</td>
                                <td align="center" valign="top" style="padding-top:10px;font-family:arial;">$'.number_format($item['unit_price'], 2).'</td>  
                                <td align="center" valign="top" style="padding-top:10px;font-family:arial;">'.$item['quantity'].'</td>
                                <td align="right" valign="top" style="padding-top:10px;font-family:arial;">$'.number_format($item['unit_price'] * $item['quantity'], 2).'</td>
                              </tr>';
            $i=1;
            if (!empty($item ['addons'])) {
                foreach ($item ['addons'] as $addon) {
                    if ($addon ['addon_option'] != 'None') {
                        $freeText = "";
                        $sendMailToRestaurant = $restaurantAccount->checkRestaurantForMail($restaurant_id, 'orderconfirm');
                        if ($addon ['was_free'] == 1 && $status === "ordered") {
                            if ($sendMailToRestaurant == true || $sendMailToRestaurant == 1) {
                                $freeText = " (Included in base price)";
                            }
                        }
                        $order_string .= '<tr style="font-size:11px;color:#686868;">
                             <td align="left" valign="top" style="padding-top:5px;font-family:arial;">'.utf8_encode($addon['addon_option']).'</td>
                             <td align="center" valign="top" style="padding-top:5px;font-family:arial;">$'.number_format($addon['price'],2).'</td>
                             <td align="center" valign="top" style="padding-top:5px;font-family:arial;">'.$item['quantity'].'</td>
                             <td align="right" valign="top" style="padding-top:5px;font-family:arial;">$'.number_format($addon['price']*$item['quantity'],2).'</td>
                          </tr>';
                    }
                }
            }
           if(!empty($item['special_instruction'])){ 
                $order_string .='<tr style="font-size:10px;color:#686868;font-style: italic;">
                    <td align="left" valign="top">'.$item['special_instruction'].'</td>
                    <td></td>
                    <td></td>
                    <td></td>
                 </tr>';
               }
               $i++;  
        }
        $order_string .='</table>';
        return $order_string;
    }
    
    public function ariaFooterData($shortcode){
        if ($shortcode == 'wv') {
            $footer['image_one'] = 'add_westvillage.jpg';
            $footer['alt_one'] = "Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St";
            $footer['image_two'] = 'add_hellkitchen.jpg';
            $footer['alt_two'] = "Aria hell's kitchen 369 W 51st St, New York, NY 10019 b/t  8th Ave & 9th Ave";
        } else {
            $footer['image_one'] = 'add_hellkitchen.jpg';
            $footer['alt_one'] = "Aria hell's kitchen 369 W 51st St, New York, NY 10019 b/t  8th Ave & 9th Ave";
            $footer['image_two'] = 'add_westvillage.jpg';
            $footer['alt_two'] = "Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St";
        }
        return $footer;
    }
     public function restaurantTaged($restaurantId = false) {
        if ($restaurantId) {
            $this->restaurantId = $restaurantId;
        }
        $tags = new \Restaurant\Model\Tags();
        $tagsDetails = $tags->getTagDetailByName("dine-more");
        if (!empty($tagsDetails)) {
            $restaurant = new \Restaurant\Model\Restaurant();
            $joins = array();
            $joins [] = array(
                'name' => array(
                    'rt' => 'restaurant_tags'
                ),
                'on' => 'rt.restaurant_id = restaurants.id',
                'columns' => array(
                    'tag_id',
                ),
                'type' => 'inner'
            );
            return $restaurant->findByRestaurantId(
                            array(
                                'columns' => array('restaurant_name', 'rest_code'),
                                'where' => array('restaurants.id' => $this->restaurantId, 'rt.tag_id' => $tagsDetails[0]['tags_id'], 'rt.status' => 1),
                                'joins' => $joins
                            )
            );
        }
    }
}
