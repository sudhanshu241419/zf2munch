<?php

namespace Restaurant;

use Restaurant\Crm;
use \Search\DateTimeUtils;
use Restaurant\Model\Calendar;

/**
 * This class has utility functions which can be used to get various date time 
 * related values for a given restaurant.
 *
 * @author dhirendra
 */
class RestaurantDateTimeUtil {
    /**
     * restaurant id
     * @var int 
     */
    private $res_id;
    
    /**
     * restaurant's city's current time between 0-2359
     * @var int 
     */
    private $current_time;
    
    /**
     * restaurant's city's current day in 2 char format
     * @var string 
     */
    private $current_day;
    
    /**
     * restaurant's city's current date in 'Y-m-d' format
     * @var string 
     */
    private $current_date;
    
    /**
     * restaurant's city's timezone
     * @var string 
     */
    private $current_timezone = '';
    
    /**
     *
     * @var Crm
     */
    private $crm;
    
    /**
     *
     * @var array  
     */
    private $today_delivery_slots_without_cap;
    
    /**
     *
     * @var array  
     */
    private $today_delivery_slots_with_cap;
    
    /**
     *
     * @var array  
     */
    private $today_takeout_slots_without_cap;
    
    /**
     *
     * @var array  
     */
    private $today_takeout_slots_with_cap;
    
    /**
     * restaurant's weekly calendar
     * @var array with key 2 char day name and value as day's calendar 
     */
    private $res_cal;

    /**
     * 
     * @param int $res_id
     * @param array $cityDateTime optional usefull for debugging. Use DateTimeUtils::getCityDayDateAndTime24F
     * @throws \Exception
     */
    public function __construct($res_id, $cityDateTime = []) {
        $this->res_id = intval($res_id);
        
        //set city's datetime info
        if (!empty($cityDateTime)) {
            if( !isset($cityDateTime['time']) || !isset($cityDateTime['day']) || !isset($cityDateTime['date']) || !isset($cityDateTime['timezone'])){
                throw new Exception('Invalid call to RestaurantDateTimeUtil');
            }
            $this->current_time = $cityDateTime['time'];
            $this->current_day = $cityDateTime['day'];
            $this->current_date = $cityDateTime['date'];
            $this->current_timezone = $cityDateTime['timezone'];
        } else {
            $resModel = new Model\Restaurant();
            $resData = $resModel->find(array('where' => array('id' => $res_id)))->toArray();
            if (empty($resData)) {
                throw new \Exception('Invalid restaurant id');
            }
            $cityDateTime = DateTimeUtils::getCityDayDateAndTime24F(intval($resData[0]['city_id']));
            $this->current_time = $cityDateTime['time'];
            $this->current_day = $cityDateTime['day'];
            $this->current_date = $cityDateTime['date'];
            $this->current_timezone = $cityDateTime['timezone'];
        }

        $this->crm = new Crm($this->current_timezone);
        $this->setResWeeklyCalendar();
        $this->setDeliveryTakeoutSlots();
    }
    
    private function setResWeeklyCalendar(){
        $calendar = new Calendar();
        $rawTimeSlots = $calendar->getResWeekCalData($this->res_id);
        foreach($rawTimeSlots as $slot){
            $this->res_cal[$slot['calendar_day']] = $slot;
        }
    }
    
    private function setDeliveryTakeoutSlots(){
        $this->today_delivery_slots_without_cap = $this->getDeliveryTimeSlots($this->current_day, FALSE);
        $this->today_delivery_slots_with_cap = $this->getDeliveryTimeSlots($this->current_day, TRUE);
        $this->today_takeout_slots_without_cap = $this->getTakeoutTimeSlots($this->current_day, FALSE);
        $this->today_takeout_slots_with_cap = $this->getTakeoutTimeSlots($this->current_day, TRUE);
    }
    
    private function getResCalSlotsForDay($selected_day){
        if(isset($this->res_cal[$selected_day])){
            return $this->res_cal[$selected_day];
        }
        return array();
    }
    
