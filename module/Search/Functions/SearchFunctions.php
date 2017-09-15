<?php

namespace Search;

use Restaurant\Model\MenuBookmark;
use Restaurant\Model\RestaurantBookmark;
use MCommons\StaticOptions;
use Restaurant\Model\Calendar;
use User\Model\UserReview;
use Restaurant\Model\RestaurantReview;
use Solr\SearchHelpers;
use Search\CityDeliveryCheck;
use Restaurant\Model\Restaurant;

class SearchFunctions {

    //const TYPEOFPLACE = 'Type of Place';

    private static $arrDateMappingThreeChar = array(
        "Sun" => "su",
        "Mon" => "mo",
        "Tues" => "tu",
        "Tue" => "tu",
        "Wed" => "we",
        "Thu" => "th",
        "Thur" => "th",
        "Fri" => "fr",
        "Sat" => "sa"
    );
    private static $nextDay2Char = array(
        "su" => "mo",
        "mo" => "tu",
        "tu" => "we",
        "we" => "th",
        "th" => "fr",
        "fr" => "sa",
        "sa" => "su"
    );

    public function addAndFormatFoodData(&$response, $input) {
        
        $deliver_flag = ($input['at'] == 'street') ? true : false;
        //vd($input,true);
        
        $user_lat = $input['lat'];
        $user_lng = $input['lng'];
        
        $totalData = count($response ['data']);
        //$response ['has_deals'] = 0;
        for ($i = 0; $i < $totalData; $i ++) {
            //$response ['data'][$i]['deals']="[]";
            $response ['data'] [$i] ['tags_fct'] = isset($response ['data'] [$i] ['tags_fct']) ? $response ['data'] [$i] ['tags_fct'] : [];
            $response ['data'] [$i] ['can_deliver'] = 1;
            $response ['data'] [$i] ['res_cod'] = 1;
            if ($deliver_flag) {//update if this needs to be updated
                $response ['data'] [$i] ['can_deliver'] = (int) CityDeliveryCheck::canDeliver($response ['data'] [$i]['res_id'], $user_lat, $user_lng);
            }
            $response ['data'] [$i] ['distance'] = round($response ['data'] [$i] ['distance'], 2);
            $menu_id = $response ['data'][$i]['menu_id'];
            if (isset($response ['highlight'][$menu_id]['res_cuisine'])) {
                $response ['data'] [$i] ['res_cuisine'] = $response ['highlight'][$menu_id]['res_cuisine'][0];
            } else if (isset($response ['highlight'][$menu_id]['menu_cuisine'])) {
                $response ['data'] [$i] ['res_cuisine'] = $response ['highlight'][$menu_id]['menu_cuisine'][0];
            } else if (isset($response ['highlight'][$menu_id]['feature_name'])) {
                $response ['data'] [$i] ['res_cuisine'] = $response ['highlight'][$menu_id]['feature_name'][0];
            }
            if (isset($response ['highlight'][$menu_id]['menu_item_desc'])) {
                $response ['data'] [$i] ['menu_item_desc'] = $response ['highlight'][$menu_id]['menu_item_desc'][0];
            }
            
            /*
             * aor=1 if Accept Online Orders else 0
             *ordering_enabled = 1 iff ($doc['accept_cc_phone']) && ($doc['res_delivery'] || $doc['res_takeout'] || $doc['res_reservations'])
            */
            $response ['data'] [$i] ['aor'] = $response ['data'] [$i]['ordering_enabled'];
            //acp =1 if registered restaurants or with some tag
            $acp = ($response ['data'] [$i]['is_registered'] == 1) || (count($response ['data'] [$i]['tags_fct']) > 0);
            $response ['data'] [$i] ['acp'] = ($acp) ? 1 : 0;

            $calender = new Calendar ();
            $isRestaurantOpen = $calender->isRestaurantOpen($response ['data'] [$i] ['res_id']);
            $response ['data'] [$i] ['is_currently_open'] = $isRestaurantOpen;
        }
        
        if(isset($response ['highlight'])){
            unset($response ['highlight']);
        }
    }

