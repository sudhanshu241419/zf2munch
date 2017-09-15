<?php

namespace Solr;

use Solr\SearchUrls;
use Solr\SearchHelpers;

class SeoSearch {

    /**
     * Method for providing SEO only restaurant-data
     * @param array $req input parameters
     * @return array seo restaurant data
     */
    public function getSeoSearchData($req) {
//        $req =  array(
//            'city_id' => 1241,
//            'lat' => 0,
//            'lng' => 0,
//            'start' => 0,
//            'rows' => 10
//        );
        $response = array();
        $req = $this->filterParams($req);
        $searchUrls = new SearchUrls();
        $url = $searchUrls->getSeoUrl($req);
        $url .= '&fl=res_id,res_name,res_code,res_description,res_primary_image';
        $output = SearchHelpers::getCurlUrlData($url);
        if ($output['status_code'] == 200) {
            $resArr = json_decode($output['data'], true);
            $response = $resArr['response'];
        }
        return $response;
    }
    
    private function filterParams($req){
        if(!isset($req['city_id']) || !isset($req['lat']) || !isset($req['lng'])){
            throw new \Exception('invalid call');
        }
        
        $req['start'] = isset($req['start']) ? $req['start']: 0;
        if($req['rows'] > 2500){
            $req['rows'] = 2500;
        }
        
        return array(
            'city_id' => $req['city_id'],
            'lat' => $req['lat'],
            'lng' => $req['lng'],
            'start' => $req['start'],
            'rows' => $req['rows']
        );
    }

}
