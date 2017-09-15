<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Restaurant\Model\Image;
use Restaurant\Model\Story;
use Restaurant\Model\RestaurantReview;
use Restaurant\Model\Cuisine;
use MCommons\StaticOptions;
use Restaurant\Model\Calendar;
use Restaurant\RestaurantDetailsFunctions;
use Zend\Db\Sql\Predicate\Expression;
use Restaurant\RestaurantDateTimeUtil;
use Restaurant\Crm;
use Search\DateTimeUtils;
use Home\Model\RestaurantTag;


class WebRestaurantGeneralDetailsController extends AbstractRestfulController {

    private $daysMapping = array(
        'mo' => 'mon',
        'tu' => 'tue',
        'we' => 'wed',
        'th' => 'thu',
        'fr' => 'fri',
        'sa' => 'sat',
        'su' => 'sun'
    );

    public function get($id) {
        
        $timezoneformat = StaticOptions::getTimeZoneMapped(array(
         'restaurant_id' => $id
        ));
        $userId = $this->getUserSession()->getUserId();
        $restaurantModel = new Restaurant ();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'c' => 'cities'
            ),
            'on' => 'c.id = restaurants.city_id',
            'columns' => array(
                'city' => 'city_name',
                'sales_tax'
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'rc' => 'restaurant_calendars'
            ),
            'on' => 'rc.restaurant_id = restaurants.id',
            'columns' => array(
                'working_days' => new Expression('GROUP_CONCAT(rc.calendar_day)')
            ),
            'group' => 'rc.restaurant_id',
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'ra' => 'restaurant_accounts'
            ),
            'on' => 'ra.restaurant_id = restaurants.id',
            'columns' => array(
                'is_register'=>'id',
                'account_status'=>'status',
                'email'
                //'ra.restaurant_id'=>'restaurant_id'
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'id',
                'name' => 'restaurant_name',
                'city_id',
                'address',
                'zipcode',
                'res_code' => 'rest_code',
                'has_delivery' => 'delivery',
                'has_takeout' => 'takeout',
                'has_dining' => 'dining',
                'has_menu' => 'menu_available',
                'has_reservation' => 'reservations',
                'price' => 'price',
                'delivery_area',
                'minimum_delivery',
                'min_partysize',
                'delivery_charge',
                'latitude',
                'longitude',
                'accept_cc',
                'menu_without_price',
                'accept_cc_phone',
                'phone_no',
                'delivery_desc',
                'allowed_zip',
                'restaurant_image_name',
                'order_pass_through',
                'cod',
                'restaurant_video_name',
                'facebook_url',
                'twitter_url',
                'gmail_url',
                'pinterest_url',
                'instagram_url',
                'restaurant_logo_name',
            ),
            'joins' => $joins,
            'where' => array(
                'restaurants.id' => $id,
                'restaurants.inactive'=>0,
                'restaurants.closed'=>0
            )
        );
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $detailResponse = $restaurantModel->find($options)->toArray();
       
        if(!isset($detailResponse[0]['id'])){
          throw new \Exception('Restaurant is not valid');
        }       
        $response = (current($detailResponse));
        $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus ( $id );
        
        $response['has_delivery'] = intval($response['has_delivery']);
        $response['cod'] = (int)$response['cod'];
        // As discussed with Yash sir i have comment below code
//        if($response['has_delivery'] == 1){
//          $response['has_delivery']=($currentDayDelivery)?intval(1):intval(0);
//        }
        
        ########### CRM OPEN CLOSE TIME ###############
            $crmOpenCloseTime = explode("-", CRM_OPEN_CLOSE_TIME);
            $selectedLocation = $this->getUserSession ()->getUserDetail ( 'selected_location', array () );
            $cityId = isset($selectedLocation ['city_id'])?$selectedLocation ['city_id']:false;//18848
            if(!$cityId){
                $restaurantCityId = StaticOptions::getRestaurantCityId($id);                
                $cityId = $restaurantCityId['city_id'];
            }
            $cityModel = new \Home\Model\City();
            $cityDetails = $cityModel->cityDetails($cityId);
            $currentCityDateTime = \MCommons\StaticOptions::getRelativeCityDateTime(array(
             'state_code' => $cityDetails [0] ['state_code']
            ));
           
            $cityDateTime = $currentCityDateTime->format('Y-m-d H:i:s');
            $currentTime = strtotime($cityDateTime);
            $response['current_dateTime'] = $cityDateTime;
            $sevenDate = $this->getSevenDayDateFromCurrentDate($cityDateTime,$timezoneformat);
            
