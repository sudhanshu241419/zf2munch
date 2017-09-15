<?php

namespace Search\Controller;

use MCommons\Controller\AbstractRestfulController;
use Solr\PickAnArea;
use Search\SearchFunctions;
use Solr\Facets;
use Solr\MainSearch;
use Solr\SeoSearch;

class WebSearchController extends AbstractRestfulController {

    const FOOD = 'food';
    const RESTAURANT = 'restaurant';
    const SUGGEST = 'suggest';
    const SEO_PAGE_SIZE = 100;
    
    private $debug = false;
    private $starttime_milli = 0;

    public function getList() {
        $debug = $this->getQueryParams('DeBuG', "");
        if($debug == '404'){
            $this->debug = true;
            $this->starttime_milli = microtime(true);
        }
        
        $reqtype = $this->getQueryParams('rt', "");
        $response = array();
        if($reqtype == 'seo'){
            $response = $this->getSeoResponse();
            $response['image_base_path'] = IMAGE_PATH;
        } else {
            $response = $this->search_by_req_type($reqtype);
        }
        
        if ($this->debug) {
            $response['time_in_millis'] = microtime(true) - $this->starttime_milli;
            $response['original_request_params'] = $this->getRequest()->getQuery()->toArray();
        }
        return $response;
    }

    private function getSeoResponse() {
        $seo = new SeoSearch();
        $req = $this->getRequest()->getQuery()->toArray();
        $req['city_id'] = isset($req['city_id']) ? $req['city_id'] : $this->getCityId();
        $req['start'] = ((int) $req['page'] - 1) * self::SEO_PAGE_SIZE;
        $req['rows'] = self::SEO_PAGE_SIZE;
//        $req = array(
//            'city_id' => 1241,
//            'lat' => 0,
//            'lng' => 0,
//            'start' => 0,
//            'rows' => 10
//        );
        return $seo->getSeoSearchData($req);
    }

    private function search_by_req_type($type) {
        $queryParam = $this->getRequest()->getQuery()->toArray();
        $seo = isset($queryParam['seo'])?true:false;
       
        $rawInput = SearchFunctions::mapRequestKeys($this->getRequest()->getQuery()->toArray());
       if(!isset($rawInput['slrRestaurant'])){
        $session = $this->getUserSession();
        $user_loc = $session->getUserDetail('selected_location', array());
        $rawInput['city_id'] = isset($user_loc ['city_id']) ? intval($user_loc ['city_id']) : 18848;
        $rawInput['city_name'] = isset($user_loc['city_name']) ? $user_loc['city_name'] : 'New York';
        $rawInput['is_registered'] = (isset($queryParam['rgt']) && $queryParam['rgt']==1)?$queryParam['rgt']:'';
       }
        $input = SearchFunctions::cleanWebSearchParams($rawInput);
        if ($input['sq'] != '') {
            $input['sq'] = \Solr\Common\Synonyms::applySynonyFilter($input['sq']);
            $query_type = SearchFunctions::getQueryType($input['sq'], $input['ovt']);
            if ($query_type == 'cuisine') {
                $input['sdt'] = 'cui';
            } elseif ($query_type == 'top') {
                $input['sdt'] = 'top';
            }
        }
        if($seo){
            $input['rows']=100;
        }
       
        switch ($type) {
            case 's' :
                $response = $this->getMainSearchData($input);
                if ($input['sq'] != '') {
                    $log = new \Search\Model\LogSearch();
                    $saved = $log->saveSearchLogWeb($input);
                    if (isset($input['DeBuG'])) {
                        $response['query_saved'] = $saved;
                    }
                }
                break;
            case 'getCuisineCount' :
                $response = $this->getCuisineCount($input);
                break;
            case 'pickAnArea' :
                $pick_an_area = new PickAnArea ();
               // $request = $this->pickAnArea($input);
                $newresponse = isset($_REQUEST['newresponse']) ? true : false;
                $data = $pick_an_area->getLandmarksData($input, $newresponse);
                if($newresponse){
                   $response = $data;
                } else {
                   $response = isset($data ['landmarks']) ? $data ['landmarks'] : array(); 
                }
                break;
            case 'getRestCount' :
//                $cache_key = 'restcount_' . $input['city_id'];
//                $cached_response = $this->getCacheData($cache_key);
//                if($cached_response){
//                    return $cached_response;
//                }
                // Get Restaurant Count. Not in use in frontend
                $response = array(
                    'city_id' => $input['city_id'],
                    'city_name' => $input['city_name'],
                    'count' => -1
                );
                //$pick_an_area = new PickAnArea ();
                //$response = $pick_an_area->getRestaurantCounts($input);
                //$this->setCacheData($cache_key, $response);
                break;
            case 'topLocation' :
                $pick_an_area = new PickAnArea ();
                $data = $pick_an_area->getTopNeighborhoods($input);
                $response = isset($data ['landmarks']) ? $data ['landmarks'] : array();
                break;
            case 'ads' :
                $munchAds = new \Solr\MunchAds();
                $response = $munchAds->getAdsData($input);
                break;
            case 'tags' :
                $munchTags = new \Solr\MunchTags();
                $response = $munchTags->getTagsData($input);
                break;            
            case 'userdeals' :
                $response = $this->getUserDealsData($input);
                break;
            case 'apitest' :
                //http://munch-local.com/wapi/search?rt=apitest&dEbUg=404
                $debug = $this->getQueryParams('dEbUg', '');
                if($debug == '404'){
                    $searchApiTest = new \Search\SearchApiTest();
                    $device = $this->getQueryParams('device', 'web');
                    $response = $searchApiTest->getSearchApiTestData($device); 
                } else {
                    $response = array(
                            'message' => 'You must be kidding! aAb.',
                            'help' => 'Who brought you here?',
                        );
                }
                break;
            default :
               throw new \Exception("Invalid Request", 400);
        }
        return $response;
    }

    private function getCuisineCount($input) {
        $search = new Facets ();
        $response = $search->returnFacetData($input);
        $cusineResponse = SearchFunctions::formatCuisineCount($response);
        return $cusineResponse;
    }

    private function getDayAndTime($input) {
        if (isset($input['st']) && $input['st'] == 'reserve' && empty($input['time'])) {
            $input['time'] = "00:00";
        }
        return SearchFunctions::getDayTime($input);
    }

    private function getMainSearchData($input) {
        $search = new MainSearch ();
        $response_arr = $search->returnSearchData($input);

        // --------DO NOT TOUCH---------------------------//
        $searchFunctions = new SearchFunctions ();
        if ($input ['ovt'] == self::FOOD) {
            $searchFunctions->addAndFormatFoodData($response_arr, $input);
        } elseif ($input ['ovt'] == self::RESTAURANT) {
            $searchFunctions->addAndFormatRestData($response_arr, $input);
        }
        // ----------------------------------------------//
        $response_arr ['image_base_path'] = IMAGE_PATH;
        return $response_arr;
    }
    
    
    private function getUserDealsData($input) {
        $input['uid'] = $this->getUserSession()->getUserId();
        $udw = new \Solr\Web\UserDealWeb();
        $response_arr = $udw->getUserDeals($input);
        $searchFunctions = new SearchFunctions ();
        $searchFunctions->addAndFormatRestData($response_arr, $input);
        
        // ----------------------------------------------//
        $response_arr ['image_base_path'] = IMAGE_PATH;
        return $response_arr;
    }
    
    private function getCityId(){
        $session = $this->getUserSession();
        $user_loc = $session->getUserDetail('selected_location', array());
        return isset($user_loc ['city_id']) ? $user_loc ['city_id'] : 18848;
    }

}
