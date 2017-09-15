<?php

namespace Restaurant;

use Restaurant\RestaurantDateTimeUtil;
use \Search\DateTimeUtils;
/**
 * Description of Crm
 *
 * @author dhirendra
 */
class Crm {
    
    const BASE_TIMEZONE = 'America/New_York'; //New York Time
    const CRM_OPEN_TIME = '09:00';
    const CRM_CLOSE_TIME = '22:30';
    const CRM_ORDER_CUTOFF_TIME = '21:45';
    
    /**
     * crm open time with respect to the selected city
     * @var int in 0-2359 range
     */
    public $open_time;
     
    /**
     * crm close time with respect to the selected city
     * @var int in 0-2359 range
     */
    public $close_time;
    
    /**
     * crm order cutoff time
     * @var int in 0-2359 range
     */
    public $order_cutoff_time;
    
    /**
     * Set crm open, close, and order_cutoff times according to selected city's timezone
     * @param string $timezone 
     */
    public function __construct($timezone) {
        //pr($timezone,1);
        $openTime = new \DateTime(self::CRM_OPEN_TIME, new \DateTimeZone(self::BASE_TIMEZONE));
        $openTime->setTimezone(new \DateTimeZone($timezone));
        $this->open_time = intval($openTime->format('Hi'));
        
        $closeTime = new \DateTime(self::CRM_CLOSE_TIME, new \DateTimeZone(self::BASE_TIMEZONE));
        $closeTime->setTimezone(new \DateTimeZone($timezone));
        $this->close_time = intval($closeTime->format('Hi'));
        
        $orderCutoffTime = new \DateTime(self::CRM_ORDER_CUTOFF_TIME, new \DateTimeZone(self::BASE_TIMEZONE));
        $orderCutoffTime->setTimezone(new \DateTimeZone($timezone));
        $this->order_cutoff_time = intval($orderCutoffTime->format('Hi'));
    }
    
    /**
     * Get info about crm open and close times
     * @return array with keys crm_open_time and crm_close_time,order_cutoff_time
     */
    public function getCrmInfo(){
        return array(
                'crm_open_time' => $this->open_time,
                'crm_close_time' => $this->close_time,
                'crm_order_cutoff_time' => $this->order_cutoff_time,
            );
    }
    
    /**
     * Check if CRM is open at time $time
     * @param int $time time in range 0-2359
     * @return boolean
     */
    public function isOpen($time){
        if($time >= $this->open_time && $time <= $this->close_time){
            return true;
        }
        return false;
    }
    
    /**
     * Get time (milli seconds) when crm will open. Assumes that current time 
     * is less than crm open time.
     * @param int $time in range 0-2359 and less than 
     * @return int crm will open after these many milli seconds
     * @throws \Exception
     */
    public function getCrmOpenTimeInMilli($time){
        if($this->isOpen($time) || $time > $this->open_time){
            return 0;
        }
        $seconds = DateTimeUtils::getSecondsFrom24HTime($this->open_time) - DateTimeUtils::getSecondsFrom24HTime($time);
        //pr('crm_will_open_in:'.gmdate("H:i:s", $seconds));
        return $seconds * 1000;
    }
    
    /**
     * Get time (milli seconds) when crm will close. Assumes that current time 
     * is less than crm close time.
     * @param type $time
     * @return int crm will open after these many milli seconds
     * @throws \Exception
     */
    public function getCrmCloseTimeInMilli($time){
        if(!$this->isOpen($time)){
            return 0;
        }
        $seconds = DateTimeUtils::getSecondsFrom24HTime($this->close_time) - DateTimeUtils::getSecondsFrom24HTime($time);
        //pr('crm_will_close_in:'.gmdate("H:i:s", $seconds));
        return $seconds * 1000;
    }
}