    public function getInfo(){
        return array(
            'res_id' => $this->res_id,
            'current_time' => $this->current_time,
            'current_day' => $this->current_day,
            'current_date' => $this->current_date,
            'current_timezone' => $this->current_timezone,
            'crm_info' => $this->crm->getCrmInfo(),
            'today_delivery_slots_without_cap' => json_encode($this->today_delivery_slots_without_cap),
            'today_delivery_slots_with_cap' => json_encode($this->today_delivery_slots_with_cap),
            'today_takeout_slots_without_cap' => json_encode($this->today_takeout_slots_without_cap),
            'today_takeout_slots_with_cap' => json_encode($this->today_takeout_slots_with_cap),
            'res_cal' => $this->getResCalSlotsForDay($this->current_day),
        );
    }

    /**
     * Check if delivery is possible within 1 hours from now.
     * @return boolean true if current delivery is possible else false
     */
    public function isDeliveryPossibleNow(){
        $dayDeliverySlots = $this->getDeliveryTimeSlots($this->current_day, false);
        if(!empty($dayDeliverySlots)){
            $currentSeconds = DateTimeUtils::getSecondsFrom24HTime($this->current_time);
            $firstDeliverySeconds = DateTimeUtils::getSecondsFrom24HTime($dayDeliverySlots[0]);
            if($firstDeliverySeconds <= $currentSeconds + 3600){
                return true;
            }
        }
        return false;
    }
    
    /**
     * Get the restaurant's next delivery date time
     * @return string ex. Today at 12:00 PM
     */
    public function getNextDeliveryDateTime(){
        $found = false;
        $i = -1;
        $selected_day = $this->current_day;
        while(!$found && $i < 6){
            $dayDeliverySlots = $this->getDeliveryTimeSlots($selected_day);
            if(!empty($dayDeliverySlots)){
                $found = true;
                $delivery_time = DateTimeUtils::get24HourHiTo12HAmPmTime($dayDeliverySlots[0]);
            }
            $selected_day = DateTimeUtils::$nextDay2Char[$selected_day];
            $i++;
        }
        if($found){
            if($i == 0){
                $formattedDate = 'Today';
            } else {
                $formattedDate = date('D d M', strtotime($this->current_date . ' +'.$i . ' days'));
            }
            return $formattedDate . ' at '. $delivery_time;
        }
        return '';
    }
    
