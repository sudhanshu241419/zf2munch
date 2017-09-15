<?php

namespace Solr;

use Solr\SearchUrls;
use Solr\SearchHelpers;
use Solr\Recommendations;

class MainSearch {

    private $debug = 0;
    
    /**SearchUrls class instance
     * @var SearchUrls 
     */
    public $searchUrls;
    
    /**
     * Get search results for given query parameters
     * @param array $$request search parameters
     * @return array data for food or restaurant view
     */
    public function returnSearchData($request) {
        //pr($params);$request
        //pr($request,1);
        if ($request['DeBuG'] == 404) {
            $this->debug = 1;
        }
        $this->searchUrls = new SearchUrls();
        try {
            $unescapedurl = $this->getSearchUrl($request);
            $resData = $this->prepareReturnData($unescapedurl, $request);

            //ad data
            $resData['ad_data'] = $this->getResAdsData($request);
            $resData['dym'] = $request['sq'] . ' ' . $request['fq'];
            return $resData;
        } catch (\Exception $e) {
            
        }
    }

    private function getSearchUrl($request) {
        //$sst = $request['sst'];//sst = {all,deliver,takeout,dinein,reservation}
        $url = '';
        switch ($request['sst']) {
            case 'deliver':
                //$url = $this->getOrderUrl($request);
                $url = $this->searchUrls->getDeliverUrl($request);
                break;
            case 'takeout':
                $url = $this->searchUrls->getTakeoutUrl($request);
                break;
            case 'reservation':
                $url = $this->searchUrls->getReservationUrl($request);
                break;
            case 'dinein':
                $url = $this->searchUrls->getDineinUrl($request);
                break;
            case 'all':
                $url = $this->searchUrls->getDiscoverUrl($request);
                break;
        }
        return $url;
    }

    private function prepareReturnData($unescapedurl, $request) {
        $unescapedurl .= SearchHelpers::getUrlDealsFacetPart($request['sst']);
        
        $unescapedurl .= $this->searchUrls->getFacetCountForSelectedFilters($request);
        
        $retData = array();
        $unescapedurl .= SearchHelpers::getHighlightFl(array($request['sq'], $request['fq']), $request['ovt']);
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        if ($this->debug) {
            $retData['url'] = SearchHelpers::getDebugUrl($url);
            $retData['params_used'] = $request;
        }
        $output = SearchHelpers::getCurlUrlData($url);
        
        if ($output['status_code'] == 200) {
            $responseArr = json_decode($output['data'], true);
            //pr($responseArr,1);
            $retData['count'] = $responseArr['response']['numFound'];
            $retData['data'] = $responseArr['response']['docs'];
            $retData['has_deals'] = $responseArr['facet_counts']['facet_queries']['has_deals'];
            
            /* Below option has been added to give the count of selected filters  [ Athar: 28-08-2017 ]*/
            $retData['dyn_filters'] = $responseArr['facet_counts']['facet_queries'];  
            
            //pr($retData,1);die;
            if (isset($responseArr['highlighting'])) {
                $retData['highlight'] = $responseArr['highlighting'];
            }
            //did you mean if no result
            if ($retData['count'] == 0 && isset($responseArr['spellcheck']['suggestions'])) {
                $dymArr = $responseArr['spellcheck']['suggestions'];
                $dymLength = count($dymArr);
                if ($dymLength > 0) {
                    $retData['dym'] = $dymArr[$dymLength - 1];
                }
            }
            //recommendations for less than 3 results
            /* Recommendations not showing for now.
            $count = ($this->debug) ? 100000 : 4;
            if ($retData['count'] < $count && $request['ovt'] != 'food') {
                $reco = new Recommendations();
                $reco->getRecommendations($retData, $this->searchUrls, $request, $retData['count']);
            }
            */
        } else {
            $responseArr = isset($output['result']) ? json_decode($output['result'], true) : array('error' => 'server error');
            $retData['error'] = $responseArr['error'];
            $retData['count'] = 0;
            $retData['data'] = array();
        }
        $retData['render_view'] = $request['ovt'];
        //print_r($retData);die;
        return $retData;
    }
    
    private function getResAdsData($request){
        if ($request['ovt'] == 'restaurant' && $this->searchUrls->ad_keyword != '') {
            $munchAds = new MunchAds();
            return $munchAds->getAdsData($request);
        }
        return array();
    }
}