//            if(strtotime(date('Y-m-d',$currentTime)." ".$crmOpenCloseTime[0]) <= $currentTime && $currentTime < strtotime(date('Y-m-d',$currentTime)." ".$crmOpenCloseTime[1])){
//                $response['crm_open_now']= true;
//            }else{
//                $response['crm_open_now']= false;
//            }
            $response['crm_open_now']= true;
            ######## Calculate crm time ###########            
            $orderTimeSlot = explode("-",ORDER_TIME_SLOT);            
            $orderFirstSlot = strtotime(date('Y-m-d',$currentTime)." ".$orderTimeSlot[0].':00');
            $orderLastSlot = strtotime(date('Y-m-d',$currentTime)." ".$orderTimeSlot[1]);
            $openbefore45min = $orderFirstSlot - (60*60);
            $before45min = $orderLastSlot - (45*60);
            $firstTimeSlotDate = new \DateTime(date("Y-m-d H:i:s", $openbefore45min), new \DateTimeZone($timezoneformat));
            $lastTimeSlotDate = new \DateTime(date("Y-m-d H:i:s", $before45min), new \DateTimeZone($timezoneformat));
            $currentDateTimeSlot = new \DateTime($cityDateTime, new \DateTimeZone($timezoneformat));
            $crmOpenTimeInterval = $firstTimeSlotDate->diff($currentDateTimeSlot);
            $response['crm_open_time'] = 0;
            $response['crm_close_time'] = 0;
            $response['crm_left_time']=0;    
//            if($crmOpenTimeInterval->invert==1){               
//                $ho = $crmOpenTimeInterval->format('%h')*60*60;
//                $io = $crmOpenTimeInterval->format('%i')*60;
//                $so = ($crmOpenTimeInterval->format('%s')+$ho+$io)*1000;
//                $response['crm_open_time'] = $so;
//            }
            
//            $crmTimeInterval = $lastTimeSlotDate->diff($currentDateTimeSlot); 
//            if($crmTimeInterval->invert==1){               
//                $h = $crmTimeInterval->format('%h')*60*60;
//                $i = $crmTimeInterval->format('%i')*60;
//                $s = ($crmTimeInterval->format('%s')+$h+$i)*1000;
//                $response['crm_close_time'] = $s;
//            }
            
         #######################################
         
         ############CRM OPEN LEFT TIME#################
             
//         if($response['crm_close_time']==0){     
//            $crmCloseDateTime = new \DateTime(date('Y-m-d',$currentTime)." ".$crmOpenCloseTime[1], new \DateTimeZone($timezoneformat));
//            $currentDateTimeSlot = new \DateTime(date("Y-m-d H:i",$currentTime +(30*60)), new \DateTimeZone($timezoneformat));
//            $crmLeftTimeInterval = $crmCloseDateTime->diff($currentDateTimeSlot);

