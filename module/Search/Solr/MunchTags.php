<?php

namespace Solr;

use Solr\SearchUrls;
use Solr\SearchHelpers;

class MunchTags {

    private $debug = 0;
    
    /**SearchUrls class instance
     * @var SearchUrls 
     */
    public $searchUrls;

    /**
     * Get search results for given query parameters
     * @param array $params search parameters
     * @return array data for food or restaurant view
     */
    public function getTagsData($params) {
        $params['sst'] = 'all';
        $params['at'] = 'city';
        $params['av'] = 'NewYork';
        $params['ovt'] = 'restaurant'; 
        $params['lat'] = '40.7127'; 
        $params['lng'] = '-74.0059'; 
        $params['sdt'] = 'tag';
        $params['page'] = '0';
        $params['rows'] = 50; //by param to get all sweepstake results
        //pr($params,1);
        $request = \Search\SearchFunctions::cleanWebSearchParams($params);
        //pr($params);
        if ($request['DeBuG'] == 404) {
            $this->debug = 1;
        }
        $this->searchUrls = new SearchUrls();
        $res_url = $this->searchUrls->getDiscoverUrl($request);
        //pr($res_url,1);
        $retData = array();
        $output = SearchHelpers::getCurlUrlData($res_url);
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
        
        //add image
        foreach ($retData['data'] as $i => $rest) {
            $retData['data'][$i]['restaurant_image_name'] = IMAGE_PATH .strtolower($rest['res_code']).'/' . $retData['data'][$i]['res_primary_image'];
        }
        if($this->debug){
            $retData['url'] = $res_url;
        }
        return $retData;
    }

}