    public function addAndFormatRestData(&$response, $input) {
        $deliver_flag = ($input['at'] == 'street') ? true : false;
        //vd($input,true);
        
        $user_lat = $input['lat'];
        $user_lng = $input['lng'];
        
        // Restaurant open and close time
        $calender = new Calendar ();
        $totalData = count($response ['data']);
        for ($i = 0; $i < $totalData; $i ++) {
//            $response ['data'] [$i] ['has_deals'] = 0;
//            $response ['data'] [$i] ['deals'] = "";
            $response ['data'] [$i] ['tags_fct'] = isset($response ['data'] [$i] ['tags_fct']) ? $response ['data'] [$i] ['tags_fct'] : [];
            $response ['data'] [$i] ['can_deliver'] = 1;
            $response ['data'] [$i] ['res_cod'] = 1;
            if ($deliver_flag) {//update if this needs to be updated
                $response ['data'] [$i] ['can_deliver'] = (int) CityDeliveryCheck::canDeliver($response ['data'] [$i]['res_id'], $user_lat, $user_lng);
            }
            if (isset($response ['data'] [$i] ['distance'])) {
                $response ['data'] [$i] ['distance'] = round($response ['data'] [$i] ['distance'], 2);
            }
            $res_code = $response ['data'][$i]['res_code'];
            if (isset($response ['highlight'][$res_code]['res_cuisine'])) {
                $response ['data'] [$i] ['res_cuisine'] = $response ['highlight'][$res_code]['res_cuisine'][0];
            }
            //fill res_description
            $res_description = '';
            if (isset($response ['highlight'][$res_code]['res_menu'])) {
                $res_description .= $response ['highlight'][$res_code]['res_menu'][0];
            }
            if (isset($response ['highlight'][$res_code]['feature_name'])) {
                $res_description .= '... ' . $response ['highlight'][$res_code]['feature_name'][0];
            }
            if (isset($response ['highlight'][$res_code]['res_description'])) {
                $res_description .= '... ' . $response ['highlight'][$res_code]['res_description'][0];
            }
            //echo $res_description;die;
            if ($res_description != '') {
                if (isset($response ['highlight'][$res_code]['res_description'])) {
                    $res_description .= '... ' . $response ['highlight'][$res_code]['res_description'][0];
                }
                $res_description .= '... ' . $response ['data'] [$i] ['res_description'];
                $response ['data'] [$i] ['res_description'] = substr(utf8_encode($res_description), 0, 250);
            }

            //check if the restaurant is currently open. if not add next open time
            $open_now = $calender->isRestaurantOpen($response ['data'] [$i] ['res_id']);
            //vd($open_now,1);
            $response ['data'] [$i] ['is_currently_open'] = $open_now;
            if (!$open_now && isset($response ['data'] [$i]['oh_ft'])) {
                $response ['data'] [$i] ['opens_at'] = self::getNextOpenTime($response ['data'] [$i]['oh_ft'], $input);
            } else {
                $response ['data'] [$i] ['opens_at'] = '';
            }

            // deal price
            if (!empty($response ['data'] [$i] ['deal_price'])) {
                if ($response ['data'] [$i] ['deal_discount_type'] == 'f') {
                    $response ['data'] [$i] ['deal_price_after_discount'] = $response ['data'] [$i] ['deal_price'] - $response ['data'] [$i] ['deal_discount'];
                } else {
                    $response ['data'] [$i] ['deal_price_after_discount'] = $response ['data'] [$i] ['deal_price'] - (($response ['data'] [$i] ['deal_price'] * $response ['data'] [$i] ['deal_discount']) / 100);
                }
            } else {
                $response ['data'] [$i] ['deal_price_after_discount'] = "";
            }
            
            /*
             * aor=1 if Accept Online Orders else 0
             *ordering_enabled = 1 iff ($doc['accept_cc_phone']) && ($doc['res_delivery'] || $doc['res_takeout'] || $doc['res_reservations'])
            */
            $response ['data'] [$i] ['aor'] = $response ['data'] [$i]['ordering_enabled'];
            
            //acp =1 if registered restaurants or with some tag
            $acp = ($response ['data'] [$i]['is_registered'] == 1) || (count($response ['data'] [$i]['tags_fct']) > 0);
            $response ['data'] [$i] ['acp'] = ($acp) ? 1 : 0;
        }

        if (isset($response ['highlight'])) {
            unset($response ['highlight']);
        }
    }

    /**
     * 
     * @param string $oh_ft operating hours as in ft sheet
     * @param array $input must have keys day and curr_time
     * @return string
     */
    public static function getNextOpenTime($oh_ft, $input) {
        if ($oh_ft == "") {
            return "";
        }
        //$input['time'] = 2230; //sample data
        //flag to check if next open time has been found
        $found = false;

        try {
            $ohft_arr = explode('$', $oh_ft);
            $day = $input['day'];
            $count = -1; // used in adding days when restaurant next opens
            while (!$found && $count < 7) {
                $count++; // how many times
                $match = preg_grep('/' . $day . '/', $ohft_arr);

                //length of $match = 1
                foreach ($match as $value) {
                    $tmp = explode('|', $value); //$value = fr|11:00 AM - 01:00 PM, 02:00 PM - 11:00  PM
                    $times = explode(',', $tmp[1]);
                    $expTime = 0;
                    foreach ($times as $time) {//$time = 11:00 AM - 01:00 PM
                        preg_match_all("/\d+:\d+\s*[APM]+/", $time, $pat_array);

                        if (isset($pat_array[0][0])) {
                            $timestring = $pat_array[0][0];
                            if (preg_match('/AM/', $pat_array[0][0])) {
                                if ($count == 0) {
                                    $expTime = (int) str_replace(':', '', $timestring);
                                    if ($input['curr_time'] <= $expTime) {
                                        $found = true;
                                        $nextOpenTime = $timestring;
                                        break;
                                    }
                                } else {
                                    $found = true;
                                    $nextOpenTime = $timestring;
                                    break;
                                }
                            } elseif (preg_match('/PM/', $pat_array[0][0])) {
                                if ($count == 0) {
                                    $expTime = (int) str_replace(':', '', $timestring) + 1200;
                                    if ($input['curr_time'] <= $expTime) {
                                        $found = true;
                                        $nextOpenTime = $timestring;
                                        break;
                                    }
                                } else {
                                    $found = true;
                                    $nextOpenTime = $timestring;
                                    break;
                                }
                            }
                        }
                        //print_r($timestring);die;
                    }//end inner foreach
                }//end out foreach
                $day = self::$nextDay2Char[$day];
            }//end while loop
        } catch (\Exception $e) {
            
        }

        $opens_at = '';
        if ($found) {
            if($count == 1){
                $opens_at .= 'Tomorrow';
            } elseif ($count > 1) {
                $opens_at .= date('M d', strtotime($input['curr_date'] . ' + ' . $count . ' day'));
            }
            $opens_at .= " at " . $nextOpenTime;
        }
        return $opens_at;
    }

    public static function formatCuisineCount($response) {
        $counters = array();
        if (isset($response ['url'])) {
            $counters ['url'] = $response ['url'];
        }
        if (isset($response ['facet_data'] ['cuisine_id'])) {
            foreach ($response ['facet_data'] ['cuisine_id'] as $key => $val) {
                //$counters ['cuisines'] [] = array('id' => $key, 'count' => $val);
                $tmp = explode("##",$val);
                if( count($tmp) > 1){
                    $counters ['cuisines'] [] = array('id' => $key, 'count' => $tmp[0],'name'=>$tmp[1]);
                }else{
                    $counters ['cuisines'] [] = array('id' => $key, 'count' => 0,'name'=>$tmp[0]);
                }
            }
        }
        if (isset($response ['facet_data'] ['feature_id'])) {
            foreach ($response ['facet_data'] ['feature_id'] as $key => $val) {
                //$counters ['features'] [] = array('id' => $key, 'count' => $val);
                $tmp = explode("##",$val);
                if( count($tmp) > 1){
                $counters ['features'] [] = array('id' => $key, 'count' => $tmp[0],'name'=>$tmp[1]);
                }else {
                    $counters ['features'] [] = array('id' => $key, 'count' => 0,'name'=>$tmp[0]);
                }
            }
        }
        return $counters;
    }