    /**
     * Check if takeout is possible within 1 hours from now.
     * @return boolean true if current takeout is possible else false
     */
    public function isTakeoutPossibleNow() {
        $dayTakeoutSlots = $this->getTakeoutTimeSlots($this->current_day, false);
        if (!empty($dayTakeoutSlots)) {
            $currentSeconds = DateTimeUtils::getSecondsFrom24HTime($this->current_time);
            $firstDeliverySeconds = DateTimeUtils::getSecondsFrom24HTime($dayTakeoutSlots[0]);
            if ($firstDeliverySeconds <= $currentSeconds + 3600) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the restaurant's next delivery date time
     * @return string ex. Today at 12:00 PM
     */
    public function getNextTakeoutDateTime(){
        $result = '';
        $found = false;
        $i = -1;
        $selected_day = $this->current_day;
        while(!$found && $i < 6){
            $dayTakeoutSlots = $this->getTakeoutTimeSlots($selected_day);
            if(!empty($dayTakeoutSlots)){
                $found = true;
                $delivery_time = DateTimeUtils::get24HourHiTo12HAmPmTime($dayTakeoutSlots[0]);
            }
            $selected_day = DateTimeUtils::$nextDay2Char[$selected_day];
            $i++;
        }
        if($found){
            if($i == 0){
                $formattedDate = 'Today';
            } else if($i == 1){
                $formattedDate = 'Tomorrow';
            } else {
                $formattedDate = date('D d M', strtotime($this->current_date . ' +'.$i . ' days'));
            }
            $result .= $formattedDate . ' at '. $delivery_time;
        }
        return $result;
    }
    
    /**
     * 
     * @param string $selected_day 2 char day string
     * @param boolean $crm_cap optional default true
     * @return array
     */
    public function getDeliveryTimeSlots($selected_day, $crm_cap = true) {
        $rawTimeSlots = $this->getResCalSlotsForDay($selected_day);
        if(empty($rawTimeSlots)){
            return array();
        }
        $timeSlots = [];
        foreach ($rawTimeSlots as $key => $v) {
            $timeSlots[$key] = intval(date("Hi", strtotime($v)));
        }
        $deliverySlots = [];
        $this->setSlotsBetween($timeSlots['breakfast_start_time'], $timeSlots['breakfast_end_time'], $deliverySlots);
        $this->setSlotsBetween($timeSlots['lunch_start_time'], $timeSlots['lunch_end_time'], $deliverySlots);
        $this->setSlotsBetween($timeSlots['dinner_start_time'], $timeSlots['dinner_end_time'], $deliverySlots);
        if($crm_cap){ 
            $this->applyCrmCapOnSlots($deliverySlots);
        }
        if($selected_day == $this->current_day){
            //add 30 minutes to current time.
            $cutoffTime = DateTimeUtils::get24HTimeFromSeconds(DateTimeUtils::getSecondsFrom24HTime($this->current_time) + 1800);
            $this->removeTodaysPassedSlots($cutoffTime, $deliverySlots);
        }
        sort($deliverySlots);
        return $deliverySlots;
    }
    
    /**
     * Get takeout slots for selected time on selected day
     * @param int $selected_time format XX30 or XX00 used to filter out today's passed slots
     * @param type $selected_day
     * @param boolean $crm_cap default true
     * @return array of delivery slots
     * @throws \Exception
     */
    public function getTakeoutTimeSlots($selected_day, $crm_cap = true) {
        $rawTakeoutSlots = $this->getResCalSlotsForDay($selected_day);
        if(empty($rawTakeoutSlots)){
            return array();
        }
        $oh_arr = explode(',', $rawTakeoutSlots['operation_hours']);
        //pr($oh_arr);
        $count = min(count($oh_arr), 4);
        $i = 0;
        $takeoutSlots = [];
        while ($i < $count) {
            $slot = explode('-', trim($oh_arr[$i]));
            $st = (int) str_replace(':', '', substr($slot[0], 0, 5));
            $et = (int) str_replace(':', '', substr($slot[1], 0, 5));
            //pr('$selected_day:'.$selected_day.',st:'.$st. ',et:'.$et);
            if ($st > $et) {
                $et = 2359;
            }
            //restaurant accepts takeout orders only after 30 minutes of open time
            $startTimeSeconds = DateTimeUtils::getSecondsFrom24HTime($st) + 1800;
            $actualStartTime = DateTimeUtils::get24HTimeFromSeconds($startTimeSeconds);
            $this->setSlotsBetween($actualStartTime, $et, $takeoutSlots);
            $i++;
        }
        
        if($crm_cap){ 
            $this->applyCrmCapOnSlots($takeoutSlots);
        }
        
        if($selected_day == $this->current_day){
            //add 30 minutes to current time.
            $cutoffTime = DateTimeUtils::get24HTimeFromSeconds(DateTimeUtils::getSecondsFrom24HTime($this->current_time) + 1800);
            $this->removeTodaysPassedSlots($cutoffTime, $takeoutSlots);
        }
        //sort values in ascending order and also resets keys from 0
        sort($takeoutSlots);
        return $takeoutSlots;
    }
    
    /**
     * Check if restaurant is currently open
     * @return boolean
     */
    public function isResCurrentlyOpen() {
        $open = false;
        $dayCalendar = $this->getResCalSlotsForDay($this->current_day);
        if (!empty($dayCalendar)) {
            $oh_arr = explode(',', $dayCalendar['operation_hours']);
            //pr($dayCalendar[0]);
            $count = min(count($oh_arr), 4);
            $i = 0;
            while (!$open && $i < $count) {
                $slot = explode('-', trim($oh_arr[$i]));
                $st = (int) str_replace(':', '', substr($slot[0], 0, 5));
                $et = (int) str_replace(':', '', substr($slot[1], 0, 5));
                if (($st < $et) && ($this->current_time >= $st) && ($this->current_time <= $et)) {
                    $open = true;
                }
                if (($st > $et) && ($this->current_time >= $st) && ($this->current_time <= 2359)) {
                    $open = true;
                }
                $i++;
            }
        }

        //not open according to today's operation hours. may be open due to yesterdays operation hours
        if (!$open) {
            $prevDayCalendar = $this->getResCalSlotsForDay(DateTimeUtils::$prevDay2Char[$this->current_day]);
            if (!empty($prevDayCalendar)) {
                $oh_arr = explode(',', $prevDayCalendar['operation_hours']);
                //pr($prevDayCalendar[0]);
                $count = min(count($oh_arr), 4);
                $i = 0;
                while (!$open && $i < $count) {
                    $slot = explode('-', trim($oh_arr[$i]));
                    $st = (int) str_replace(':', '', substr($slot[0], 0, 5));
                    $et = (int) str_replace(':', '', substr($slot[1], 0, 5));
                    if (($st > $et) && ($this->current_time >= 0) && ($this->current_time < $et)) {
                        $open = true;
                    }
                    $i++;
                }
            }
        }

        return $open;
    }

    private function setSlotsBetween($start, $end, &$rawSlots){
        if($start > $end){
            $end = 2359;
        }
        $slot = $start;
        while($slot <= $end){
            if(!in_array($slot, $rawSlots)){
                $rawSlots[] = $slot;
            }
            $add = $slot % 100 == 0 ? 30 : 70;
            $slot = $slot + $add;
        }
    }
    
    private function applyCrmCapOnSlots(&$rawSlots){
        foreach ($rawSlots as $k => $slot) {
            if($slot < $this->crm->open_time || $slot > $this->crm->close_time ){
                unset($rawSlots[$k]);
            }
        }
    }
    
    private function removeTodaysPassedSlots($selected_time, &$rawSlots){
        foreach ($rawSlots as $k => $slot) {
            if($slot < $selected_time){
                unset($rawSlots[$k]);
            }
        }
    }
    
    /**
     * Number of milliseconds after which delivery will start
     * @return int 
     */
    public function getDeliveryOpensInMilliSeconds(){
        if(empty($this->today_delivery_slots_with_cap)){
            return 0;
        }
        $firstSlotSeconds = DateTimeUtils::getSecondsFrom24HTime($this->today_delivery_slots_with_cap[0]);
        $oneHourSeconds = 3600;
        $currentTimeSeconds = DateTimeUtils::getSecondsFrom24HTime($this->current_time);
        
        return ($firstSlotSeconds - $oneHourSeconds - $currentTimeSeconds) * 1000;
    }
    
    /**
     * Number of milliseconds after which delivery will start
     * @return int 
     */
    public function getTakeoutOpensInMilliSeconds(){
        if(empty($this->today_takeout_slots_with_cap)){
            return 0;
        }
        $firstSlotSeconds = DateTimeUtils::getSecondsFrom24HTime($this->today_takeout_slots_with_cap[0]);
        $oneHourSeconds = 3600;
        $currentTimeSeconds = DateTimeUtils::getSecondsFrom24HTime($this->current_time);
        
        return ($firstSlotSeconds - $oneHourSeconds - $currentTimeSeconds) * 1000;
    }
    
    /**
     * 
     * @return int # milliseconds till last delivery slot
     */
    public function getLastDeliverySlotDiffMilli(){
        $len = count($this->today_delivery_slots_with_cap);
        if($len == 0 ){
            return 0;
        }
        $lastSlot = $this->today_delivery_slots_with_cap[$len -1];
        $lastSlotSeconds = DateTimeUtils::getSecondsFrom24HTime($lastSlot);
        $currTimeSeconds = DateTimeUtils::getSecondsFrom24HTime($this->current_time);
        $secondsIn45Mins = 45 *60;
        return  ($lastSlotSeconds - $currTimeSeconds - $secondsIn45Mins) * 1000;
    }
    
    /**
     * 
     * @return int # milliseconds till last delivery slot
     */
    public function getLastTakeoutSlotDiffMilli(){
        $len = count($this->today_takeout_slots_with_cap);
        if($len == 0 ){
            return 0;
        }
        $lastSlot = $this->today_takeout_slots_with_cap[$len -1];
        $lastSlotSeconds = DateTimeUtils::getSecondsFrom24HTime($lastSlot);
        $currTimeSeconds = DateTimeUtils::getSecondsFrom24HTime($this->current_time);
        $secondsIn45Mins = 45 *60;
        return  ($lastSlotSeconds - $currTimeSeconds - $secondsIn45Mins) * 1000;
    }
}
