<?php

namespace Search\Controller;

use MCommons\Controller\AbstractRestfulController;
use Solr\PickAnAreaMobile;
use Search\SearchFunctions;
use Solr\FacetsMobile;
use Solr\MainSearchMobile;
use Solr\AutoCompleteMobile;
use \Solr\FriendsMobile;
use Solr\Mob\UserDealMob;

class MobileSearchController extends AbstractRestfulController {

    const FOOD = 'food';
    const RESTAURANT = 'restaurant';
    const SEO_PAGE_SIZE = 100;
    
    private $debug = false;
    private $starttime_milli = 0;

    public function create($data) {
        $input = $data;
        if (!empty($input['reqtype'])) {
            $response = $this->search_by_req_type($input);
        } else {
            throw new \Exception("Invalid Parameters", 400);
        }
        return $response;
    }

    public function getList() {
        $debug = $this->getQueryParams('DeBuG', "");
        if($debug == '404'){
            $this->debug = true;
            $this->starttime_milli = microtime(true);
        }
        $input = $this->getRequest()->getQuery()->toArray();
        if (empty($input['reqtype'])) {
            throw new \Exception("Parameter reqtype is invalid or missing.", 400);
        }
        $response = $this->search_by_req_type($input);
        if ($this->debug) {
            $response['time_in_seconds'] = microtime(true) - $this->starttime_milli;
            $response['original_request_params'] = $this->getRequest()->getQuery()->toArray();
        }
        return $response;
    }

    public function search_by_req_type($rawInput) {
        $session    =   $this->getUserSession();
        $user_loc   =   $session->getUserDetail('selected_location', array());
        $rawInput['city_id']    = isset($user_loc['city_id']) ? intval($user_loc ['city_id']) : 18848;
        $rawInput['city_name']  = isset($user_loc['city_name']) ? $user_loc['city_name'] : 'New York';
        
        $input = SearchFunctions::cleanMobileSearchParams($rawInput);
        
        $response = array();
        switch ($input['reqtype']) {
            case 'search' :
                $response = $this->getMainSearchData($input);
                if (isset($input['q']) && $input['q'] != '') {
                    $log    = new \Search\Model\LogSearch();
                    $saved  = $log->saveSearchLogMob($input);
                }
                break;
            case 'friendsearch' :
                $input['user_id'] = $session->getUserId();
                $input['rows'] = 8;
                $fm = new FriendsMobile();
                $response = $fm->getFriendSuggestions($input);
                break;
            case 'counters' :
                $response = $this->getCuisineFeatureCounters($input);
                break;
            case 'curated' :
                $response = $this->getCuratedCounters($input);
                break;
            case 'landmarks' :
                $pick_an_area = new PickAnAreaMobile();
                $data = $pick_an_area->getLandmarksData($input);
                $response = isset($data ['landmarks']) ? $data ['landmarks'] : array();
                break;
            case 'suggest' :
                $ac = new AutoCompleteMobile();
                $autocompleteArr = $ac->getAutocomplete($input);
                $response = isset($autocompleteArr ['data']) ? $autocompleteArr ['data'] : array();
                break;
            case 'totalrescount' :
                // Get Restaurant Count
                $pick_an_area = new PickAnAreaMobile();
                $response = $pick_an_area->getRestaurantCounts($input);
                break;
            case 'userdeals' :
                // Get User Deals
                $response = $this->getUserDealsData($input);
                break;
            default :
                throw new \Exception("Invalid Request", 400);
        }
        return $response;
    }

    private function getTimeSlot($currentDateTime, $stype) {
        $currentMinute = (int) $currentDateTime->format('i');
        $currentHour = (int) $currentDateTime->format('H');
        if ($currentMinute >= 0 && $currentMinute < 30) {
            $currentDateTime->setTime($currentHour, 30, 0);
        } else if ($currentMinute >= 30) {
            $currentDateTime->setTime($currentHour + 1, 00, 0);
        }
        if ($stype == "order") {
            $this->getTimeSlot($currentDateTime, null);
        }
        $reservationTimeslots = $currentDateTime->format('hi');
        return $reservationTimeslots;
    }

    private function getCuisineFeatureCounters($input) {
        $search = new FacetsMobile();
        return $search->returnFacetData($input);
    }

    private function getCuratedCounters($input) {
        $search = new FacetsMobile();
        return $search->returnCuratedData($input);
    }

    private function getMainSearchData($input) {
        $search = new MainSearchMobile();
        $response = $search->returnMobileSearchData($input);
        //SearchHelpers::pr($response_arr,true);
        // --------DO NOT TOUCH---------------------------//
        $searchFunctions = new SearchFunctions ();
        if ($input ['view_type'] == self::RESTAURANT) {
            $searchFunctions->updateResDataMob($response, $input);
        } elseif($input ['view_type'] == self::FOOD) {
            $searchFunctions->updateFoodDataMob($response, $input);
        }
        // ----------------------------------------------//
        $response ['image_base_path'] = IMAGE_PATH;
        $city = new \Home\Model\City();
        $response ['curr_time'] = $city->getCityCurrentDateTime($input['city_id']);
        $response ['search_time'] = !empty($input['stime'])?$input['stime']:$response ['curr_time'];
        return $response;
    }

    private function getUserDealsData($input) {
        $input['uid'] = $this->getUserSession()->getUserId();
        $udm = new UserDealMob();
        $response = $udm->getUserDeals($input);
        $searchFunctions = new SearchFunctions ();
        $searchFunctions->updateResDataMob($response, $input);
        $response ['image_base_path'] = IMAGE_PATH;
        $city = new \Home\Model\City();
        $response ['curr_time'] = $city->getCityCurrentDateTime($input['city_id']);
        $response ['search_time'] = $input['stime'];
        return $response;
    }

}