    public static function getBookmarks($rest, $type) {
        //$items_array = explode(",", $items);
        $session = StaticOptions::getUserSession();
        $userReviewModel = new UserReview();
        $resreviewModel = new RestaurantReview();
        $isLoggedIn = $session->isLoggedIn();
        $currentUserId = $session->getUserId();
        $bookmarks = array();
        switch ($type) {
            case 'restaurant' :
                $restaurantBookmarkModel = new RestaurantBookmark ();
                //foreach ($items_array as $rest) {
                    $bookmark = array();
                    $bookmark = $restaurantBookmarkModel->getRestaurantBookmarkCount($rest);
                    $resUserReview = $userReviewModel->getRestaurantReviewCount($rest);
                    $resReview = $resreviewModel->getRestaurantReviewCount($rest);
                    $totalReview = $resReview['total_count'] + $resUserReview['total_count'];
                    if (!empty($bookmark)) {
                        foreach ($bookmark as $bkey => $bitem) {
                            switch ($bitem ['type']) {
                                case 'bt' :
                                    $bookmark ['been_count'] = $bitem ['total_count'];
                                    break;
                                case 'lo' :
                                    $bookmark ['love_count'] = $bitem ['total_count'];
                                    break;
                                /* case 're' :
                                  $bookmark ['review_count'] = $bitem ['total_count'];
                                  break; */
                            }
                            unset($bookmark [$bkey]);
                        }
                    } else {
                        $bookmark = array(
                            'been_count' => "0",
                            'love_count' => "0",
                                //'review_count' => "0"
                        );
                    }
                    $bookmark['review_count'] = $totalReview;
                    if (!isset($bookmark ['been_count'])) {
                        $bookmark ['been_count'] = "0";
                    }
                    if (!isset($bookmark ['love_count'])) {
                        $bookmark ['love_count'] = "0";
                    }
                    if (!isset($bookmark ['review_count'])) {
                        $bookmark ['review_count'] = "0";
                    }
                    if ($isLoggedIn) {
                        $userBookmarks = $restaurantBookmarkModel->getRestaurantBookmarksByUserId($rest, $session->getUserId());
                        $bookmark ['user_been_there'] = isset($userBookmarks ['bt']) && (int) $userBookmarks ['bt'] ? true : false;
                        $bookmark ['user_loved_it'] = isset($userBookmarks ['lo']) && (int) $userBookmarks ['lo'] ? true : false;
                        $bookmark ['user_reviewed_it'] = isset($userBookmarks ['re']) && (int) $userBookmarks ['re'] ? true : false;
                    } else {
                        $bookmark ['user_been_there'] = false;
                        $bookmark ['user_loved_it'] = false;
                        $bookmark ['user_reviewed_it'] = false;
                    }
                    $bookmark ['restaurant_id'] = $rest;
                    //$bookmarks [] = $bookmark;
                //}
                break;
            case 'food' :
                $menuBookmarkModel = new MenuBookmark ();
                //foreach ($items_array as $food) {
                    $bookmark = array();
                    // menu bookmark
                    $bookmark = $menuBookmarkModel->menuBookmarksCounts(array(
                                'columns' => array(
                                    'menu_id' => $rest
                                )
                            ))->toArray();
                    if (!empty($bookmark)) {
                        foreach ($bookmark as $bkey => $bitem) {
                            switch ($bitem ['type']) {
                                case 'wi' :
                                    $bookmark ['craving_count'] = $bitem ['total_count'];
                                    break;
                                case 'lo' :
                                    $bookmark ['love_count'] = $bitem ['total_count'];
                                    break;
                                case 'ti' :
                                    $bookmark ['tried_count'] = $bitem ['total_count'];
                                    break;
                            }
                            unset($bookmark [$bkey]);
                        }
                    } else {
                        $bookmark ['craving_count'] = "0";
                        $bookmark ['love_count'] = "0";
                        $bookmark ['tried_count'] = "0";
                    }
                    if (!isset($bookmark ['craving_count'])) {
                        $bookmark ['craving_count'] = "0";
                    }
                    if (!isset($bookmark ['love_count'])) {
                        $bookmark ['love_count'] = "0";
                    }
                    if (!isset($bookmark ['tried_count'])) {
                        $bookmark ['tried_count'] = "0";
                    }
                    // menu user bookmark
                    if ($isLoggedIn) {
                        $bookmark_user = $menuBookmarkModel->getMenuBookmarksByUserId($rest, $session->getUserId());
                        $bookmark ['user_craving_it'] = isset($bookmark_user ['wi']) && (int) $bookmark_user ['wi'] ? true : false;
                        $bookmark ['user_loved_it'] = isset($bookmark_user ['lo']) && (int) $bookmark_user ['lo'] ? true : false;
                        $bookmark ['user_tried_it'] = isset($bookmark_user ['ti']) && (int) $bookmark_user ['ti'] ? true : false;
                    } else {
                        $bookmark ['user_craving_it'] = false;
                        $bookmark ['user_loved_it'] = false;
                        $bookmark ['user_tried_it'] = false;
                    }
                    $bookmark ['menu_id'] = $rest;
                    //$bookmarks [] = $bookmark;
                //}
                break;
            default :
                throw new \Exception("Invalid request", 400);
        }
        return $bookmark;
    }
    