//            if($crmLeftTimeInterval->invert==1){               
//                   $h = $crmLeftTimeInterval->format('%h')*60*60;
//                   $i = $crmLeftTimeInterval->format('%i')*60;
//                   $s = ($crmLeftTimeInterval->format('%s')+$h+$i)*1000;
//                   $response['crm_left_time'] = $s;
//               }
//        }         
         ###############################################
        //$response['crm_open_now']= true;// it is static due to asked by parmanad sir for some time.
        #####################################################
        
        $response['has_takeout'] = intval($detailResponse[0]['has_takeout']);
        $response['has_dining'] = intval($detailResponse[0]['has_dining']);
        $response['has_menu'] = intval($detailResponse[0]['has_menu']);
        $response['has_reservation'] = intval($detailResponse[0]['has_reservation']);
        $response['delivery_area'] = floatval(number_format($detailResponse[0]['delivery_area'], 2));
        $response['minimum_delivery'] = floatval($detailResponse[0]['minimum_delivery']);
        $response['delivery_charge'] = floatval($detailResponse[0]['delivery_charge']);
        $response['accept_cc'] = intval($detailResponse[0]['accept_cc']);
        $response['menu_without_price'] = intval($detailResponse[0]['menu_without_price']);
        $response['accept_cc_phone'] = intval($detailResponse[0]['accept_cc_phone']);
        $response['sales_tax'] = floatval($detailResponse[0]['sales_tax']);
        $response['is_register'] = ($detailResponse[0]['is_register']==null || empty($detailResponse[0]['is_register']))?0:1;       
        $response['order_pass_through'] = intval($detailResponse[0]['order_pass_through']);
        $response['restaurant_video_name'] = ($detailResponse[0]['restaurant_video_name'])?WEB_IMG_URL."munch_videos/".strtolower($response ['res_code'])."/".$detailResponse[0]['restaurant_video_name']:"";
        $response['restaurant_logo_name'] = ($detailResponse[0]['restaurant_logo_name'])?WEB_IMG_URL."munch_images/".strtolower($response ['res_code'])."/".$detailResponse[0]['restaurant_logo_name']:"";;
        $accept_cc = (int) ($response ['accept_cc']);
        $accept_cc_phone = (int) ($response ['accept_cc_phone']);
        $menu_without_price = (int) ($response ['menu_without_price']);
        $response['has_delivery_o'] = intval($response ['has_delivery']);
        $response['has_takeout_o'] = intval($response ['has_takeout']);
        if ($menu_without_price || !$accept_cc_phone) {
            $response ['has_delivery'] = intval(0);
            $response ['has_takeout'] = intval(0);
        }
        $restaurantImageModel = new Image ();
        $options = array(
            'columns' => array(
                'image_count' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $id
            )
        );
        $restaurantImageModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $imageCount = $restaurantImageModel->find($options)->current()->getArrayCopy();
        $response ['has_gallery'] = $imageCount ['image_count'] > 0 ? true : false;
        $restaurantStoryModel = new Story ();
        $restaurantStoryModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'story_count' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $id
            )
        );
        $storyResponse = $restaurantStoryModel->find($options)->current()->getArrayCopy();
        $response ['has_story'] = $storyResponse ['story_count'] > 0 ? true : false;
        $restaurantReviewModel = new RestaurantReview ();
        $restaurantReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'review_count' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $id
            )
        );
        $reviewCount = $restaurantReviewModel->find($options)->current()->getArrayCopy();
        $response ['has_review'] = $reviewCount ['review_count'] > 0 ? true : false;
        $restaurantCuisineModel = new Cuisine ();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'c' => 'cuisines'
            ),
            'on' => 'c.id = restaurant_cuisines.cuisine_id',
            'columns' => array(
                'name' => 'cuisine'
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(),
            'where' => array(
                'restaurant_cuisines.status' => 1,
                'restaurant_cuisines.restaurant_id' => $id
            ),
            'joins' => $joins
        );
        $restaurantCuisineModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $cuisineResponse = $restaurantCuisineModel->find($options)->toArray();
        $response ['cuisines'] = $cuisineResponse;
        $date = StaticOptions::getDateTime()->format(StaticOptions::MYSQL_DATE_FORMAT);
        $restaurantCalendarModel = new Calendar ();
        $restaurantCalendarModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $restaurantDetailsFunctions = new RestaurantDetailsFunctions ();
        $currentDay = $restaurantDetailsFunctions->extract_day_from_date($date);
        $response ['is_currently_open'] = $restaurantCalendarModel->isRestaurantOpen($id);
        //$response ['crm_start_time'] = $restaurantCalendarModel->crmStartTime($id);
        //$response ['crm_end_time'] = $restaurantCalendarModel->crmEndTime($id);
        //$response ['is_currently_deliver'] = $restaurantCalendarModel->isRestaurantDeliver($id,$response ['is_currently_open'],$response ['has_delivery']);
        $response ['is_currently_deliver'] = $restaurantCalendarModel->isOpenDeliverForOneHourBefore($id,$response ['has_delivery']);
        
        if($response ['is_currently_deliver']){
            $response['delivery_open_timer'] = 0;
        }else{
            $response['delivery_open_timer'] = $restaurantCalendarModel->getDeliveryTakeoutTimmer($id,$response ['has_delivery'],'order');
        }
        
         //$response ['is_currently_takeout'] = $restaurantCalendarModel->isRestaurantTakeout($id,$response ['is_currently_open'],$response ['has_takeout']);
        $response ['is_currently_takeout'] = $restaurantCalendarModel->isOpenTakeoutForhalfHourBefore($id,$response ['is_currently_open'],$response ['has_takeout']);
        if($response ['is_currently_takeout']){
            $response['takeout_open_timer'] = 0;
        }else{
            $response['takeout_open_timer'] = $restaurantCalendarModel->getDeliveryTakeoutTimmer($id,$response ['has_takeout'],'takeout');
        }
            


        //$response ['delivery_sec_last'] = $restaurantCalendarModel->deliveryPreviousSlotOfRestauratCloseSlot($id,$response ['is_currently_open'],$response ['has_delivery']);
       if($response ['is_currently_deliver']){
            $response ['delivery_time_diff'] = $restaurantCalendarModel->restaurantDeliveryTimeDiff($id,$response ['has_delivery']);
        }else{
            $response ['delivery_time_diff'] = 0;
        }
        $response ['next_deliver'] = $this->nextDelivery($response ['is_currently_deliver'],$sevenDate,$id,'order',$timezoneformat,$response ['delivery_time_diff']);        
        if($response ['is_currently_takeout']){
            $response ['takeout_time_diff'] = $restaurantCalendarModel->restaurantTakeoutTimeDiff($id,$response ['has_takeout']);
        }else{
            $response ['takeout_time_diff'] = 0;
        }
        $response ['next_takeout'] = $this->nextDelivery($response ['is_currently_takeout'],$sevenDate,$id,'takeout',$timezoneformat,$response ['takeout_time_diff']);
        //$response ['takeout_sec_last'] = $restaurantCalendarModel->takeoutPreviousSlotOfRestauratCloseSlot($id,$response ['is_currently_open'],$response ['has_takeout']);
        $response ['res_code'] = strtolower($response ['res_code']);
        $response ['base_url'] = IMAGE_PATH;
        $days = explode(",", $response ['working_days']);
        $mapping = $this->daysMapping;
        $days = array_map(function ($val) use($mapping) {
            if (isset($mapping [$val])) {
                return $val = $mapping [$val];
            }
        }, $days);
        $response ['working_days'] = $days;
        ###########################Get All Working Days###################################
        $response ['all_delivery_working_days'] = $this->nextDelivery($response ['is_currently_deliver'],$sevenDate,$id,'order',$timezoneformat,$response ['delivery_time_diff'],1);
        $response ['all_takeout_working_days'] = $this->nextDelivery($response ['is_currently_deliver'],$sevenDate,$id,'takeout',$timezoneformat,$response ['takeout_time_diff'],1);
        ##################################################################################
        $restaurantFunctions = new RestaurantDetailsFunctions ();
        $response ['user_craveit'] = ($restaurantFunctions->checkIfUserCravesForIt($id)==null)?false:$restaurantFunctions->checkIfUserCravesForIt($id);
        
        //=================changes made by dhirendra on 22-Jan-2016=============
        //$this->updateFieldsByDsyzug($response);
        //======================================================================
        $this->addTags($response);
        if ($response ['has_delivery']) {
            $response ['review_for'] = intval(1);          
        }
        $response ['review_for'] = (isset($response ['has_takeout']) && $response ['has_takeout'] == 1) ? intval(2) : intval(3);
        $restaurantModal=new \Restaurant\Model\RestaurantSocialMediaActivity();
        $socialOneLiner=$restaurantModal->getResSocialOneLiner($id);
       
        if(!empty($socialOneLiner)){
            $response ['cuisine_one_liner']=($socialOneLiner['cuisine_one_liner']!='')?trim($socialOneLiner['cuisine_one_liner']):'';
            $response ['famous_dish_one_liner']=($socialOneLiner['famous_dish_one_liner']!='')?trim($socialOneLiner['famous_dish_one_liner']):'';
            $response ['ambience_one_liner']=($socialOneLiner['ambience_one_liner']!='')?trim($socialOneLiner['ambience_one_liner']):'';
            $response ['chef_feature_one_liner']=($socialOneLiner['chef_feature_one_liner']!='')?trim($socialOneLiner['chef_feature_one_liner']):'';
        }else{
            $response ['cuisine_one_liner']='';
            $response ['famous_dish_one_liner']='';
            $response ['ambience_one_liner']='';
            $response ['chef_feature_one_liner']='';
        }
        
        $orderTimeSlot = explode("-", ORDER_TIME_SLOT);
        $response['capping_message']=(CRM_CAPPING)?'We process all orders and reservation between '.date("h:i A",strtotime($orderTimeSlot[0].":00")).' and '.date("h:i A",strtotime($orderTimeSlot[1])).' EST':'';      
        $RestaurantServerModal= new \Restaurant\Model\RestaurantServer();
        $getExUsers= $RestaurantServerModal->findExistingUser($id, $userId);
        $response['dine-more-register']=(!empty($getExUsers))?true:false;
        return $response;
    }
    
    public function getList() {
        $restaurantModel = new Restaurant ();
       
        $joins = array();

        $joins [] = array(
            'name' => array(
                'ra' => 'restaurant_accounts'
            ),
            'on' => new \Zend\Db\Sql\Expression('ra.restaurant_id = restaurants.id AND restaurants.delivery_desc!="" AND restaurants.id>58000'),
            'columns' => array(                
                'account_status'=>'status'            
            ),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'id',
                'restaurant_name',
                'rest_code',
                'price',              
                'allowed_zip',
                'restaurant_image_name',
                'accept_cc_phone',
                'delivery',
                'takeout',
                'minimum_delivery',
                'delivery_desc',
                'reservations',
            ),
            'joins' => $joins,
            'where' => array(
                'restaurants.inactive'=>0,
                'restaurants.closed'=>0, 
                'accept_cc_phone'=>1, 
                'menu_without_price'=>0,
            ),
            
            'order' =>'restaurants.updated_on desc',            
            'limit'=>20
        );
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $detailResponse = $restaurantModel->find($options)->toArray();
        $detailResponse1=array();
        #Get cuisines of Restaurant#
        $restaurantCuisineModel = new Cuisine ();
        $config = $this->getServiceLocator()->get('Config');  
        if($detailResponse){
        foreach($detailResponse as $key => $details){
          if($details['delivery']==1 || $details['takeout']==1 || $details['reservation']==1){
             $detailResponse1 = $this->refineRestaurantDetail($key,$restaurantCuisineModel,$detailResponse1,$details,$config);
          }
        }
        }
            
        return $detailResponse1;
    }
    
    private function refineRestaurantDetail($key,$restaurantCuisineModel,$detailResponse1,$details,$config){
          $restaurantImageModel = new \Restaurant\Model\Gallery();  
          $restaurantGalleryImage=$restaurantImageModel->getRestaurantGallery($details['id']);
          $restCode=  strtolower($details['rest_code']);
          $galImage=isset($restaurantGalleryImage[0]['image']) && $restaurantGalleryImage[0]['image']!=''?$config['constants']['protocol']."://".$config['constants']['imagehost'].'munch_images/'.$restCode."/".$restaurantGalleryImage[0]['image']:'';
          if($this->checkRemoteFile($galImage)){
          $detailResponse1[$key]['id'] = $details['id'];
          $detailResponse1[$key]['restaurant_name'] = $details['restaurant_name'];
          $detailResponse1[$key]['rest_code'] = $details['rest_code'];
          $detailResponse1[$key]['price'] = $details['price'];
          $detailResponse1[$key]['allowed_zip'] = $details['allowed_zip'];
          $detailResponse1[$key]['account_status'] = $details['account_status'];
          $detailResponse1[$key]['delivery_desc'] = $details['delivery_desc'];
          $minDelivery =  explode(".",$details['minimum_delivery']);
          if($minDelivery[1] > 0){
            $detailResponse1[$key]['minimum_delivery'] = $details['minimum_delivery'];
          }else{
            $detailResponse1[$key]['minimum_delivery'] = $minDelivery[0];
          }
            $detailResponse1[$key]['restaurant_image_name'] = $galImage;
            $detailResponse1[$key]['link']=PROTOCOL.$config['constants']['web_url']."/restaurants/".$details['restaurant_name']."/".$details['id']."/menu";
            $joins = array();
            $joins [] = array(
                'name' => array(
                    'c' => 'cuisines'
                ),
                'on' => 'c.id = restaurant_cuisines.cuisine_id',
                'columns' => array(
                    'name' => 'cuisine'
                ),
                'type' => 'left'
            );
            $options = array(
                'columns' => array(),
                'where' => array(
                    'restaurant_cuisines.status' => 1,
                    'restaurant_cuisines.restaurant_id' => $details['id'],
                ),
                'joins' => $joins
            );
            $restaurantCuisineModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
            $cuisineResponse = $restaurantCuisineModel->find($options)->toArray();
            $detailResponse1[$key]['cuisines']=$cuisineResponse;
          }
            return $detailResponse1;
    }
    
  private function checkRemoteFile($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        // don't download content
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (curl_exec($ch) !== FALSE) {
            return true;
        } else {
            return false;
        }
    }
    
    
    private function getSevenDayDateFromCurrentDate($currentDate=false,$timezoneformat){
        $sevenDateFromCurrent = array();
        if($currentDate){
            $todayDateF  = $xmasDay = new \DateTime($currentDate,new \DateTimeZone($timezoneformat));
            $todayDate=$todayDateF->format('Y-m-d');
            $sevenDateFromCurrent[]=$todayDate;
            for($i=1;$i<=30;$i++){
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
                      if($slotHour >=$startOrderTime && $slotHour <= $endOrderTime){ 
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
                    if($slotHour >=$startOrderTime && $slotHour <= $endOrderTime){  
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
    
    
    private function nextDelivery($isCurrentlyDeliver,$sevenDate,$id,$type,$timezoneformat,$timeDeff=false,$allWorking=false){
        $i=1;         
        $allWorkingDays = array(); 
        $todayDates = '';

        
//        if($timeDeff && $timeDeff > 0 && $timeDeff < 4500000){
//            $timeDeff = $timeDeff+1000;
//            
//        }
        $currentDateTime = new \DateTime("now",new \DateTimeZone($timezoneformat));
            $fomatedCurrentDateTime = $currentDateTime->format('Y-m-d H:i:s');
        
         foreach($sevenDate as $key =>$sDate){
              $timeSlotArray = $this->getFirstOpenTime($type,$id,$sDate);   //6:30  
             
//             // if($timeDeff > 0 && $timeDeff < 4500000 && $i==1){
//                $desireSlotInMSec = strtotime($currentDateTime->format("H:i:s"))*1000;
//                $calculatedTime = $desireSlotInMSec+$timeDeff+2700000;
//                $calculatedDateTime = date('Y-m-d H:i:s',$calculatedTime/1000);
//                $timeSlotArray = $this->getFirstOpenTime($type,$id,$sDate,$calculatedDateTime);   //6:30       
//              }
              
             if(isset($timeSlotArray['timeslots']) && !empty($timeSlotArray['timeslots'])){
                $xmasDay = new \DateTime($sDate." ".$timeSlotArray['timeslots'][0],new \DateTimeZone($timezoneformat));
                if($allWorking){                       
                     if($i==1){
                        $todayDates = $xmasDay->format('Y-m-d');
                        $allWorkingDays[$todayDates] = "Today"; // Today                      
                     }elseif($i==2){
                         $tomorrowDates = $xmasDay->format('Y-m-d');
                         if(isset($allWorkingDays[$todayDates]) && $allWorkingDays[$todayDates]==="Today"){
                            $allWorkingDays[$tomorrowDates] = $xmasDay->format('D d M'); // Tomorrow
                         }else{
                            $allWorkingDays[$tomorrowDates] = "Tomorrow"; // Tomorrow 
                         }
                     }else{
                         $dayAfterTomorrowDate = $xmasDay->format('Y-m-d');
                         $allWorkingDays[$dayAfterTomorrowDate] = $xmasDay->format('D d M'); // Sat 19 Dec      
                     }    
                     $countAllWorkingDays = count($allWorkingDays);
                     if($countAllWorkingDays == 7){
                         break;
                     }
                }else{
                     if($i==1){
                         return $nextDelivery = "Today at ".$xmasDay->format('h:i A'); // Today at 10:00 AM
                     }elseif($i==2){
                         return $nextDelivery = "Tomorrow at ".$xmasDay->format('h:i A'); // Tomorrow at 10:00 AM   
                     }else{
                         return $nextDelivery = $xmasDay->format('D d \a\t h:i A'); // Sat 19 at 10:00 AM      
                     }
                }
             }
             $i++;
         }
         if($allWorking){             
            return $allWorkingDays;
         }
    }
    
    
    private function updateFieldsByDsyzug(&$response){
        //pr($response);
        $cityDatetimeInfo = DateTimeUtils::getCityDayDateAndTime24F($response['city_id']);
//        $cityDatetimeInfo = array(
//                'city_id' => 18848,
//                'date' => '2016-02-11',
//                'day' => 'th',
//                'time' => 2210,
//                'timezone' => 'America/New_York'
//            );

        $crm = new Crm($cityDatetimeInfo['timezone']);
        $response['dsyzug']['crm_open_now'] = $crm->isOpen($cityDatetimeInfo['time']);
        $response['dsyzug']['crm_open_time'] = $crm->getCrmOpenTimeInMilli($cityDatetimeInfo['time']);
        $response['dsyzug']['crm_close_time'] = $crm->getCrmCloseTimeInMilli($cityDatetimeInfo['time']);
    
        $rdtu = new RestaurantDateTimeUtil($response['id'], $cityDatetimeInfo);
        //pr($rdtu->getVars());
        $response['dsyzug']['is_currently_open'] = $rdtu->isResCurrentlyOpen();
        $response['dsyzug']['is_currently_deliver'] = $rdtu->isDeliveryPossibleNow();
        $response['dsyzug']['is_currently_takeout'] = $rdtu->isTakeoutPossibleNow();
        $response['dsyzug']['next_deliver'] = $rdtu->getNextDeliveryDateTime();
        $response['dsyzug']['next_takeout'] = $rdtu->getNextTakeoutDateTime();
        
        if($response['dsyzug']['is_currently_deliver']){
            $response['dsyzug']['delivery_open_timer'] = 0;
        } else {
            $response['dsyzug']['delivery_open_timer'] = $rdtu->getDeliveryOpensInMilliSeconds();
        }
        
        if($response['dsyzug']['is_currently_takeout']){
            $response['dsyzug']['takeout_open_timer'] = 0;
        } else {
            $response['dsyzug']['takeout_open_timer'] = $rdtu->getTakeoutOpensInMilliSeconds();
        }
        
        $response['dsyzug']['delivery_time_diff'] = $rdtu->getLastDeliverySlotDiffMilli();
        $response['dsyzug']['takeout_time_diff'] = $rdtu->getLastTakeoutSlotDiffMilli();
        
        if ($this->getQueryParams('DeBuG','') == '404') {
            $response['dsyzug']['debug_info'] = array(
                'crm_opens_in_His' => gmdate("H:i:s", $response['dsyzug']['crm_open_time']/1000),
                'crm_closes_in_His' => gmdate("H:i:s", $response['dsyzug']['crm_close_time']/1000),
                'delivery_starts_in_His' => gmdate("H:i:s", $response['dsyzug']['delivery_open_timer']/1000),
                'takeout_starts_in_His' => gmdate("H:i:s", $response['dsyzug']['takeout_open_timer']/1000),
                'delivery_time_diff_His' => gmdate("H:i:s", $response['dsyzug']['delivery_time_diff']/1000),
                'takeout_time_diff_His' => gmdate("H:i:s", $response['dsyzug']['takeout_time_diff']/1000),
                'rdtu_info' => $rdtu->getInfo(),
            );
        }


        // pr($response,1);
    }

    private function addTags(&$response){
        $tags = new RestaurantTag();
        $response['tags_fct'] = $tags->getTags($response['id']);
    }
}
