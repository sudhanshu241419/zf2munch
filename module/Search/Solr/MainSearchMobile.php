<?php

namespace Solr;

use Solr\SearchUrlsMobile;
use Solr\SearchHelpers;
use Solr\RecommendationsMobile;

class MainSearchMobile {

    private $debug = 0;
    public $searchUrlsMobile; //SearchUrls class instance
    
    /**
     * Get search results for given query parameters for mobile api.
     * This assumes that $params is already cleaned and free from any errors.
     * @param array $params search parameters
     * @return array data for food or restaurant view
     */
    public function returnMobileSearchData($params) {
        if ($params['DeBuG'] == 404) {
            $this->debug = 1;
        }
        $this->searchUrlsMobile = new SearchUrlsMobile();
        try {
            $unescapedurl = $this->getSearchUrl($params);
            $resData = $this->prepareReturnData($unescapedurl, $params);
            return $resData;
        } catch (\Exception $e) {
            return array();
        }
    }

    private function getSearchUrl($request) {
        $url = '';
        switch ($request['tab']) {
            case 'delivery':
                $url = $this->searchUrlsMobile->getDeliverUrl($request);
                break;
            case 'takeout':
                $url = $this->searchUrlsMobile->getTakeoutUrl($request);
                break;
            case 'reservation':
                $url = $this->searchUrlsMobile->getReservationUrl($request);
                break;
            case 'dinein':
                $url = $this->searchUrlsMobile->getDineinUrl($request);
                break;
            case 'all':
                $url = $this->searchUrlsMobile->getDiscoverUrl($request);
                break;
        }
        return $url;
    }

    private function prepareReturnData($unescapedurl, $request) {
        $unescapedurl .= SearchHelpers::getUrlDealsFacetPart($request['tab']);
        $retData = array();
        $unescapedurl .= SearchHelpers::getHighlightFl(array($request['q'], $request['cuisines'], $request['features']), $request['view_type']);
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        if ($this->debug) {
            $retData['url'] = SearchHelpers::getDebugUrl($url);
            $retData['params_used'] = $request;
        }
        $output = SearchHelpers::getCurlUrlData($url);
        if ($output['status_code'] == 200) {
            $responseArr = json_decode($output['data'], true);             
            $retData['count'] = $responseArr['response']['numFound'];
            $retData['data'] = $responseArr['response']['docs'];
            $retData['has_deals'] = $responseArr['facet_counts']['facet_queries']['has_deals'];
            //$retData['ordering_enabled'] = $responseArr['facet_counts']['facet_queries']['ordering_enabled'];
            //print_r($responseArr);die;
            if (isset($responseArr['highlighting'])) {
                //$retData['highlight'] = $responseArr['highlighting'];
            }
            
//            //did you mean if no result
//            if ($retData['count'] == 0 && isset($responseArr['spellcheck']['suggestions'])) {
//                $dymArr = $responseArr['spellcheck']['suggestions'];
//                $dymLength = count($dymArr);
//                if ($dymLength > 0) {
//                    $retData['dym'] = $dymArr[$dymLength - 1];
//                }
//            }
            
            //recommendations for less than 3 results
            /*
            $count = ($this->debug) ? 100000 : 4;
            if ($retData['count'] < $count && $request['view_type'] != 'food') {
                $recoMob = new RecommendationsMobile();
                $recoMob->getRecommendations($retData, $this->searchUrlsMobile, $request);
            }
            */
        } else {
            $responseArr = isset($output['result']) ? json_decode($output['result'], true) : array('error' => 'server error');
            $retData['error'] = $responseArr['error'];
            $retData['count'] = 0;
            $retData['data'] = array();
        }
        //print_r($retData);die;
        return $retData;
    }

}

?>