    // TODO : to be removed when solr gets done
    public static function mapRequestKeys($input) {
        $params = array();
        $map = array(
            'p' => array(
                'decode' => 'price',
                'values' => array()
            ),
            'date' => array(
                'decode' => 'sdate',
                'values' => array()
            ),
            'time' => array(
                'decode' => 'stime',
                'values' => array()
            ),
            'd' => array(
                'decode' => 'deals',
                'values' => array()
            ),
            'srb' => array(
                'decode' => 'sort_by',
                'values' => array(
                    'r' => 'relevancy',
                    'p' => 'price',
                    'd' => 'distance',
                    'md' => 'min_delivery',
                    'pop' => 'popularity'
                )
            ),
            'rrt' => array(
                'decode' => 'rrt',
                'values' => array(
                    'b' => 'breakfast',
                    'l' => 'lunch',
                    'd' => 'dinner'
                )
            ),
            'in' => array(
                'decode' => 'av',
                'values' => array()
            ),
            'srt' => array(
                'decode' => 'sort_type',
                'values' => array(
                    'a' => 'asc',
                    'd' => 'desc'
                )
            ),
            'st' => array(
                'decode' => 'st',
                'values' => array(
                    'd' => 'discover',
                    'r' => 'reserve',
                    'o' => 'order'
                )
            ),
            'sst' => array(
                'decode' => 'sst',
                'values' => array(
                    'de' => 'deliver',
                    't' => 'takeout',
                    'a' => 'all',
                    'di' => 'dinein',
                    'r' => 'reservation'
                )
            ),
            'vt' => array(
                'decode' => 'ovt',
                'values' => array(
                    'r' => 'restaurant',
                    'f' => 'food',
                    's' => 'restaurant'
                )
            ),
            'at' => array(
                'decode' => 'at',
                'values' => array(
                    'a' => 'address',
                    's' => 'street',
                    'n' => 'nbd',
                    'z' => 'zip',
                    'c' => 'city'
                )
            ),
            'q' => array(
                'decode' => 'fq',
                'value' => array()
            ),
            'dt' => array(
                'decode' => 'fdt',
                'values' => array(
                    'ft' => 'ft',
                    'c' => 'cui',
                    'fav' => 'fav',
                    'p' => 'pref',
                    't' => 'top',
                    'fe' => 'feat',
                    'a' => 'amb',
                    'r' => 'r',
                    'fo' => 'food',
                    'ta' => 'tags'
                )
            ),
            'sdt' => array(
                'decode' => 'sdt',
                'values' => array(
                    'ft' => 'ft',
                    'c' => 'cui',
                    'fav' => 'fav',
                    'p' => 'pref',
                    't' => 'top',
                    'fe' => 'feat',
                    'a' => 'amb',
                    'r' => 'r',
                    'fo' => 'food',
                    'tag' => 'tag'
                )
            )
        );
        foreach ($input as $pkey => $param) {
            if (array_key_exists($pkey, $map)) {
                $params [$map [$pkey] ['decode']] = urldecode($param);
                if (!empty($map [$pkey] ['values']) && !empty($param)) {
                    if (strpos($param, "||")) {
                        $paramArray = explode("||", $param);
                        $subParamArray = array();
                        foreach ($paramArray as $subKey => $subValue) {
                            $subParamArray [] = urldecode($map [$pkey] ['values'] [$subValue]);
                        }
                        $params [$map [$pkey] ['decode']] = implode("||", $subParamArray);
                    } else {
                        $params [$map [$pkey] ['decode']] = urldecode($map [$pkey] ['values'] [$param]);
                    }
                }
            } else {
                $params [$pkey] = urldecode($param);
            }
        }
        if (isset($params ['page'])) {
            $params ['page'] = $params ['page'] - 1;
            if ($params ['page'] == 0) {
                $params ['start'] = 0;
            } else {
                $params ['start'] = (int) $params ['page'] * 12;
            }
            $params ['rows'] = 12;
        }
        if (isset($params ['pageSlrRes'])) {
            $params ['page'] = $params ['pageSlrRes'] - 1;
            if ($params ['page'] == 0) {
                $params ['start'] = 0;
            } else {
                $params ['start'] = (int) $params ['page'] * 200;
            }
            $params ['rows'] = 200;
        }
        return $params;
    }
    
