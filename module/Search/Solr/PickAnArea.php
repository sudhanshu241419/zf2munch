<?php

namespace Solr;

use MCommons\StaticOptions;
use Solr\SearchHelpers;

/**
 * Use this class for web search only.
 *
 * @author Dhirendra Singh Yadav
 */
class PickAnArea {

    private $debug = false;
    private $_solr_url_res = '';
    private $_solr_url_food = '';
    private $solr_select_url = '';

    public function __construct() {
        $solr_host = StaticOptions::getSolrUrl();
        $this->_solr_url_res = $solr_host . 'hbr/hbsearch?';
        $this->_solr_url_food = $solr_host . 'hbm/hbsearch?';
        $this->solr_select_url = $solr_host . 'hbr/select?';
    }

    /**
     * For <b>rt=getRestCount</b>. 
     * Used for counting number of restaurants in a city (discover landing page)
     * @param array $params with keys nbd_cities,
     * @return array
     */
    public function getRestaurantCounts($params) {
        //pr($params,1);
        if (isset($params['DeBuG']) && $params['DeBuG'] == 404) {
            $this->debug = true;
        }
        $response = array();
        $city_fq = '&fq=city_id:' . $params['city_id'];
        $unescapedurl = $this->solr_select_url . 'wt=json&rows=0' . $city_fq;
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        $output = SearchHelpers::getCurlUrlData($url);
        if ($output ['status_code'] == 200) {
            $dataArr = json_decode($output ['data'], true);
            $response ['city_id'] = $params['city_id'];
            $response ['city_name'] = $params['city_name'];
            $response ['count'] = $dataArr ['response'] ['numFound'];
        } else {
            $response ['city_id'] = $params['city_id'];
            $response ['city_name'] = $params['city_name'];
            $response ['count'] = 0;
        }
        if ($this->debug) {
            $response ['url'] = SearchHelpers::getDebugUrl($url);
        }
        return $response;
    }

    /**
     * For <b>rt=pickAnArea</b>. used for showing landmarks list for select a location dropdown
     */
    public function getLandmarksData($req) {
        if ($req['DeBuG'] == 404) {
            $this->debug = 1;
        }
        if ($req['ovt'] == 'food') {
            $baseUrl = $this->_solr_url_food;
        } else {
            $baseUrl = $this->_solr_url_res;
        }

        $groupPart = 'rows=1000&fl=res_neighborhood,borough,nbd_lat,nbd_long&group=true&group.field=res_neighborhood&sort=borough+asc,res_neighborhood+asc&';

        $searchUrl = new SearchUrls ();
        $fqPart = $searchUrl->getPickAnAreaFq($req);

        $unescapedurl = $baseUrl . $groupPart . $fqPart;
        $response = $this->getLandmarksFromUrl($unescapedurl, 1);
        
        if ($this->debug) {
            $response ['landmarks'] [] = array('url' => SearchHelpers::getDebugUrl($unescapedurl), 'req' => $req, 'landmark' => '', 'latitude' => '', 'longitude' => '');
        }
        return $response;
    }

    /**
     * For <b>rt=topLocation</b>. Get List of Top Neighborhoods(based on frequency) in a city.
     * @param array $req Array(rt=>topLocation,token=>a187142e2ede048ac44efcab51195389,deals=>0,
     * sdate => 2015-6-10,city_id=>18848,nbd_cities=>18848,day=>we,time=>0952,curr_time=>0952)
     * @return array with key landmarks 
     */
    public function getTopNeighborhoods($req) {
        if (isset($req['DeBuG']) && $req['DeBuG'] == 404) {
            $this->debug = 1;
        }
        $baseUrl = $this->_solr_url_res;
        $groupPart = 'rows=1000&fl=res_neighborhood,borough,nbd_lat,nbd_long&group=true&group.field=res_neighborhood&sort=borough+asc,res_neighborhood+asc&';
        $fqPart = '&fq=city_id:' . $req['city_id'];
        $unescapedurl = $baseUrl . $groupPart . $fqPart;
        $response = $this->getLandmarksFromUrl($unescapedurl, 2);
        if ($this->debug) {
            $response ['landmarks'] [] = array('url' => SearchHelpers::getDebugUrl($unescapedurl), 'req' => $req);
        }
        return $response;
    }

    private function getLandmarksFromUrl($unescapedurl, $type = 1) {
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        $response = array();
        $output = SearchHelpers::getCurlUrlData($url);
        $newresponse = isset($_REQUEST['newresponse']) ? true : false; 
        if ($output ['status_code'] == 200) {
            $boroughList = [];
            $dataArr = json_decode($output ['data'], true);
            $numCount = $dataArr ['grouped'] ['res_neighborhood'] ['matches'];
            if ($numCount > 0) {
                $landmarks = $dataArr ['grouped'] ['res_neighborhood'] ['groups'];
                foreach ($landmarks as $landmark) {
                    if (!in_array($landmark ['groupValue'], array('', '0'))) {
                        $borough = isset($landmark ['doclist'] ['docs'] [0] ['borough']) ? $landmark ['doclist'] ['docs'] [0] ['borough'] : '';
                        $boroughList[$borough] = $borough;
                        if ($type == 1) {
                            $response ['landmarks'] [] = array(
                                'landmark' => $landmark ['doclist'] ['docs'] [0] ['res_neighborhood'],
                                'borough' => $borough,
                                'latitude' => $landmark ['doclist'] ['docs'] [0] ['nbd_lat'],
                                'longitude' => $landmark ['doclist'] ['docs'] [0] ['nbd_long']
                            );
                        } else {
                            if($newresponse) {
                                $response ['landmarks'] [] = [
                                    'landmark' => $landmark ['doclist'] ['docs'] [0] ['res_neighborhood'],
                                    'borough' => $borough
                                    ];
                            } else {
                               $response ['landmarks'] [] = $landmark ['doclist'] ['docs'] [0] ['res_neighborhood'];
                            }
                            
                        }
                    }
                }
                if($type == 1) {
                    $response ['borough_list'] = array_keys($boroughList);
                }
            }
        }
        return $response;
    }

}
