<?php

namespace Restaurant;

use Bookmark\Model\FoodBookmark;
use Restaurant\Model\Calendar;
use MCommons\StaticOptions;
use Restaurant\Model\DealsCoupons;
use Bookmark\Model\RestaurantBookmark;
use Restaurant\Model\Menu;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class RestaurantDetailsFunctions {

    public static $_bookmark_types;
    public static $_isMobile;
    public $deliveryFinalTimings = array();
    public $reserveFinalTimings = array();
    public $restaurantName = '';
    public function extract_day_from_date($date) {
        $restaurant_day = array(
            'su',
            'mo',
            'tu',
            'we',
            'th',
            'fr',
            'sa'
        );
        if (empty($date))
            return '';
        $date = strtotime($date);
        $day = date('w', $date);
        return $restaurant_day [$day];
    }

    /*
     * This function will format the response
     */

    public static function formatResponse(&$menu_items) {
        if(!empty($menu_items)){
        foreach ($menu_items as &$menu_item) :
            if (isset($menu_item ['sub_categories']) && count($menu_item ['sub_categories'])) {
                unset($menu_item ['item_ordering_count']);
                unset($menu_item ['item_lovedit_count']);
                unset($menu_item ['item_image_url']);
                unset($menu_item ['friend_loveit']);
                if (isset($menu_item ['sub_categories'] [0] ['sub_categories']) && !count($menu_item ['sub_categories'] [0] ['sub_categories'])) {
                    foreach ($menu_item ['sub_categories'] as &$item) :
                        $item ['item_id'] = $item ['category_id'];
                        $item ['item_name'] = html_entity_decode(htmlspecialchars_decode($item ['category_name'], ENT_QUOTES));
                        unset($item ['sub_categories']);
                        unset($item ['category_items']);
                        unset($item ['category_id']);
                        unset($item ['category_name']);
                        $item ['item_desc'] = html_entity_decode(htmlspecialchars_decode($item ['category_desc'], ENT_QUOTES));
                        unset($item ['category_desc']);
                    endforeach;
                 
                    foreach($menu_item ['sub_categories'] as $imgkey => $valimg){
                            $itmimage [] = $valimg['item_image_url'];
                    }
                   @array_multisort($itmimage, SORT_DESC, $menu_item ['sub_categories']);
             
                    $menu_item ['category_items'] = $menu_item ['sub_categories'];                    
                    
                    $menu_item ['sub_categories'] = array();
                    $itmimage = array();
                }
                if (isset($menu_item ['item_price'])) {
                    unset($menu_item ['item_price']);
                }
            }
            if (isset($menu_item ['sub_categories']) && is_array($menu_item ['sub_categories'])) {
                self::formatResponse($menu_item ['sub_categories']);
            }
        endforeach;
        }
        return $menu_items;
    }

    /*
     * this function will generate nested tree menu from a array
     */

    public static function createNestedMenu(&$Menues, $restaurant_id) {
        $session = StaticOptions::getUserSession();
        $userId = $session->getUserId();
        $sl = StaticOptions::getServiceLocator();
        $socialProofing = $sl->get("Restaurant\Controller\MenuSocialProofingController");
        $foodbookmark = new FoodBookmark ();
        $map = array(
            0 => array(
                'items' => array()
            )
        );
        $isMobile = self::$_isMobile;
        foreach (self::$_bookmark_types as $type) {
            $bmdata [$type] = 0;
        }
        $priceArr = array();
        foreach ($Menues as &$Menu) {
            $Menu['category_name'] = html_entity_decode(htmlspecialchars_decode($Menu['category_name'], ENT_QUOTES));
            $Menu['category_desc'] = html_entity_decode(htmlspecialchars_decode($Menu['category_desc'], ENT_QUOTES));
            $Menu['category_name'] = strip_tags($Menu['category_name']);
            $Menu['category_desc'] = strip_tags($Menu['category_desc']);
            if (!empty($Menu ['price_id'])) {
                if (!isset($priceArr [$Menu ['category_id']])) {
                    $priceArr [$Menu ['category_id']] = array();
                }
                $priceArr [$Menu ['category_id']] [] = array(
                    'id' => $Menu ['price_id'],
                    'value' => $Menu ['price'],
                    'desc' => $Menu ['price_desc']
                );
                unset($Menu ['price_id']);
                unset($Menu ['price']);
                unset($Menu ['price_desc']);

                $bookmarkcount = $foodbookmark->getMenuBookmarkCount($restaurant_id, $Menu ['category_id']);

                if ($bookmarkcount) {
                    foreach ($bookmarkcount as $bdata) {
                        $key = $bdata ['type'];
                        $bmdata [$key] = $bdata ['total_count'];
                    }
                    $Menu ['total_love_count'] = (string) $bmdata ['lo'];
                    $Menu ['total_tryit_count'] = (string) $bmdata ['ti'];
                } else {
                    $Menu ['total_love_count'] = "0";
                    $Menu ['total_tryit_count'] = "0";
                }
//               
            }
            $Menu ['item_image_url'] = !$Menu ['item_image_url'] ? '' : $Menu ['item_image_url'];

            /*
             * Total bookmark count for menu
             */

            if (!$isMobile) {
                $Menu ['image_path'] = IMAGE_PATH . strtolower(@$Menu ['rest_code']) . "/" . THUMB . "/" . $Menu ['item_image_url'];
            }

            $Menu = array_intersect_key($Menu, array_flip(array(
                'category_id',
                'pid',
                'category_name',
                'category_desc',
                'item_image_url',
                'item_price',
                'total_love_count',
                'total_tryit_count',
                'sub_categories',
                'category_items',
                'online_order_allowed'
            )));

            $Menu ['sub_categories'] = array();
            $map [$Menu ['category_id']] = &$Menu;
            if ($userId) {
                $map [$Menu ['category_id']]['friend_loveit'] = $socialProofing->get($Menu ['category_id'])['action'];
            } else {
                $map [$Menu ['category_id']]['friend_loveit'] = '';
            }
        }

        foreach ($Menues as &$Menu) {
            if (isset($priceArr [$Menu ['category_id']])) {
                $Menu ['prices'] = $priceArr [$Menu ['category_id']];
            } else {
                $Menu ['prices'] = array();
            }
            $map [$Menu ['pid']] ['sub_categories'] [] = &$Menu;
        }

        return $map [0] ['sub_categories'];
    }

    public function adjustReserveTimings($timings) {
        $reserveFinalTimings = array();
        if (empty($timings)) {
            return array();
        }
        $timArray = explode(",", $timings);
        foreach ($timArray as $key => $tim) {
            $tim = trim($tim);
            $tim = explode('-', $tim);
            if (count($tim) != 2) {
                continue;
            }
            $dateTime = new \DateTime(trim($tim[0]));
            $open = $dateTime->format("H:i");

            $dateTime = new \DateTime(trim($tim[1]));
            $close = $dateTime->format("H:i");
            $tim = array(
                'open' => $open,
                'close' => $close
            );
            $timArray[$key] = $tim;
        }
        return $timArray;
    }

    /*
     * this function will generate nested tree menu from a array
     * 
     */

    public static function createWebNestedMenu(&$Menues, $restaurant_id) {
        $foodbookmark = new FoodBookmark ();
        $map = array(
            0 => array(
                'items' => array()
            )
        );
        $isMobile = self::$_isMobile;
        foreach (self::$_bookmark_types as $type) {
            $bmdata [$type] = 0;
        }

        $priceArr = array();
        foreach ($Menues as $key => &$Menu) {
            if (!empty($Menu ['price_id'])) {
                if (!isset($priceArr [$Menu ['category_id']])) {
                    $priceArr [$Menu ['category_id']] = array();
                }
                $priceArr [$Menu ['category_id']] [] = array(
                    'id' => $Menu ['price_id'],
                    'value' => $Menu ['price'],
                    'desc' => $Menu ['price_desc']
                );
                unset($Menu ['price_id']);
                unset($Menu ['price']);
                unset($Menu ['price_desc']);
            }
            $Menu ['item_image_url'] = !$Menu ['item_image_url'] ? '' : $Menu ['item_image_url'];
            $Menu = array_intersect_key($Menu, array_flip(array(
                'category_id',
                'pid',
                'category_name',
                'category_desc',
                'item_image_url',
                'item_price',
                'sub_categories',
                'category_items',
                'online_order_allowed',
                'restaurant_name', 'rest_code', 'cuisines_id'
            )));

            $Menu ['sub_categories'] = array();
            $map [$Menu ['category_id']] = &$Menu;
        }
        foreach ($Menues as &$Menu) {
            if (isset($priceArr [$Menu ['category_id']])) {
                $Menu ['prices'] = $priceArr [$Menu ['category_id']];
            } else {
                $Menu ['prices'] = array();
            }
            $map [$Menu ['pid']] ['sub_categories'] [] = &$Menu;
            }

        return $map [0] ['sub_categories'];
    }

    /*
     * this function identify that which one is last leaf of tested tree menu
     */

    public static function knowLastLeaf(&$menu_items) {
        if (!empty($menu_items)) {
            foreach ($menu_items as $keys => &$value) {
                unset($value ['pid']);
                if (is_array($value)) {
                    foreach ($value as $key => &$val) {
                        if ($key == 'sub_categories') {
                            if (!empty($val)) {
                                $menu_items [$keys] ['sub_categories'] = $val;
                                $menu_items [$keys] ['category_items'] = array();
                                unset($menu_items[$keys]['online_order_allowed']);
                                $val = array_values(array_map('unserialize', array_unique(array_map('serialize', $val))));
                                self::knowLastLeaf($val);
                            } else {
                                $menu_items [$keys] ['sub_categories'] = array();
                                $menu_items [$keys] ['category_items'] = $val;
                            }
                        }
                    }
                }
            }
            return $menu_items;
        }
    }

    public function converToTwelveHourFormat($time) {
        $timeInTwelveHourFormat = '';
        if ($time != null) {
            $time = new \DateTime($time);
            $timeInTwelveHourFormat = $time->format('g:i A');
        }
        return $timeInTwelveHourFormat;
    }

    public function getDealsForRestaurant($id = 0, $userid = 0, $isMobile = false) {
        $menu=new Menu();
        $foodbookmark= new FoodBookmark();
        $userDealsOnRestaurant = $this->userDealOnRestaurant($id, $isMobile,$userid);
        
        $restaurantDeals = $this->restaurantDeals($id, $isMobile);
        
        //$resSpecificDeal = $this->getDealsForSpecificRestaurant($id);
        
//        pr($userDealsOnRestaurant);
//        pr($restaurantDeals);
     
        $restaurntDeals = array_merge($restaurantDeals,$userDealsOnRestaurant);
//        pr($resSpecificDeal);
//        pr($restaurntDeals);

        foreach ($restaurntDeals as $key => $value) {
            if (!$value['menu_id'] && !$value['description'] && $isMobile) {
                if ($value['days']) {
                    $daysArr = explode(',', $value['days']);
                    $daysStr = "";
                    foreach ($daysArr as $keys => $val) {
                        $daysStr .= StaticOptions::$dayMapping[$val] . ", ";
                    }
                    $days = substr($daysStr, 0, -2);
                } else {
                    $days = "all days";
                }

                $restaurntDeals[$key]['description'] = "Get " . $value['title'] . ". The discount is automatically applied to your order total once it exceeds $" . $value['minimum_order_amount'] . ". Offer valid from " . date("M d, Y h:m A", strtotime($value['start_on'])) . " on " . $days . " (check restaurant’s open hours) only for online orders on Munch Ado.";
            }
            
            $restaurntDeals[$key]['dealtype'] = ($value['menu_id'] !== '' && $value['menu_id'] !== NULL) ? 'menu' : 'order';
            $restaurntDeals[$key]['condition'] = "*offer made available via email after verification";
            $restaurantDetailModel = new Model\Restaurant();
            $resDetails = $restaurantDetailModel->findRestaurant(array('where' => array('id' => $id)))->toArray();
            $iPath=USER_REVIEW_IMAGE.strtolower($resDetails['rest_code']).'/offer/';
            $restaurntDeals[$key]['offer_image']=$iPath.$value['offer_image'];
            $restaurntDeals[$key]['menuDetails']=[];
            if(($value['menu_id'] !== '' && $value['menu_id'] !== NULL) && $value['menu_id'] > 0){
               
              $prices= $menu->restaurantMenuesSpecificPrice($value['menu_id']);
              $bookmarkcount = $foodbookmark->getMenuBookmarkCount($id,$value['menu_id']);
              if ($bookmarkcount) {
                    foreach ($bookmarkcount as $bdata) {
                        $k = $bdata ['type'];
                        $bmdata [$k] = $bdata ['total_count'];                    
                    }
                    $r['love_count'] = isset($bmdata ['lo'])?(string)$bmdata ['lo']:'0';
                    $r['tried_count'] = isset($bmdata ['ti'])?(string)$bmdata ['ti']:'0';
                    $r['craving_count'] = isset($bmdata ['wi'])?(string)$bmdata ['wi']:'0';
                    $r['user_tried_it'] = false;
                    $r['user_loved_it'] = false;
                    $r['user_craving_it'] = false;
                    $r['bookmarks_loaded'] = false;
                } else {
                    $r['love_count'] ='0';
                    $r['tried_count'] ='0';
                    $r['craving_count'] ='0';
                    $r['bookmarks_loaded'] = false;
                    $r['user_tried_it'] = false;
                    $r['user_loved_it'] = false;
                    $r['user_craving_it'] = false;
                }
              $menuD=current($menu->menuesDetails(array(
                        'columns' => array(
                            'menu_id' => $value['menu_id']
                        )
                    ))); 
              $menuD['prices']=$prices;
              if(isset($menuD['user_deals']) && $menuD['user_deals']==2){
                 $restaurntDeals[$key]['dealtype']='dinein';   
              }
              $menuD=array_merge($menuD,$r);
              $restaurntDeals[$key]['menuDetails']=$menuD;
            }
            
            $restaurntDeals[$key]['trend'] = ($value['trend'] == NULL) ? 0 :(int) $value['trend'];
            $restaurntDeals[$key]['dine-more'] = ($value['user_deals'] == 0) ? false : true;
            $currentDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $value['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT);
            $currentDateTimeUnixTimeStamp = strtotime($currentDateTime);
            $dealsEndDateTimeUnixTimeStamp = strtotime($value['end_date']);
            $dealsStartDateTimeUnixTimeStamp = strtotime($value['start_on']);
            $dealsExpireDateTimeUnixTimeStamp = strtotime($value['expired_on']);
            if ($currentDateTimeUnixTimeStamp > $dealsEndDateTimeUnixTimeStamp || $currentDateTimeUnixTimeStamp > $dealsExpireDateTimeUnixTimeStamp || $dealsStartDateTimeUnixTimeStamp > $currentDateTimeUnixTimeStamp) {
                unset($restaurntDeals [$key]);
            }
//            if ($currentDateTime <= $endDate) {
//                if (isset($startDate) && $currentDateTime < $startDate) {
//                    unset($dealsCoupons [$key]);
//                }
//                continue;
//            }
//            unset($dealsCoupons [$key]);
        }
        
        //pr($restaurntDeals,1);
        return $restaurntDeals;
    }
    
   public function getDealsForSpecificRestaurant($id = 0) {
        
        $dealsCoupons = new DealsCoupons ();
        $select = new Select();
        $menu=new Menu();
        $foodbookmark= new FoodBookmark();
        $select->from('restaurant_deals_coupons');
        $select->columns(array(
            'id',
            'title',
            'type',
            'start_on',
            'end_date',
            'discount',
            'discount_type',
            'minimum_order_amount',
            'days',
            'slots',
            'description',
            'restaurant_id',
            'deal_for','trend','fine_print','offer_image'=>'image',
            'menu_id', 'user_deals', 'deal_used_type'
        ));
//        $select->join(array(
//            'ra' => 'restaurant_accounts'
//                ), 'ra.restaurant_id = restaurant_deals_coupons.restaurant_id', array('email'), $select::JOIN_INNER);
//        $select->join(array(
//            'ud' => 'user_deals'
//                ), 'ud.deal_id = restaurant_deals_coupons.id', array('user_id'), $select::JOIN_LEFT);
        $where = new Where();
        $where->NEST->in('restaurant_deals_coupons.restaurant_id', $id)->AND->equalTo('restaurant_deals_coupons.status', 1)->UNNEST->AND->NEST->isNull('restaurant_deals_coupons.user_deals')->OR->equalTo('restaurant_deals_coupons.user_deals', 0)->UNNEST;;

        $select->where($where);
        //pr($select->getSqlString($dealsCoupons->getPlatform('READ')),true);
        $dealsCoupons = $dealsCoupons->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        //pr($dealsCoupons,1);

        foreach ($dealsCoupons as $key => $value) {
            $dealsCoupons[$key]['dealtype'] = 'order';
            $dealsCoupons[$key]['condition'] = "";
//            $restaurantDetailModel = new Model\Restaurant();
//            $resDetails = $restaurantDetailModel->findRestaurant(array('where' => array('id' => $id)))->toArray();
//             $iPath=USER_REVIEW_IMAGE.strtolower($resDetails['rest_code']).'/offer/';
             $dealsCoupons[$key]['offer_image']="";
            $dealsCoupons[$key]['menuDetails']=[];
//            if(($value['menu_id'] !== '' && $value['menu_id'] !== NULL) && $value['menu_id'] > 0){
//               
//              $prices= $menu->restaurantMenuesSpecificPrice($value['menu_id']);
//              $bookmarkcount = $foodbookmark->getMenuBookmarkCount($id,$value['menu_id']);
//              if ($bookmarkcount) {
//                    foreach ($bookmarkcount as $bdata) {
//                        $k = $bdata ['type'];
//                        $bmdata [$k] = $bdata ['total_count'];                    
//                    }
//                    $r['love_count'] = isset($bmdata ['lo'])?(string)$bmdata ['lo']:'0';
//                    $r['tried_count'] = isset($bmdata ['ti'])?(string)$bmdata ['ti']:'0';
//                    $r['craving_count'] = isset($bmdata ['wi'])?(string)$bmdata ['wi']:'0';
//                    $r['user_tried_it'] = false;
//                    $r['user_loved_it'] = false;
//                    $r['user_craving_it'] = false;
//                    $r['bookmarks_loaded'] = false;
//                } else {
//                    $r['love_count'] ='0';
//                    $r['tried_count'] ='0';
//                    $r['craving_count'] ='0';
//                    $r['bookmarks_loaded'] = false;
//                    $r['user_tried_it'] = false;
//                    $r['user_loved_it'] = false;
//                    $r['user_craving_it'] = false;
//                }
//              $menuD=current($menu->menuesDetails(array(
//                        'columns' => array(
//                            'menu_id' => $value['menu_id']
//                        )
//                    ))); 
//              $menuD['prices']=$prices;
//              if($menuD['user_deals']==2){
//                 $dealsCoupons[$key]['dealtype']='dinein';   
//              }
//              $menuD=array_merge($menuD,$r);
//              $dealsCoupons[$key]['menuDetails']=$menuD;
//            }
            
            $dealsCoupons[$key]['trend'] = ($value['trend'] == NULL) ? 0 :(int) $value['trend'];
            $dealsCoupons[$key]['dine-more'] = ($value['user_deals'] == 0) ? false : true;
            $cityDateTime = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $value['restaurant_id']
                            ), StaticOptions::getDateTime()->format(StaticOptions::MYSQL_DATE_FORMAT), 'Y-m-d H:i:s');
            $endDate = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $id
                            ), $dealsCoupons [$key] ['end_date'], 'Y-m-d H:i:s');

            if ($cityDateTime <= $endDate) {
                continue;
            }
            unset($dealsCoupons [$key]);
        }
        return $dealsCoupons;
    }

    public function checkIfUserCravesForIt($restaurant_id) {
        $userId = StaticOptions::getUserSession()->getUserId();
        if ($userId) {
            $bookmarkModel = new RestaurantBookmark ();
            $options = array(
                'type' => 'wl',
                'user_id' => $userId,
                'restaurant_id' => $restaurant_id
            );
            $response = $bookmarkModel->isAlreadyBookmark($options);
            return (!empty($response)) ? true : false;
        }
    }

    public function getRestaurantDisplayTimings($restaurantId) {
        $date = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $restaurantId
                ))->format('Y-m-d H:i');
        $options = array(
            'columns' => array(
                'operation_hours' => 'operation_hrs_ft',
                'calendar_day',
            ),
            'where' => array(
                'restaurant_id' => $restaurantId,
                'status' => 1,
                'open_close_status > ?' => 1
            )
        );
        $calendar = new Calendar();
        $calendar->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $calendar->find($options)->toArray();
        $cals = StaticOptions::arrengeOrderOfCalendar($response);
        return $cals;
    }

    public function getRestaurantDisplayTimingsByDay($restaurantId, $day) {
        $date = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $restaurantId
                ))->format('Y-m-d H:i');
        $options = array(
            'columns' => array(
                'operation_hours' => 'operation_hrs_ft',
                'calendar_day',
                'open_time'
            ),
            'where' => array(
                'restaurant_id' => $restaurantId,
                'status' => 1,
                'calendar_day' => $day,
                'open_close_status > ?' => 1
            )
        );
        $calendar = new Calendar();
        $calendar->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $calendar->find($options)->toArray();
        return $response;
    }

    public static function formatResponseApi(&$menu_items) {
        $userFunctions = new \User\UserFunctions();
        foreach ($menu_items as &$menu_item) :
            if (isset($menu_item ['sub_categories']) && count($menu_item ['sub_categories'])) {

                unset($menu_item ['item_image_url']);

                if (isset($menu_item ['sub_categories'] [0] ['sub_categories']) && !count($menu_item ['sub_categories'] [0] ['sub_categories'])) {
                    foreach ($menu_item ['sub_categories'] as &$item) :
                        $item ['item_id'] = $item ['category_id'];
                        $item ['item_name'] = html_entity_decode(htmlspecialchars_decode($item ['category_name'], ENT_QUOTES));
                        $desc = html_entity_decode(htmlspecialchars_decode($item ['category_desc'], ENT_QUOTES));
                        $item ['item_desc'] = $desc;
                        $item ['item_name'] = html_entity_decode(htmlspecialchars_decode(strip_tags($item ['item_name']), ENT_QUOTES));
                        $item ['item_desc'] = html_entity_decode(htmlspecialchars_decode(strip_tags($item ['item_desc']), ENT_QUOTES));
                        unset($item ['sub_categories']);
                        unset($item ['category_items']);
                        unset($item ['category_id']);
                        unset($item ['category_name']);
                        unset($item ['category_desc']);
                    endforeach;

                    $menu_item ['category_items'] = $menu_item ['sub_categories'];
                    $menu_item ['sub_categories'] = array();
                }
                if (isset($menu_item ['item_price'])) {
                    unset($menu_item ['item_price']);
                }
            }

            if (isset($menu_item ['sub_categories']) && is_array($menu_item ['sub_categories'])) {
                self::formatResponseApi($menu_item ['sub_categories']);
            }
        endforeach;
        return $menu_items;
    }

    public function checkUserBookmarked($restaurant_id, $type) {
        $userId = StaticOptions::getUserSession()->getUserId();
        if ($userId) {
            $bookmarkModel = new RestaurantBookmark ();
            $options = array(
                'type' => $type,
                'user_id' => $userId,
                'restaurant_id' => $restaurant_id
            );
            $response = $bookmarkModel->isAlreadyBookmark($options);
            return (!empty($response)) ? true : false;
        } else {
            return false;
        }
    }

    public function isRestaurantOpenTwentyFourHours($restaurantId = false) {

        if ($restaurantId) {
            $features = new Model\Feature();
            $joins = array();
            $joins [] = array(
                'name' => array(
                    'f' => 'features'
                ),
                'on' => 'f.id = restaurant_features.feature_id',
                'columns' => array(
                    'features'
                ),
                'type' => 'inner'
            );
            $options = array(
                'columns' => array('feature_id'),
                'where' => array(
                    'f.status' => 1,
                    'restaurant_id' => $restaurantId,
                    'f.features' => 'Open 24 hours'
                ),
                'joins' => $joins
            );
            $isOpenTwentyFourHours = $features->find($options)->toArray();
            if ($isOpenTwentyFourHours) {
                return true;
            }
        }
        return false;
    }

    public function formatDeliveryGeo($deliveryGeo = false) {
        //get polygon value and implement the logic
        if (!empty($deliveryGeo)) {
            $polygonData = str_replace('POLYGON((', '', $deliveryGeo);
            $polygonData = str_replace('))', '', $polygonData);
            $polygonData = trim($polygonData);
            $polygonData = explode(',', $polygonData);
            foreach ($polygonData as $key => $polyValue) {
                $getExplode = explode(' ', trim($polyValue));
                if (count($getExplode) > 0) {
                    $response[$key]['longitude'] = $getExplode[0];
                    $response[$key]['latitude'] = $getExplode[1];
                }
            }
        } else {
            $response = array();
        }
        return $response;
    }

    public static function getRestaurantOpenAt($restaurantId, $restaurntDate, $restaurantOpenAt = false) {
        $resOpenAt = '';
        $calendar = new Calendar();
        $isRestaurantOpen = $calendar->isRestaurantOpen($restaurantId);

        if ($isRestaurantOpen) {
            $resOpenAt = "1"; //open right now
        } elseif ($restaurantOpenAt) {
            $resOpenAtTimeStamp = strtotime($restaurantOpenAt);
            $resDateTimeStamp = strtotime($restaurntDate);

            $date1 = new \DateTime(date("Y-m-d H:i", $resOpenAtTimeStamp));
            $date2 = new \DateTime(date("Y-m-d H:i", $resDateTimeStamp));
            $interval = $date1->diff($date2);

            if ($interval->invert == 1) {
                if ($interval->format('%d') > 1) {
                    $resOpenAt = date('Y-m-d h:i A', $resOpenAtTimeStamp);
                } elseif ($interval->format('%d') == 1) {
                    $resOpenAt = "Tomorrow " . date('h:i A', $resOpenAtTimeStamp);
                } elseif ($interval->format('%h') > 12 && $interval->format('%h') < 24) {
                    $resOpenAt = "Tomorrow " . date('h:i A', $resOpenAtTimeStamp);
                } else {
                    $resOpenAt = date('h:i A', $resOpenAtTimeStamp);
                }
            }
        } else {
            $resOpenAt = "0"; //close for today
        }
        return $resOpenAt;
    }
    
    public function restaurantAddress($restaurantId){
        $restaurantAddress = "";
        $joins = [];
        $restaurantModel = new Model\Restaurant();
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
         $options = array(
            'columns' => array(
                'id',
                'name' => 'restaurant_name',
                'city_id',
                'address',
                'zipcode',
                'res_code' => 'rest_code',                
            ),
            'joins' => $joins,
            'where' => array(
                'restaurants.id' => $restaurantId,
                'restaurants.inactive'=>0,
                'restaurants.closed'=>0
            )
        );
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $restaurantDetails = $restaurantModel->find($options)->toArray();
        if($restaurantDetails){
          $restaurantAddress =  $restaurantDetails[0]['address'].", ".$restaurantDetails[0]['city'].", ".$restaurantDetails[0]['zipcode'];
          $this->restaurantName = $restaurantDetails[0]['name'];
        }
        return $restaurantAddress;
    }
    
    public function restaurantDeals($id,$isMobile){
         if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
        }else{
            $id=array($id);    
        }
        $dealsCoupons = new DealsCoupons ();
        $select = new Select();        
        $select->from('restaurant_deals_coupons');
        $select->columns(array(
            'id',
            'title',
            'type',
            'start_on',
            'end_date',
            'expired_on',
            'discount',
            'discount_type',
            'minimum_order_amount',
            'days',
            'slots',
            'description',
            'restaurant_id',
            'deal_for','trend','fine_print','offer_image'=>'image',
            'menu_id', 'user_deals', 'deal_used_type'
        ));
        $select->join(array(
            'ra' => 'restaurant_accounts'
                ), 'ra.restaurant_id = restaurant_deals_coupons.restaurant_id', array('email'), $select::JOIN_INNER);
        $where = new Where();
       
        if($isMobile){
            $where->NEST->in('restaurant_deals_coupons.restaurant_id', $id)->AND->isNull('restaurant_deals_coupons.menu_id')->AND->equalTo('restaurant_deals_coupons.status', 1)->UNNEST->NEST->AND->lessThan('restaurant_deals_coupons.trend', 7)->OR->isNull('restaurant_deals_coupons.trend')->UNNEST->NEST->AND->equalTo('ra.status', 1)->UNNEST->NEST->AND->equalTo('user_deals',0)->UNNEST;
        }else{
            $where->NEST->in('restaurant_deals_coupons.restaurant_id', $id)->AND->equalTo('restaurant_deals_coupons.status', 1)->UNNEST->NEST->AND->equalTo('ra.status', 1)->UNNEST->NEST->AND->equalTo('user_deals',0)->UNNEST;
        }

        $select->where($where);
        //print_r($select->getSqlString($dealsCoupons->getPlatform('READ')));
        $rd = $dealsCoupons->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $rd;
    }
    
    public function userDealOnRestaurant($id,$isMobile,$userid){
        if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
        }else{
            $id=array($id);    
        }
        $dealsCoupons = new DealsCoupons ();
        $select = new Select();
        
        $select->from('restaurant_deals_coupons');
        $select->columns(array(
            'id',
            'title',
            'type',
            'start_on',
            'end_date',
            'expired_on',
            'discount',
            'discount_type',
            'minimum_order_amount',
            'days',
            'slots',
            'description',
            'restaurant_id',
            'deal_for','trend','fine_print','offer_image'=>'image',
            'menu_id', 'user_deals', 'deal_used_type'
        ));
        $select->join(array(
            'ra' => 'restaurant_accounts'
                ), 'ra.restaurant_id = restaurant_deals_coupons.restaurant_id', array('email'), $select::JOIN_INNER);
        $select->join(array(
            'ud' => 'user_deals'
                ), 'ud.deal_id = restaurant_deals_coupons.id', array('user_id'), $select::JOIN_INNER);
        $where = new Where();
        
        if($isMobile){
            $where->NEST->in(
                    'restaurant_deals_coupons.restaurant_id', $id
                    )->AND->isNull(
                            'restaurant_deals_coupons.menu_id'
                            )->AND->equalTo(
                                    'restaurant_deals_coupons.status', 1
                                    )->AND->equalTo("user_deals", 1)->UNNEST->NEST->AND->lessThan(
                                            'restaurant_deals_coupons.trend', 7
                                            )->OR->isNull(
                                                    'restaurant_deals_coupons.trend'
                                                    )->UNNEST->NEST->AND->equalTo(
                                                            'ra.status', 1
                                                            )->UNNEST->NEST->AND->equalTo(
                                                                    'ud.availed', 0
                                                                    )->UNNEST->AND->NEST->isNull(
                                                                            'ud.user_id'
                                                                            )->OR->equalTo('ud.user_id', $userid)->UNNEST;
        }else{
            $where->NEST->in(
                    'restaurant_deals_coupons.restaurant_id', $id
                    )->AND->equalTo('restaurant_deals_coupons.status', 1
                            )->UNNEST->NEST->AND->equalTo('ra.status', 1
                                    )->UNNEST->NEST->AND->equalTo('ud.availed', 0
                                            )->UNNEST->AND->NEST->isNull('ud.user_id'
                                                    )->OR->equalTo('ud.user_id', 0
                                                            )->OR->equalTo('ud.user_id', $userid)->UNNEST;
        }

        $select->where($where);
        //print_r($select->getSqlString($dealsCoupons->getPlatform('READ')));
        $ud = $dealsCoupons->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $ud;
    }

}