    /**
     * Mobile API Only: Used for adding extra fields in search response
     * @param array $response
     */
    public function updateResDataMob(&$response, $request) {
        $deliver_flag = ($request['at'] == 'street') ? true : false;
        $latlong = explode(',', $request['latlong']);
        $user_lat = $latlong[0]; 
        $user_lng = $latlong[1];
        // Restaurant open and close time
        $calender = new Calendar ();
        $totalData = count($response ['data']);
        for ($i = 0; $i < $totalData; $i ++) {
            $response ['data'] [$i] ['tags_fct'] = isset($response ['data'] [$i] ['tags_fct']) ? $response ['data'] [$i] ['tags_fct'] : [];
            $response ['data'] [$i] ['res_name'] = html_entity_decode($response ['data'] [$i] ['res_name']);
            $response ['data'] [$i] ['can_deliver'] = 1;
            if ($deliver_flag) {//update if this needs to be updated
                $response ['data'] [$i] ['can_deliver'] = (int) CityDeliveryCheck::canDeliver($response ['data'] [$i]['res_id'], $user_lat, $user_lng);
            }
            if (isset($response ['data'] [$i] ['distance'])) {
                $response ['data'] [$i] ['distance'] = round($response ['data'] [$i] ['distance'], 2);
            }
            $res_code = $response ['data'][$i]['res_code'];
            if (isset($response ['highlight'][$res_code]['res_cuisine'])) {
                $response ['data'] [$i] ['res_cuisine'] = $response ['highlight'][$res_code]['res_cuisine'][0];
            }
            //fill res_description
            $res_description = '';
            if (isset($response ['highlight'][$res_code]['res_menu'])) {
                $res_description .= $response ['highlight'][$res_code]['res_menu'][0];
            }
            if (isset($response ['highlight'][$res_code]['feature_name'])) {
                $res_description .= '... ' . $response ['highlight'][$res_code]['feature_name'][0];
            }
            if (isset($response ['highlight'][$res_code]['res_description'])) {
                $res_description .= '... ' . $response ['highlight'][$res_code]['res_description'][0];
            }
            //echo $res_description;die;
            if ($res_description != '') {
                if (isset($response ['highlight'][$res_code]['res_description'])) {
                    $res_description .= '... ' . $response ['highlight'][$res_code]['res_description'][0];
                }
                $res_description .= '... ' . $response ['data'] [$i] ['res_description'];
                $response ['data'] [$i] ['res_description'] = substr($res_description, 0, 250);
            }
            $response ['data'] [$i] ['res_description'] = html_entity_decode($response ['data'] [$i] ['res_description']);

            //check if the restaurant is currently open. if not add next open time
            $open_now = $calender->isRestaurantOpen($response ['data'] [$i] ['res_id']);
            $response ['data'] [$i] ['is_currently_open'] = $open_now;
            if (!$open_now && isset($response ['data'] [$i]['oh_ft'])) {
                $response ['data'] [$i] ['opens_at'] = self::getNextOpenTime($response ['data'] [$i]['oh_ft'], $request);
            } else {
                $response ['data'] [$i] ['opens_at'] = '';
            }

            // deal price
            if (!empty($response ['data'] [$i] ['deal_price'])) {
                if ($response ['data'] [$i] ['deal_discount_type'] == 'f') {
                    $response ['data'] [$i] ['deal_price_after_discount'] = $response ['data'] [$i] ['deal_price'] - $response ['data'] [$i] ['deal_discount'];
                } else {
                    $response ['data'] [$i] ['deal_price_after_discount'] = $response ['data'] [$i] ['deal_price'] - (($response ['data'] [$i] ['deal_price'] * $response ['data'] [$i] ['deal_discount']) / 100);
                }
            } else {
                $response ['data'] [$i] ['deal_price_after_discount'] = "";
            }
            
            $response ['data'] [$i] ['bookmarks'] = $this->getRestBookmarksMob($response ['data'] [$i] ['res_id']);

            /*
             * aor=1 if Accept Online Orders else 0
             * ordering_enabled = 1 iff ($doc['accept_cc_phone']) && ($doc['res_delivery'] || $doc['res_takeout'] || $doc['res_reservations'])
             */
            $response ['data'] [$i] ['aor'] = $response ['data'] [$i]['ordering_enabled'];

            //acp =1 if registered restaurants or with some tag
            $acp = ($response ['data'] [$i]['is_registered'] == 1) || (count($response ['data'] [$i]['tags_fct']) > 0);
            $response ['data'] [$i] ['acp'] = ($acp) ? 1 : 0;
            $response ['data'] [$i] ['user_craved_it'] = rand(0,1) == 0 ? false : true;
        }
        
        if(isset($response ['highlight'])){
            unset($response ['highlight']);
        }
    }
    
    
    /**
     * Mobile API Only: Used for adding extra fields in search response
     * @param array $response
     */    
    public function updateFoodDataMob(&$response, $request) {
        $deliver_flag = ($request['at'] == 'street') ? true : false;
        $latlong = explode(',', $request['latlong']);
        $user_lat = $latlong[0]; 
        $user_lng = $latlong[1];
        $totalData = count($response ['data']);
        for ($i = 0; $i < $totalData; $i ++) {
            $response ['data'] [$i] ['tags_fct'] = isset($response ['data'] [$i] ['tags_fct']) ? $response ['data'] [$i] ['tags_fct'] : [];
            $response ['data'] [$i] ['distance'] = round($response ['data'] [$i] ['distance'], 2);
            $response ['data'] [$i] ['menu_name'] = html_entity_decode($response ['data'] [$i] ['menu_name']);
            $menu_id = $response ['data'][$i]['menu_id'];
            $response ['data'] [$i] ['can_deliver'] = 1;
            if ($deliver_flag) {//update if this needs to be updated
                $response ['data'] [$i] ['can_deliver'] = (int) CityDeliveryCheck::canDeliver($response ['data'] [$i]['res_id'], $user_lat, $user_lng);
            }
            if (isset($response ['highlight'][$menu_id]['res_cuisine'])) {
                $response ['data'] [$i] ['res_cuisine'] = $response ['highlight'][$menu_id]['res_cuisine'][0];
            } else if (isset($response ['highlight'][$menu_id]['menu_cuisine'])) {
                $response ['data'] [$i] ['res_cuisine'] = $response ['highlight'][$menu_id]['menu_cuisine'][0];
            } else if (isset($response ['highlight'][$menu_id]['feature_name'])) {
                $response ['data'] [$i] ['res_cuisine'] = $response ['highlight'][$menu_id]['feature_name'][0];
            }
            if (isset($response ['highlight'][$menu_id]['menu_item_desc'])) {
                $response ['data'] [$i] ['menu_item_desc'] = $response ['highlight'][$menu_id]['menu_item_desc'][0];
            }
            $response ['data'] [$i] ['menu_item_desc'] = html_entity_decode($response ['data'] [$i] ['menu_item_desc']);

            $calender = new Calendar ();
            $isRestaurantOpen = $calender->isRestaurantOpen($response ['data'] [$i] ['res_id']);
            $response ['data'] [$i] ['is_currently_open'] = $isRestaurantOpen;
            $response ['data'] [$i] ['bookmarks'] = $this->getFoodBookmarksMob($menu_id);
            $response ['data'] [$i] ['res_primary_image'] = $this->getResPrimaryImgName($response ['data'] [$i] ['res_id']);

            /*
             * aor=1 if Accept Online Orders else 0
             * ordering_enabled = 1 iff ($doc['accept_cc_phone']) && ($doc['res_delivery'] || $doc['res_takeout'] || $doc['res_reservations'])
             */
            $response ['data'] [$i] ['aor'] = $response ['data'] [$i]['ordering_enabled'];

            //acp =1 if registered restaurants or with some tag
            $acp = ($response ['data'] [$i]['is_registered'] == 1) || (count($response ['data'] [$i]['tags_fct']) > 0);
            $response ['data'] [$i] ['acp'] = ($acp) ? 1 : 0;
        }
        
        if(isset($response ['highlight'])){
            unset($response ['highlight']);
        }
    }
    
