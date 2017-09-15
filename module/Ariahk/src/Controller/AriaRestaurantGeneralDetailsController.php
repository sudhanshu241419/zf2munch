<?php

namespace Ariahk\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Restaurant\Model\Image;
use Restaurant\Model\Cuisine;
use MCommons\StaticOptions;
use Restaurant\Model\Calendar;
use Restaurant\RestaurantDetailsFunctions;
use Zend\Db\Sql\Predicate\Expression;
use MCommons\Caching;
use Restaurant\Model\Menu;

class AriaRestaurantGeneralDetailsController extends AbstractRestfulController {

    public function get($id) {
        $selectedLocation = $this->getUserSession()->getUserDetail('selected_location', array());
        $stateCode = isset($selectedLocation ['state_code']) ? $selectedLocation ['state_code'] : 'NY';
        $timezoneformat = StaticOptions::getTimeZoneMapped(array(
                    'restaurant_id' => $id,
                    'state_code' => $stateCode
        ));
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
        $currentTime = strtotime($cityDateTime);

        $sevenDate = $this->getSevenDayDateFromCurrentDate($cityDateTime, $timezoneformat);
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
                'ra' => 'restaurant_accounts'
            ),
            'on' => 'ra.restaurant_id = restaurants.id',
            'columns' => array(
                'is_register' => 'id',
                'account_status' => 'status'
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
                'has_delivery' => 'delivery',
                'res_code' => 'rest_code',
                'has_takeout' => 'takeout',
                'has_dining' => 'dining',
                'has_menu' => 'menu_available',
                'has_reservation' => 'reservations',
                'price' => 'price',
                'delivery_area',
                'minimum_delivery',
                'delivery_charge',
                'latitude',
                'longitude',
                'accept_cc',
                'menu_without_price',
                'accept_cc_phone',
                'phone_no',
                'delivery_desc',
                'allowed_zip',
                'restaurant_image_name'
            ),
            'joins' => $joins,
            'where' => array(
                'restaurants.id' => $id,
                //'restaurants.inactive' => 0,
                'restaurants.closed' => 0
            )
        );
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $detailResponse = $restaurantModel->find($options)->toArray();
        if (!isset($detailResponse[0]['id'])) {
            throw new \Exception('Restaurant is not valid');
        }
        $response = (current($detailResponse));
        $response['has_takeout'] = intval($detailResponse[0]['has_takeout']);
        $response['has_dining'] = intval($detailResponse[0]['has_dining']);
        $response['has_menu'] = intval($detailResponse[0]['has_menu']);
        $response['has_reservation'] = intval($detailResponse[0]['has_reservation']);
        $response['accept_cc'] = intval($detailResponse[0]['accept_cc']);
        $response['menu_without_price'] = intval($detailResponse[0]['menu_without_price']);
        $response['accept_cc_phone'] = intval($detailResponse[0]['accept_cc_phone']);
        $response['sales_tax'] = floatval($detailResponse[0]['sales_tax']);
        $response['is_register'] = ($detailResponse[0]['is_register'] == null || empty($detailResponse[0]['is_register'])) ? 0 : 1;
        $accept_cc = (int) ($response ['accept_cc']);
        $accept_cc_phone = (int) ($response ['accept_cc_phone']);
        $menu_without_price = (int) ($response ['menu_without_price']);
        $response['has_takeout_o'] = intval($response ['has_takeout']);
        $response ['current_datetime'] = $cityDateTime;
        $restaurantImageModel = new Image ();
        $options = array(
            'columns' => array(
                'image_count' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $id
            )
        );
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
        $response ['res_code'] = strtolower($response ['res_code']);
        $response ['all_delivery_working_days'] = $this->nextDelivery(true, $sevenDate, $id, 'order', $timezoneformat, 1);
        $response ['all_takeout_working_days'] = $this->nextDelivery(true, $sevenDate, $id, 'takeout', $timezoneformat, 1);
        $ariafunction = new \Ariahk\AriaFunctions();
        $restDineAndMore  = $ariafunction->restaurantTaged($id);
        $response ['isdinemore'] = ($restDineAndMore)?(int)1:(int)0;
        $delivery_operation_hours = $ariafunction->getDeliveryOperationHours($id);
        $response = array_merge($response, $delivery_operation_hours);
        $takeout_operation_hours = $ariafunction->getTakeoutOperationHours($id);
        $response = array_merge($response, $takeout_operation_hours);
        $response ['base_url'] = IMAGE_PATH;
        $menu_item = $this->getMenu($id);
        $response = array_merge($response, $menu_item);
        $this->gallery($response);
        $this->review($response);
        $this->about($response);
        $this->calendar($response);
        $this->deals($response);
        $this->features($response);
        return $response;
    }

    public function getSevenDayDateFromCurrentDate($currentDate = false, $timezoneformat) {
        $sevenDateFromCurrent = array();
        if ($currentDate) {
            $todayDateF = $xmasDay = new \DateTime($currentDate, new \DateTimeZone($timezoneformat));
            $todayDate = $todayDateF->format('Y-m-d');
            $sevenDateFromCurrent[] = $todayDate;
            for ($i = 1; $i <= 30; $i++) {
                $xmasDay = new \DateTime($currentDate . '+ ' . $i . ' day');
                $sevenDateFromCurrent[] = $xmasDay->format('Y-m-d'); // 2010-12-25
            }
        }
        return $sevenDateFromCurrent;
    }

    public function nextDelivery($isCurrentlyDeliver, $sevenDate, $id, $type, $timezoneformat, $allWorking = false) {
        $i = 1;
        $allWorkingDays = array();
        $todayDates = '';

        $currentDateTime = new \DateTime("now", new \DateTimeZone($timezoneformat));
        $fomatedCurrentDateTime = $currentDateTime->format('Y-m-d H:i:s');

        foreach ($sevenDate as $key => $sDate) {
            $timeSlotArray = $this->getFirstOpenTime($type, $id, $sDate);   //6:30  
            if (isset($timeSlotArray['timeslots']) && !empty($timeSlotArray['timeslots'])) {
                $xmasDay = new \DateTime($sDate . " " . $timeSlotArray['timeslots'][0], new \DateTimeZone($timezoneformat));
                if ($allWorking) {
                    if ($i == 1) {
                        $todayDates = $xmasDay->format('Y-m-d');
                        $allWorkingDays[$todayDates] = "Today"; // Today                      
                    } elseif ($i == 2) {
                        $tomorrowDates = $xmasDay->format('Y-m-d');
                        if (isset($allWorkingDays[$todayDates]) && $allWorkingDays[$todayDates] === "Today") {
                            $allWorkingDays[$tomorrowDates] = $xmasDay->format('D d M'); // Tomorrow
                        } else {
                            $allWorkingDays[$tomorrowDates] = "Tomorrow"; // Tomorrow 
                        }
                    } else {
                        $dayAfterTomorrowDate = $xmasDay->format('Y-m-d');
                        $allWorkingDays[$dayAfterTomorrowDate] = $xmasDay->format('D d M'); // Sat 19 Dec      
                    }
                    $countAllWorkingDays = count($allWorkingDays);
                    if ($countAllWorkingDays == 7) {
                        break;
                    }
                } else {
                    if ($i == 1) {
                        return $nextDelivery = "Today at " . $xmasDay->format('h:i A'); // Today at 10:00 AM
                    } elseif ($i == 2) {
                        return $nextDelivery = "Tomorrow at " . $xmasDay->format('h:i A'); // Tomorrow at 10:00 AM   
                    } else {
                        return $nextDelivery = $xmasDay->format('D d \a\t h:i A'); // Sat 19 at 10:00 AM      
                    }
                }
            }
            $i++;
        }
        if ($allWorking) {
            return $allWorkingDays;
        }
    }

    public function getMenu($restaurant_id = 0) {
        $memCached = $this->getServiceLocator()->get('memcached');
        $config = $this->getServiceLocator()->get('Config');
        if ($config['constants']['memcache'] && $memCached->getItem('menu_' . $restaurant_id)) {
            return $memCached->getItem('menu_' . $restaurant_id);
        } else {
            // Get restaurant menu
            $menuModel = new Menu ();
            RestaurantDetailsFunctions::$_bookmark_types = $menuModel->bookmark_types;
            $response = $menuModel->restaurantMenuesNew(array(
                        'columns' => array(
                            'restaurant_id' => $restaurant_id
                )))->toArray();
            if (!empty($response)) {
                $response = RestaurantDetailsFunctions::createWebNestedMenu($response, $restaurant_id);
                $response = RestaurantDetailsFunctions::knowLastLeaf($response);
                $response = RestaurantDetailsFunctions::formatResponse($response);
            } else {
                throw new \Exception("Restaurant records not found", 405);
            }
            if (!$this->isMobile()) {
                $response = array(
                    'menu' => $response
                );
            }
            $memCached->setItem('menu_' . $restaurant_id, $response, 0);
            return $response;
        }
    }

    public function getFirstOpenTime($type, $id, $date, $calculatedDateTime = false) {

        $ariaFunction = new \Ariahk\AriaFunctions();

        if ($type == 'order') {
            $orderFinal ['timeslots'] = array();
            $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus($id, $date);
            if ($currentDayDelivery) {
                foreach (StaticOptions::getRestaurantOrderTimeSlots($id, $date) as $t) {
                    if (isset($t ['status']) && $t ['status'] == 1) {
                        $slotHour = strtotime($t['slot']);
                        if ($calculatedDateTime) {
                            $currentDateSlot = strtotime($date . " " . $t['slot']);
                            $calculatedDateTimeSec = strtotime($calculatedDateTime);
                            if ($currentDateSlot > $calculatedDateTimeSec) {
                                $orderFinal ['timeslots'] [] = $t ['slot'];
                            }
                        } else {
                            $orderFinal ['timeslots'] [] = $t ['slot'];
                        }
                    }
                }
            }

            return $orderFinal;
        } elseif ($type == 'takeout') {
            $orderFinal ['timeslots'] = array();
            foreach ($ariaFunction->getAriaTakeoutTimeSlots($id, $date) as $t) {
                if (isset($t ['status']) && $t ['status'] == 1) {
                    $slotHour = strtotime($t['slot']);
                    if ($calculatedDateTime) {
                        $currentDateSlot = strtotime($date . " " . $t['slot']);
                        $calculatedDateTimeSec = strtotime($calculatedDateTime);
                        if ($currentDateSlot > $calculatedDateTimeSec) {
                            $orderFinal ['timeslots'] [] = $t ['slot'];
                        }
                    } else {
                        $orderFinal ['timeslots'] [] = $t ['slot'];
                    }
                }
            }

            return $orderFinal;
        }
    }
    
   public function gallery(&$response){
   $restaurantImageModel = new Image();
        $options = array(
            'columns' => array(
                'image',
                'image_type'
            ),
            'where' => array(
                'restaurant_id' => $response['id'],
                'status' => 1
            ),
            'limit' => 6
        );
        $restaurantImageModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $restaurantImages = $restaurantImageModel->find($options)->toArray();
        $response ['gallery'] ['status'] = count($restaurantImages) > 0 ? true : false;
        $response ['gallery'] ['detail'] = $restaurantImages;
    }
    
    public function review(&$response){
        $reviewModel = new \Restaurant\Model\RestaurantReview ();
        $userReviewModel = new \User\Model\UserReview ();
        $userReviewCountOptions = array(
            'columns' => array(
                'total' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $response['id'],
                'status' => 1
            )
        );
        $countOptions = array(
            'columns' => array(
                'total' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $response['id'],
                'review_type' => 'N'
            )
        );
        $reviewOptions = array(
            'columns' => array(
                'review' => 'reviews'
            ),
            'where' => array(
                'restaurant_id' => $response['id'],
                'sentiments' => 'Positive'
            ),
            'order' => array(
                'date' => 'desc'
            ),
            'limit' => 1
        );
        $reviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $reviewCount = $reviewModel->find($countOptions)->current()->getArrayCopy();
        $userReviewCount = $userReviewModel->find($userReviewCountOptions)->current()->getArrayCopy();
        $consolidatedReview = $reviewModel->find($reviewOptions)->toArray();
        $response ['review'] ['status'] = !empty($consolidatedReview) ? true : false;
        $response ['review'] ['count'] = $reviewCount ['total'] + $userReviewCount ['total'];
        $response ['review'] ['detail'] = !empty($consolidatedReview) ? $consolidatedReview [0] ['review'] : '';
    }
    
    public function about(&$response){
        $restaurantModel = new Restaurant();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'rs' => 'restaurant_stories'
            ),
            'on' => 'rs.restaurant_id = restaurants.id',
            'columns' => array(
                'title' => 'title'
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'description'
            ),
            'joins' => $joins,
            'where' => array(
                'restaurants.id' => $response['id']
            )
        );
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $detailResponse = $restaurantModel->find($options)->current()->getArrayCopy();
        $response ['about'] ['status'] = $detailResponse ['description'] != null ? true : false;
        $response ['about'] ['detail'] = $detailResponse ['description'];
    }
    
    public function calendar(&$response){
        $restaurantDetailsFunctions = new RestaurantDetailsFunctions ();        
        if($restaurantDetailsFunctions->isRestaurantOpenTwentyFourHours($response['id'])){
            $response ['calendar'] ['open_twenty_four_hours'] = 'Open 24 hours! All year around!';
            $response ['calendar'] ['detail'] = null;            
        }else{
            $finalTimings = $restaurantDetailsFunctions->getRestaurantDisplayTimings($response['id']);
            $response ['calendar'] ['open_twenty_four_hours'] = '';
            $response ['calendar'] ['detail'] = $finalTimings;
        }
    }
    
    public function deals(&$response){       
        $userId = $this->getUserSession()->getUserId();
        $restaurantAccount = new \Restaurant\Model\RestaurantAccounts();
        $isRegisterRestaurant = $restaurantAccount->getRestaurantAccountDetail(array(
                'columns' => array(
                    'restaurant_id'
                ),
                'where' => array(
                    'restaurant_id' => $response['id'],
                    'status'=>1
                )
            ));
        
        if((isset($response['id']) && !empty($response['id']))){ 
            $restaurantFunctions = new RestaurantDetailsFunctions();
            $response['deals']=  array_values($restaurantFunctions->getDealsForRestaurant($response['id'],$userId));
        }
    }
    
    public function features(&$response){
        $resturantFeatures = new \Restaurant\Model\Feature();
        $resturantFeatures->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'f' => 'features'
            ),
            'on' => 'f.id = restaurant_features.feature_id',
            'columns' => array(
                'features' => 'features',
                'feature_type',
                'features_key'
            ),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'id'
            ),
            'joins' => $joins,
            'where' => array(
                'restaurant_features.status' => 1,
                'f.status' => 1,
                'restaurant_id' => $response['id']
            )
        );
        $features = $resturantFeatures->find($options)->toArray();
        $response ['features'] ['detail'] = $features;
    }

}
