<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Restaurant\Model\RestaurantDineinCalendars;
use User\Model\UserReservation;
use Restaurant\ReservationFunctions;
use Restaurant\Model\RestaurantAccounts;
use Restaurant\Model\Restaurant;

class TimeslotsController extends AbstractRestfulController {

    public function get($id) {
        $timezoneformat = StaticOptions::getTimeZoneMapped(array(
         'restaurant_id' => $id
        ));
        $type = $this->getQueryParams('type','reservation');
        $date = $this->getQueryParams('date',false);
        $requested_seat = $this->getQueryParams('partysize', false);
        $reservation_id = $this->getQueryParams('reservation_id', false);
       
        if (!isset($date) || empty($date)) {
           $date = StaticOptions::getRelativeCityDateTime(array(
                            'restaurant_id' =>$id
                ))->format('Y-m-d');          
        }
       
        $currentDateTime =  StaticOptions::getRelativeCityDateTime(array(
                            'restaurant_id' =>$id
                ))->format('Y-m-d H:i');  
       
        if (!isset($type)) {
            throw new \Exception('Please provide the type');
        }
        if ($requested_seat) {
            $requested_seat = $this->getQueryParams('partysize');
        } else {
            $requested_seat = 2;
        }
        
        $operationHoursController = $this->getServiceLocator()->get("Restaurant\Controller\OperatingHoursController");
        
        ########### CRM OPEN CLOSE TIME with Restaurant & Message ###############
        $crmOpenCloseTime = explode("-", ORDER_TIME_SLOT);
        $selectedLocation = $this->getUserSession()->getUserDetail('selected_location', array());
        $cityId = isset($selectedLocation ['city_id']) ? $selectedLocation ['city_id'] : false; //18848
        if (!$cityId) {
            $restaurantCityId = StaticOptions::getRestaurantCityId($id);
            $cityId = $restaurantCityId['city_id'];
        }
        $cityModel = new \Home\Model\City();
        $cityDetails = $cityModel->cityDetails($cityId);
        $currentCityDateTime = \MCommons\StaticOptions::getRelativeCityDateTime(array(
                    'state_code' => $cityDetails [0] ['state_code']
        ));

        $cityDateTime = $currentCityDateTime->format('Y-m-d H:i:s');
       
        $currentHour = strtotime($cityDateTime);
        $calendarModel = new \Restaurant\Model\Calendar();
        
        $restaurantDetailModel = new Restaurant();
        $restaurantFunctions = new \Restaurant\RestaurantDetailsFunctions ();
        $options = array('where'=>array('id'=>$id));
		$resDetails = $restaurantDetailModel->findRestaurant ( $options );
		$resDetails = $resDetails ->toArray();
		if (! $resDetails) {
			return $this->sendError ( 'Restaurant details not found', 404 );
		}
        #####################################################
//        echo $requested_seat;
//        die;
        ############# Calculate next delivery and takeout time ##################
        if ($resDetails['menu_without_price']==0 || $resDetails['accept_cc_phone']==1) {
                $hasDelivery = intval($resDetails['delivery']);
                $hasTakeout = intval($resDetails['takeout']);
           }else{
                $hasDelivery = intval(0);
                $hasTakeout = intval(0);  
           }
           $isResOpen = $calendarModel->isOpenDeliverForOneHourBefore($id,$hasDelivery);
           $deliveryTimeDiff = $calendarModel->restaurantDeliveryTimeDiff($id,$hasDelivery);
           $takeoutTimeDiff = $calendarModel->restaurantTakeoutTimeDiff($id,$hasTakeout);
           $sevenDate = $this->getSevenDayDateFromCurrentDate($date,$timezoneformat,$type);
           
           $nextDelivery=$this->nextDelivery($id,'order',$sevenDate,$timezoneformat,$deliveryTimeDiff);           
           $nextTakeout=$this->nextDelivery($id,'takeout',$sevenDate,$timezoneformat,$takeoutTimeDiff);
        ##########################################################################
        if ($type == 'reservation') {
            $reservationFinal ['timeslots'] = StaticOptions::getRestaurantReservationTimeSlotsMob($id, $date, $sevenDate);
           
            if($resDetails['reservations']!=1 || empty($reservationFinal['timeslots'])){
                $timeSlots = array('timeslots'=>array(),'operation_hours'=>'');
                $timeSlots['is_asked_date_slot'] = 0;
                $timeSlots['current_date_time'] = $currentDateTime;
                return $timeSlots;
            }          
                       
            $reservationOH = array('date'=>date('Y-m-d',strtotime($reservationFinal ['timeslots'][0]['slot'])),'id'=>$id);
            $operatingHours = $operationHoursController->create($reservationOH);

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
                'status' => '1'
                )
            ));
            if (isset($dineinCalendarsDetail) && !empty($dineinCalendarsDetail) && $restaurantAccountDetails) {
                /* SELECT * FROM `user_reservations` WHERE `restaurant_id` =49404 AND `time_slot` LIKE '%2014-10-09%' LIMIT 0 , 30 */
                $restaurantAllocatedSeat = 0;
                $totalSeatCount = 0;
                $date = date("Y-m-d", strtotime($date));

                foreach ($reservationFinal['timeslots'] as $timeslotkey => $val) {
                    $totalSeatCount = 0;
                    $seatCount = 0;
                    
                    $time123 = explode(' ', $reservationFinal['timeslots'][$timeslotkey]['time']);
                    $requested_time = strtotime($time123[1]);

                   $time_slot = $reservationFinal['timeslots'][$timeslotkey]['time']; //	"2014-10-08 11:00";
                   $time465=date('H:i',strtotime($time_slot));
                   $data = array('reserved_seats' => $requested_seat, 'time' => $time465, 'date' => $date, 'time_slot' => $time_slot, 'restaurant_id' => $id);
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
                            $reservationFinal['timeslots'][$timeslotkey]['status'] = 0;//time slot issue
                        } elseif ($totalForWordSeatCount > $restaurantAllocatedSeat) {
                            $reservationFinal['timeslots'][$timeslotkey]['status'] = 0;
                        }
                    }
                }//end of foreach
               
            } 
            $timeSlots = array('timeslots'=>$reservationFinal['timeslots'],'operation_hours'=>$operatingHours);
            $timeSlots['is_asked_date_slot'] = 0;
            if(strtotime($date)==strtotime(date('Y-m-d',strtotime($reservationFinal ['timeslots'][0]['time'])))){
                $timeSlots['is_asked_date_slot'] = 1;
            }
            $timeSlots['current_date_time'] = $currentDateTime;
            

            return $timeSlots;
        } elseif ($type == 'order') {
            $orderFinal ['timeslots'] = array();
            
            $orderTimeSlot = explode("-", ORDER_TIME_SLOT);
            $crmTimeSlot = explode("-", CRM_OPEN_CLOSE_TIME);
            $operatingHours = array();

            foreach($sevenDate as $key =>$nextDate){

                $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus ( $id,$nextDate);
                if($currentDayDelivery){ 
                
                    foreach (StaticOptions::getRestaurantOrderTimeSlotsMob($id, $nextDate) as $t) {
                        if ($t ['status'] == 1) {
                            $slotHour = strtotime($t['slot']);                     
                             $startOrderTime = strtotime($nextDate.' '.$orderTimeSlot[0].":00");
                             $endOrderTime = strtotime($nextDate.' '.$orderTimeSlot[1]);
                              if(($slotHour >=$startOrderTime) && ($slotHour <= $endOrderTime)){ 
                                  $orderFinal ['timeslots'] [] = $t ['slot'];
                              }
                        }
                      }    

                }
                
                if(count($orderFinal['timeslots'])>0){
                    $oHdata = array('date'=>$nextDate,'id'=>$id);
                    $operatingHours = $operationHoursController->create($oHdata);
                    break;
                }
            }
            // pr($orderFinal,1);

            $timeSlots = array('timeslots'=>$orderFinal['timeslots'],'operation_hours'=>$operatingHours);
                     
           // $opt_hours = explode(" ", $operatingHours['delivery_time']['open_time'][0]);
            $timeSlots['current_date_time'] = $currentDateTime;

            $timeSlots['is_asked_date_slot'] = 0;
            //pr($date);
           // pr(date('Y-m-d',strtotime($timeSlots ['timeslots'][0])));
            
            if(isset($timeSlots ['timeslots'][0]) && strtotime($date)==strtotime(date('Y-m-d',strtotime($timeSlots ['timeslots'][0])))){
                $timeSlots['is_asked_date_slot'] = 1;
            }
            ########### CRM TIME & Message & Restaurant status###############
            $currentHour = strtotime($currentDateTime);
           // pr($currentDateTime);
           // pr(date('Y-m-d',$currentHour)." ".$orderTimeSlot[0]);
           // pr(date('Y-m-d',$currentHour)." ".$orderTimeSlot[1]);
           // pr("strtotime(date('Y-m-d',$currentHour)." ".$crmOpenCloseTime[0]) <= $currentHour ) && ($currentHour < strtotime(date('Y-m-d',$currentHour)." ".$crmOpenCloseTime[1]))");
            $is_currently_deliver = $calendarModel->isOpenDeliverForOneHourBefore($id,$resDetails ['delivery']);
            //$delivery_sec_last = $calendarModel->deliveryPreviousSlotOfRestauratCloseSlot($id,$isResOpen,$resDetails ['delivery']);
            //$delivery_time_diff = $calendarModel->restaurantDeliveryTimeDiff($id,$resDetails ['delivery']);
            //$takeout_time_diff = $calendarModel->restaurantTakeoutTimeDiff($id,$resDetails ['takeout']);
            //$takeout_sec_last = $calendarModel->takeoutPreviousSlotOfRestauratCloseSlot($id,$isResOpen,$resDetails ['takeout']);
            if((strtotime(date('Y-m-d',$currentHour)." ".$crmTimeSlot[0]) <= $currentHour ) && ($currentHour < strtotime(date('Y-m-d',$currentHour)." ".$crmTimeSlot[1]))){
                $timeSlots['crm_open_now']= true;
            }else{
                $timeSlots['crm_open_now']= false;
            }  
               $timeSlots ['is_restaurant_open'] = $is_currently_deliver;//$isResOpen; 
               $timeSlots['crm_message'] ="";
                if($timeSlots['crm_open_now'] == false && $is_currently_deliver == true)
                { $timeSlots['crm_message'] = "Online ordering through Munch Ado ends at 10:30 PM. You can call the restaurant directly ".$resDetails['phone_no']." to place an order now. Or, you can pre-order a meal with us and get an early start on your day."; }
                else if($timeSlots['crm_open_now'] == true && $is_currently_deliver ==false )
                { $timeSlots['crm_message'] = "This restaurant is closed for Delivery and not accepting any more orders right now. It will begin accepting new orders ".$nextDelivery.". You can still pre-order by choosing a different time of Delivery"; }
                else if($timeSlots['crm_open_now'] == false && $is_currently_deliver == false){
                   $timeSlots['crm_message'] = "This restaurant is closed for Delivery and not accepting any more orders right now. It will begin accepting new orders ".$nextDelivery.". You can still pre-order by choosing a different time of Delivery"; 
                }
             
             #####################################################
            return $timeSlots;            
        } elseif ($type == 'takeout') {
            $orderFinal ['timeslots'] = array();
             $orderTimeSlot = explode("-", ORDER_TIME_SLOT);
             $crmTimeSlot = explode("-", CRM_OPEN_CLOSE_TIME);
            foreach($sevenDate as $key =>$nextDate){
                foreach (StaticOptions::getRestaurantTakeoutTimeSlotsMob($id, $nextDate) as $t) {
                    if ($t ['status'] == 1) {
                        $slotHour = strtotime($t['slot']);    
                         $startOrderTime = strtotime($nextDate.' '.$orderTimeSlot[0].":00");
                         $endOrderTime = strtotime($nextDate.' '.$orderTimeSlot[1]);
                        if(($slotHour >=$startOrderTime) && ($slotHour <= $endOrderTime)){ 
                        $orderFinal ['timeslots'] [] = $t ['slot'];
                        }
                    }
                }
                if(count($orderFinal['timeslots'])>0){
                    $oHdata = array('date'=>$nextDate,'id'=>$id);
                    $operatingHours = $operationHoursController->create($oHdata);
                    break;
                }
            }
           $timeSlots =  array('timeslots'=>$orderFinal['timeslots'],'operation_hours'=>$operatingHours);
           if(!empty($orderFinal ['timeslots'])){
            $opt_hours = explode(" ", $orderFinal['timeslots'][0]);
           }
           $timeSlots['current_date_time'] = $currentDateTime;
           $timeSlots['is_asked_date_slot'] = 0;
            
            if(strtotime($date)==strtotime(date('Y-m-d',strtotime($timeSlots ['timeslots'][0])))){
                $timeSlots['is_asked_date_slot'] = 1;
            }
            $currentHour = strtotime($currentDateTime);
             //pr(date('Y-m-d H:i',$currentHour));
            // pr(date('Y-m-d',$currentHour)." ".$orderTimeSlot[0]);
            // pr(date('Y-m-d',$currentHour)." ".$orderTimeSlot[1]);
            // pr(date('Y-m-d H:i'));
             //pr(strtotime(date('Y-m-d',$currentHour)." ".$orderTimeSlot[0]));
            // pr($currentHour);
                if((strtotime(date('Y-m-d',$currentHour)." ".$crmTimeSlot[0]) <= $currentHour ) && ($currentHour < strtotime(date('Y-m-d',$currentHour)." ".$crmTimeSlot[1]))){
                   $timeSlots['crm_open_now']= true;
                }else{
                   $timeSlots['crm_open_now']= false;
                } 
            
           $is_currently_takeout = $calendarModel->isOpenTakeoutForhalfHourBefore($id,$isResOpen,$resDetails ['takeout']);
          
           $timeSlots ['is_restaurant_open'] = $is_currently_takeout;//$isResOpen; 
                $timeSlots['crm_message']="";
                if($timeSlots['crm_open_now'] == false && $is_currently_takeout==true)
                { $timeSlots['crm_message'] = "Online ordering through Munch Ado ends at 10:30 PM. You can call the restaurant directly ".$resDetails['phone_no']." to place an order now. Or, you can pre-order a meal with us and get an early start on your day."; }
                else if($timeSlots['crm_open_now'] == true && $is_currently_takeout==false )
                { $timeSlots['crm_message'] = "This restaurant is closed for Takeout and not accepting any more orders right now. It will begin accepting new orders ".$nextTakeout.". You can still pre-order by choosing a different time of Takeout"; }
                else if($timeSlots['crm_open_now'] == false && $is_currently_takeout==false )
                { $timeSlots['crm_message'] = "This restaurant is closed for Takeout and not accepting any more orders right now. It will begin accepting new orders ".$nextTakeout.". You can still pre-order by choosing a different time of Takeout"; }
                
             #####################################################
          
           
           return $timeSlots;
        } else {
            throw new \Exception('Invalid type provided');
        }
    }
      
    private function nextDelivery($id,$type,$sevenDate,$timezoneformat,$timeDeff=false){
        $i=1;         
        $allWorkingDays = array(); 
        $todayDates = '';         
        $currentDateTime = new \DateTime("now",new \DateTimeZone($timezoneformat));
        $fomatedCurrentDateTime = $currentDateTime->format('Y-m-d H:i:s');
       
        
         foreach($sevenDate as $key =>$sDate){
             
            $timeSlotArray = $this->getFirstOpenTime($type,$id,$sDate);   //6:30  

                 
             if(isset($timeSlotArray['timeslots']) && !empty($timeSlotArray['timeslots'])){
                $xmasDay = new \DateTime($sDate." ".$timeSlotArray['timeslots'][0],new \DateTimeZone($timezoneformat));
                    if($i==1){
                         return $nextDelivery = "Today at ".$xmasDay->format('h:i A'); // Today at 10:00 AM
                     }elseif($i==2){
                         return $nextDelivery = $xmasDay->format('Y-m-d \a\t h:i A');//"Tomorrow at ".$xmasDay->format('h:i A'); // Tomorrow at 10:00 AM   
                     }else{
                         return $nextDelivery = $xmasDay->format('Y-m-d \a\t h:i A'); // Sat 19 at 10:00 AM      
                     }
                
             }
             $i++;
         }
         
    }
    
   
    private function getSevenDayDateFromCurrentDate($currentDate=false,$timezoneformat,$type=false){
        $sevenDateFromCurrent = array();
        if($currentDate){
            $todayDateF  = $xmasDay = new \DateTime($currentDate,new \DateTimeZone($timezoneformat));
            $todayDate=$todayDateF->format('Y-m-d');
            $sevenDateFromCurrent[]=$todayDate;
            $days = 30;
            if($type==="reservation"){
                $days = 60;
            }
            for($i=1;$i<=$days;$i++){
                $xmasDay = new \DateTime($currentDate.'+ '.$i.' day');
                $sevenDateFromCurrent[] = $xmasDay->format('Y-m-d'); // 2010-12-25
            }
        }
        return $sevenDateFromCurrent;
    }
    private function getFirstOpenTime($type,$id,$date,$calculatedDateTime=false){
        if ($type == 'order') {
            $orderFinal ['timeslots'] = array();
            $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus ( $id,$date);
            if($currentDayDelivery){
                $orderTimeSlot = explode("-", ORDER_TIME_SLOT);
                foreach (StaticOptions::getRestaurantOrderTimeSlots($id, $date) as $t) {
                    if (isset($t ['status']) && $t ['status'] == 1) {
                     $slotHour = strtotime($t['slot']);                     
                     $startOrderTime = strtotime($orderTimeSlot[0].":00");
                     $endOrderTime = strtotime($orderTimeSlot[1].":00");
                      if($slotHour >=$startOrderTime && $slotHour < $endOrderTime){ 
                        if($calculatedDateTime){                            
                            $currentDateSlot = strtotime($date." ".$t['slot']);
                            $calculatedDateTimeSec = strtotime($calculatedDateTime);
                                if($currentDateSlot > $calculatedDateTimeSec){
                                    $orderFinal ['timeslots'] [] = $t ['slot'];
                                }
                          }else{
                            $orderFinal ['timeslots'] [] = $t ['slot'];
                        }
                      }
                    }
                }
            }
           
            return $orderFinal;
        } elseif ($type == 'takeout') {
            $orderFinal ['timeslots'] = array();
            $orderTimeSlot = explode("-", ORDER_TIME_SLOT);
            foreach (StaticOptions::getRestaurantTakeoutTimeSlots($id, $date) as $t) {
                if (isset($t ['status']) && $t ['status'] == 1) {
                    $slotHour = strtotime($t['slot']);    
                     $startOrderTime = strtotime($orderTimeSlot[0].":00");
                     $endOrderTime = strtotime($orderTimeSlot[1].":00");
                    if($slotHour >=$startOrderTime && $slotHour < $endOrderTime){  
                        if($calculatedDateTime){
                            $currentDateSlot = strtotime($date." ".$t['slot']);
                            $calculatedDateTimeSec = strtotime($calculatedDateTime);
                            if($currentDateSlot > $calculatedDateTimeSec){
                                $orderFinal ['timeslots'] [] = $t ['slot'];
                            }
                        }else{
                           $orderFinal ['timeslots'] [] = $t ['slot'];
                        }
                    }
                }
            }

            return $orderFinal;
        }
    }
}