    /**
     * For Mobile API
     * @param int $rest_id restaurant_id
     * @return array
     */
    public static function getRestBookmarksMob($rest_id) {
        //data to return
        $result =  array(
            'been_count' => 0,
            'love_count' => 0,
            'review_count' => 0,
            'user_been_there' => false,
            'user_loved_it' => false,
            'user_reviewed_it' => false,
            
        );
        
        $userReviewModel = new UserReview();
        $resreviewModel = new RestaurantReview();
        $resUserReview = $userReviewModel->getRestaurantReviewCount($rest_id);
        $resReview = $resreviewModel->getRestaurantReviewCount($rest_id);
        $userTip = new \User\Model\UserTip();
        $resTip=$userTip->restaurantTotalTips($rest_id);
        $totalReview = $resReview['total_count'] + $resUserReview['total_count'] + $resTip['total_count'];
        
        $result ['review_count'] = (int) $totalReview;
        
        $restaurantBookmarkModel = new RestaurantBookmark ();
        $bookmarks = $restaurantBookmarkModel->getRestaurantBookmarkCount($rest_id);
        if (!empty($bookmarks)) {
            foreach ($bookmarks as $bitem) {
                switch ($bitem ['type']) {
                    case 'bt' :
                        $result ['been_count'] = (int) $bitem ['total_count'];
                        break;
                    case 'lo' :
                        $result ['love_count'] = (int) $bitem ['total_count'];
                        break;
                }
            }
        } 
        
        $session = StaticOptions::getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        
        if ($isLoggedIn) {
            $userBookmarks = $restaurantBookmarkModel->getRestaurantBookmarksByUserId($rest_id, $session->getUserId());
            $result ['user_been_there'] = isset($userBookmarks ['bt']) && (int) $userBookmarks ['bt'] ? true : false;
            $result ['user_loved_it'] = isset($userBookmarks ['lo']) && (int) $userBookmarks ['lo'] ? true : false;
            $result ['user_reviewed_it'] = isset($userBookmarks ['re']) && (int) $userBookmarks ['re'] ? true : false;
        }
        
        return $result;
    }

    
    /**
     * For Mobile API
     * @param int $menu_id menu id
     * @return array
     */
    public static function getFoodBookmarksMob($menu_id) {
        //data to return
        $result = array(
            'craving_count' => 0,
            'love_count' => 0,
            'tried_count' => 0,
            'user_craving_it' => false,
            'user_loved_it' => false,
            'user_tried_it' => false,
        );
        
        
        // menu bookmark
        $menuBookmarkModel = new MenuBookmark ();
        $queryArr =  array('columns' => array('menu_id' => $menu_id));
        $bookmark = $menuBookmarkModel->menuBookmarksCounts($queryArr)->toArray();

        if (!empty($bookmark)) {
            foreach ($bookmark as $bitem) {
                switch ($bitem ['type']) {
                    case 'wi' :
                        $result['craving_count'] = (int) $bitem ['total_count'];
                        break;
                    case 'lo' :
                        $result['love_count'] = (int) $bitem ['total_count'];
                        break;
                    case 'ti' :
                        $result['tried_count'] = (int) $bitem ['total_count'];
                        break;
                }
            }
        }

        $session = StaticOptions::getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $bookmark_user = $menuBookmarkModel->getMenuBookmarksByUserId($menu_id, $session->getUserId());
            $result ['user_craving_it'] = isset($bookmark_user ['wi']) && (int) $bookmark_user ['wi'] ? true : false;
            $result ['user_loved_it'] = isset($bookmark_user ['lo']) && (int) $bookmark_user ['lo'] ? true : false;
            $result ['user_tried_it'] = isset($bookmark_user ['ti']) && (int) $bookmark_user ['ti'] ? true : false;
        }

