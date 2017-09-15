<?php

namespace Solr;

use Solr\SearchUrls;
use Solr\SearchHelpers;

class MunchAds {

    private $debug = 0;

    /** url for ads
     * @var string  
     */
    private $search_ad_url;

    /**
     * Get search results for given query parameters
     * @param array $params search parameters
     * @return array data for food or restaurant view
     */
    public function getAdsData($params) {
        $request = \Search\SearchFunctions::cleanWebSearchParams($params);
        if ($request['DeBuG'] == 404) {
            $this->debug = 1;
        }
        $this->searchUrls = new SearchUrls();
        //if ($request['ovt'] == 'restaurant' && $this->searchUrls->ad_keyword != '') {
            try {
                $adsData = $this->getResAdsData($request);
                $adsData['ad_keyword'] = $this->searchUrls->ad_keyword;
                $this->addRestAdsHtml($adsData);
                if ($this->debug) {
                    $adsData['data'][] = array('ad_url' => $this->search_ad_url);
                    $adsData['data'][] = array('request' => $request);
                }
                return $adsData;
            } catch (\Exception $e) {
                
            }
        //}
        return array();
    }

    /**
     * 
     * @return array with keys data, count and error
     */
    private function getResAdsData($request) {
        $this->updateForAdRequest($request);
        switch ($request['sst']) {
            case 'deliver':
                $this->search_ad_url = $this->searchUrls->getDeliverUrlForAds($request);
                break;
            case 'takeout':
                $this->search_ad_url = $this->searchUrls->getTakeoutUrlForAds($request);
                break;
            case 'reservation':
                $this->search_ad_url = $this->searchUrls->getReservationUrlForAds($request);
                break;
            case 'dinein':
                $this->search_ad_url = $this->searchUrls->getDineinUrlForAds($request);
                break;
            case 'all':
                $this->search_ad_url = $this->searchUrls->getDiscoverUrlForAds($request);
                break;
        }
        $unescapedurl = $this->search_ad_url . '&facet=on&facet.query={!key=has_deals}has_deals:1';
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        $retData = array();
        $output = SearchHelpers::getCurlUrlData($url);
        if ($output['status_code'] == 200) {
            $responseArr = json_decode($output['data'], true);
            $retData['count'] = $responseArr['response']['numFound'];
            $retData['data'] = $responseArr['response']['docs'];
        } else {
            $responseArr = isset($output['result']) ? json_decode($output['result'], true) : array('error' => 'server error');
            $retData['error'] = $responseArr['error'];
            $retData['count'] = 0;
            $retData['data'] = array();
        }
        return $retData;
    }

    private function updateForAdRequest(&$request) {
        $request['start'] = $request['page'] * 2;
        $request['rows'] = 2;
    }

    private function addRestAdsHtml(&$response) {
        $ads_model = new \Restaurant\Model\RestaurantAds();
        foreach ($response['data'] as $i => $res) {
            $response['data'][$i]['ads'] = $ads_model->getRestaurantAds($res['res_id'], $response['ad_keyword']);
        }
    }

}