        return $result;
    }
    
    /**
     * Checks if text appearing in the search-box is cuisine or not.
     * @param string $q query as appearing in the search box
     * @param string $view_type 'restaurant' or 'food'
     * @return boolean
     */
    public static function isQueryCuisine($q, $view_type = 'restaurant') {
        $ans = false;
        $solr_host = StaticOptions::getSolrUrl();
        if ($view_type == 'restaurant') {
            $url = $solr_host . 'hbr/select?rows=0&fq=cuisine_fct:"' . urlencode($q) . '"';
        } elseif($view_type == 'food') {
            $url = $solr_host . 'hbm/select?rows=0&fq=menu_cuisine_fct:"' . urlencode($q) . '"';
        } else {
            return $ans;
        }
        $response = SearchHelpers::getCurlUrlData($url);
        if ($response['status_code'] == 200) {
            $json = json_decode($response['data'], true);
            if ($json['response']['numFound'] > 0) {
                $ans = true;
            }
        }

        return $ans;
    }
    
    /**
     * To check if user's query match a cuisine of type of place
     * @param String $q
     * @return string {ft,cuisine,top}
     */
    public static function getQueryType($q, $view_type = 'restaurant') {
        $query = rawurlencode(strtolower(trim(str_replace('"','\"',$q))));
        $solr_host = StaticOptions::getSolrUrl();
        
        //type of place check
        $url = $solr_host . 'hbr/select?rows=0&fq=feature_fct:"' . $query . '"';
        $response = SearchHelpers::getCurlUrlData($url);
        if ($response['status_code'] == 200) {
            $json = json_decode($response['data'], true);
            if ($json['response']['numFound'] > 0) {
                return 'top';
            }
        }
        //cuisnie_check
        if ($view_type == 'restaurant') {
            $url = $solr_host . 'hbr/select?rows=0&fq=cuisine_fct:"' . $query . '"';
        } elseif($view_type == 'food') {
            $url = $solr_host . 'hbm/select?rows=0&fq=menu_cuisine_fct:"' . $query . '"';
        }
        $response = SearchHelpers::getCurlUrlData($url);
        if ($response['status_code'] == 200) {
            $json = json_decode($response['data'], true);
            if ($json['response']['numFound'] > 0) {
                return 'cuisine';
            }
        }

        return 'ft';
    }
    
    
    /**
     * 
     * @param string $cache_key
     * @param boolean $debug
     * @return mixed boolean false or data
     */
    public static function getCacheData($cache_key, $debug = false) {
        $sl = StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        if (!$config['constants']['memcache']) {
            return FALSE;
        }
        /* @var $memcached \Zend\Cache\Storage\Adapter\Memcached */
        $memcached = $sl->get('memcached');
        $response = $memcached->getItem($cache_key);
        if ($response) {
            if ($debug && is_array($response)) {
                $response['cache'] = true;
            }
            return $response;
        }
        return FALSE;
    }
    
    /**
     * 
     * @param string $cache_key
     * @param mixed $data array or string
     * @param int $ttl
     * @return boolean true on success false otherwise
     */
    public static function setCacheData($cache_key, $data, $ttl = 86400){
        $sl = StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        if (!$config['constants']['memcache']) {
            return FALSE;
        }
        /* @var $memcached \Zend\Cache\Storage\Adapter\Memcached */
        $memcached = $sl->get('memcached');
        $memcached->getOptions()->setTtl($ttl);
        $memcached->setItem($cache_key, $data);
    }
    
    private function getResPrimaryImgName($res_id){
        $model = new Restaurant();
        return $model->getResPrimaryImgName($res_id);
    }
    
    /**
     * Check if required search params are present. If not set their default values.
     * @param array $params search parameters
     * @param string $stype Search Type = {search,auto,facet}
     * @return array
     */
    public static function cleanWebSearchParams($params){
        $ret = array();
        $ret['DeBuG'] = isset($params['DeBuG']) ? $params['DeBuG'] : 0;
        $ret ['city_id'] = isset($params ['city_id']) ? (int) $params ['city_id'] : 18848;
        $ret ['city_name'] = isset($params ['city_name']) ? $params ['city_name'] : 'New York';
        $ret ['is_registered'] = isset($params ['is_registered']) ? $params ['is_registered'] : '';
        
        //open view type = {food, restaurant}
        $ret ['sst'] = isset($params ['sst']) ? $params ['sst'] : 'all';
        // vt = {restaurant, food, suggest} (not in use)
        $ret['ovt'] = isset($params['ovt']) ? $params['ovt'] : 'restaurant';
        
        $dayDateTime = DateTimeUtils::getCityDayDateAndTime24F($ret['city_id']);
        $ret['curr_time'] = $dayDateTime ['time'];
        $ret['curr_date'] = $dayDateTime ['date'];
        //pr($params);
        if(!isset($params['sdate']) || $params['sdate'] == '' ) {
            $ret ['sdate'] =  $dayDateTime ['date'];
        }else{ 
            $ret ['sdate'] =  $params['sdate']; 
        }

        if( !isset($params['stime']) || $params['stime'] == ''){
            $ret ['stime'] = self::getSearchTime($ret ['sst'], $ret['curr_time']);
        }else {
            $ret ['stime'] = intval(str_replace(':', '',$params['stime']));
        }
        /*
        if(!isset($params['sdate']) || !isset($params['stime']) || $params['sdate'] == '' || $params['stime'] == ''){
            $ret ['stime'] = self::getSearchTime($ret ['sst'], $ret['curr_time']);
            $ret ['sdate'] =  $dayDateTime ['date'];
        } else {
            $ret ['stime'] = intval(str_replace(':', '',$params['stime']));
            $ret ['sdate'] =  $params['sdate'];
        }*/
        $ret ['day'] =  substr(strtolower(date('D', strtotime($ret['sdate']))), 0, 2);
        
        // reservation_request_type = rrt = {breakfast,lunch,dinner}
        $ret ['rrt'] = isset($params ['rrt']) ? $params ['rrt'] : 'breakfast';
        
        // sq=search_query, sdt=search_datatype
        $ret ['sq'] = isset($params ['sq']) ? $params ['sq'] : '';
        // data_type: dt contains {cui,fav,pref,top,feat,amb} delimited by ||
        $ret ['sdt'] = isset($params ['sdt']) ? $params ['sdt'] : '';
        
        // sq=search_query, sdt=search_datatype
        $ret ['fq'] = isset($params ['fq']) ? $params ['fq'] : '';
        // data_type: dt contains {cui,fav,pref,top,feat,amb} delimited by ||
        $ret ['fdt'] = isset($params ['fdt']) ? $params ['fdt'] : '';

        // at = { address','street','nbd','zip','city'}, av=address value
        $ret ['at'] = isset($params ['at']) ? $params ['at'] : 'city';
        $ret ['av'] = isset($params ['av']) ? $params ['av'] : '';
        if($ret ['at'] == 'zip'){
            preg_match("/\d+/", $ret['av'], $zipcodes);
            $ret['av'] = isset($zipcodes[0]) ? $zipcodes[0] : '';
        } elseif($ret ['at'] == 'nbd'){
            preg_match("/[^,]+/", $ret['av'], $nbds);
            $ret['av'] = isset($nbds[0]) ? $nbds[0] : '';
        }
        //if 'av' is empty fallback to city
        if($ret['av'] == ''){
            $ret['at'] = 'city';
        }
        
        // latitude and longitude of the address
        $ret ['lat'] = isset($params ['lat']) ? $params ['lat'] : 0;
        $ret ['lng'] = isset($params ['lng']) ? $params ['lng'] : 0;
        
        // price = {0,1,2,3,4}
        $ret ['price'] = isset($params ['price']) ? (int) $params ['price'] : 0;
        //deals part of solr fq parameter
        $ret ['deals'] = 0;//isset($params ['deals']) ? (int) $params ['deals'] : 0;
        
        //sorting
        $ret ['sort_by'] = isset($params ['sort_by']) ? $params ['sort_by'] : 'relevancy';
        $ret ['sort_type'] = isset($params ['sort_type']) ? $params ['sort_type'] : 'asc';
                    
        // start and rows
        $ret ['start'] = isset($params ['start']) ? $params ['start'] : 0;
        $ret ['rows'] = isset($params ['rows']) ? $params ['rows'] : 12;
        $ret ['page'] = isset($params ['page']) ? $params ['page'] : 0;
        
        $ret ['aor'] = isset($params ['aor']) && ($params['aor'] == '1') ? 1 : 0;
        $ret ['acp'] = isset($params ['acp']) && ($params['acp'] == '1') ? 1 : 0;

        //params required for autosuggestion only
        $ret ['term'] = isset($params ['term']) ? rawurlencode(strtolower(trim($params ['term']))) : '';
        $ret ['limit'] = isset($params ['limit']) ? $params ['limit'] : 5;
        $ret ['sec'] = isset($params ['sec']) ? $params ['sec'] : '';  //this is to identify autosuggest from checkin
        $ret ['oh'] = isset($params ['oh']) ? $params ['oh'] : '';  //oh= operating hour , possible values are 'oa', 'on','24' and 'ol'. 'oa'=> "open at", 'on'=> "open now", '24'=> "open 24 hours" and 'ol'=> "open late" 
        return $ret;
    }
    

    /**
     * Check if required search params are present. If not set their default values.
     * @param array $params search parameters
     * @param string $stype Search Type = {search,auto,facet}
     * @return array
     */
    public static function cleanMobileSearchParams($params){
        $ret = array();
        $ret['DeBuG'] = isset($params['DeBuG']) ? $params['DeBuG'] : 0;
        
        $ret ['city_id'] = isset($params ['city_id']) ? (int) $params ['city_id'] : 18848;
        $ret ['city_name'] = isset($params ['city_name']) ? $params ['city_name'] : 'New York';
        
        $ret['reqtype'] = isset($params['reqtype']) ? $params['reqtype'] : 'search';
        //open view type = {food, restaurant}
        $ret['view_type'] = isset($params['view_type']) ? $params['view_type'] : 'restaurant';
        $ret ['tab'] = isset($params ['tab']) ? $params['tab'] : 'all';
        
        $dayDateTime = DateTimeUtils::getCityDayDateAndTime24F($params['city_id']);
        $ret['curr_time'] = $dayDateTime ['time'];
        $ret['curr_date'] = $dayDateTime ['date'];
        if(!isset($params['sdate']) || !isset($params['stime']) || $params['sdate'] == '' || $params['stime'] == ''){
            $ret ['stime'] = self::getSearchTime($ret ['tab'], $ret['curr_time']);
            $ret ['sdate'] =  $dayDateTime ['date'];
        } else {
            $ret ['stime'] = intval($params['stime']);
            $ret ['sdate'] =  $params['sdate'];
        }
        $ret ['day'] =  substr(strtolower(date('D', strtotime($ret['sdate']))), 0, 2);
        
        // q=search_query, dt=search_datatype
        $ret ['q'] = isset($params ['q']) ? $params ['q'] : '';
        // data_type: dt contains {cui,fav,pref,top,feat,amb} delimited by ||
        $ret ['dt'] = isset($params ['dt']) ? $params ['dt'] : '';
        
        //update dt to cuisine or top if it q matches to cuisine or type of place        
        if ($ret['q'] != '' && $ret['dt'] != 'curated') {
            $ret['q'] = \Solr\Common\Synonyms::applySynonyFilter($ret['q']);
            $ret['dt'] = self::getQueryType($params['q'], $ret['view_type']);
        }
        
        
        // at = { address','street','nbd','zip','city'}, av=address value
        $ret ['at'] = isset($params ['at']) ? $params ['at'] : 'city';
        $ret ['av'] = isset($params ['av']) ? $params ['av'] : '';
        // latitude and longitude of the address
        $ret ['latlong'] = isset($params ['latlong']) ? $params ['latlong'] : "0,0";
        $ret ['cl_latlong'] = isset($params ['cl_latlong']) ? $params ['cl_latlong'] : $ret ['latlong'];//current location latlong in mobile
        $ret ['zm_level'] = isset($params ['zm_level']) ? $params ['zm_level'] : 10;//city level
        
        // sq=search_query, sdt=search_datatype
        $ret ['cuisines'] = isset($params ['cuisines']) ? $params ['cuisines'] : '';
        // data_type: dt contains {cui,fav,pref,top,feat,amb} delimited by ||
        $ret ['features'] = isset($params ['features']) ? $params ['features'] : '';
        
        // price = {0,1,2,3,4}
        $ret ['price'] = isset($params ['price']) ? (int) $params ['price'] : 0;
        //deals part of solr fq parameter
        $ret ['deals'] = isset($params ['deals']) ? (int) $params ['deals'] : 0;
        $ret ['orn'] = isset($params ['orn']) ? (int) $params ['orn'] : 0;
        $ret ['accepts_order'] = isset($params ['accepts_order']) ? (int) $params ['accepts_order'] : 0;
        $ret ['res_type'] = isset($params ['res_type']) ? $params ['res_type'] : 'all';
        $ret ['all'] = isset($params ['all']) ? $params ['all'] : '0';//if 1 show all results for reservation
               
        // sorting
        $ret ['sort_by'] = isset($params ['sort_by']) ? $params ['sort_by'] : 'relevancy';
        $ret ['sort_type'] = isset($params ['sort_type']) ? $params ['sort_type'] : 'asc';
        
        // start and rows
        $ret ['start'] = isset($params ['start']) ? $params ['start'] : 0;
        $ret ['rows'] = isset($params ['rows']) ? $params ['rows'] : 12;
        
        $ret ['aor'] = isset($params ['aor']) && ($params['aor'] == '1') ? 1 : 0;
        $ret ['acp'] = isset($params ['acp']) && ($params['acp'] == '1') ? 1 : 0;

        $ret ['term'] = isset($params ['term']) ? rawurlencode(strtolower(trim($params ['term']))) : '';
        $ret ['limit'] = isset($params ['limit']) ? $params ['limit'] : 5;
        $ret ['sec'] = isset($params ['sec']) ? $params ['sec'] : '';  //this is to identify autosuggest from checkin
        return $ret;
    }
    
    
    /**
     * 
     * @param string $tab all,delivery,takeout,dinein,reservation
     * @param int $current_time in range 0-2659
     * @return int time in range 0-2359
     */
    public static function getSearchTime($tab, $current_time){
        if(in_array($tab, array('delivery', 'deliver', 'takeout'))){
            $selected_tab = 'order';
        } elseif(in_array($tab, array('dinein', 'reservation'))){
            $selected_tab = 'reservation';
        } else {
            $selected_tab = 'all';
        }
        
        $hour = intval($current_time / 100);
        $minuts = $current_time % 100;
        switch ($selected_tab) {
            case 'order':
                if($minuts <= 30){
                    $ansHour = $hour + 1;
                    $ansMinutes = 0;
                } else {
                    $ansHour = $hour + 1;
                    $ansMinutes = 30;
                }
                break;
            case 'reservation':
                if($minuts <= 30){
                    $ansHour = $hour;
                    $ansMinutes = 30;
                } else {
                    $ansHour = $hour + 1;
                    $ansMinutes = 0;
                }
                break;
            default:
                $ansHour = $hour;
                $ansMinutes = $minuts;
                break;
        }
        $search_time = (100 * $ansHour) + $ansMinutes;
        if($search_time == 2400){
            $search_time = 2359;
        } else if($search_time > 2400){
            $search_time = $current_time;
        }
        return $search_time;
    }
}
