<?php

namespace Solr;

use Solr\SearchUrls;
use Solr\SearchHelpers;

class AutoComplete {

    private $debug = 0;
    private $req = array();
    public $searchUrls;
    private $ovt = 'restaurant';
    public static $res_facet_map = array(
        'cuisine_fct' => 'cuisine',
        'res_fct' => 'restaurant',
        'menu_fct' => 'food',
        'feature_fct' => 'feature'
    );
    public static $food_facet_map = array(
        'menu_cuisine_fct' => 'cuisine',
        'menu_fct' => 'food',
        'feature_fct' => 'feature'
    );

    /*
     * facet fields for various cases
     * deliver = {cuisine, popular food, popular trends}
     * takeout = {cuisine, popular food, popular trends}
     * dinein = {Cuisines,Popular trends,Restaurants,Type of place}
     * reservation = {Cuisines,Popular trends,Restaurants,Type of place}
     * discover = {cuisine, popular food, popular trends}
     */
    public static $res_facet_fields = array(
        'discover_ff' => '&facet.field=cuisine_fct',
        'deliver_ff' => '&facet.field=cuisine_fct',
        'takeout_ff' => '&facet.field=cuisine_fct',
        'dinein_ff' => '&facet.field=cuisine_fct',
        'reservation_ff' => '&facet.field=cuisine_fct'
    );
    public static $food_facet_fields = array(
        'discover_ff' => '&facet.field=menu_cuisine_fct&facet.field=menu_fct',
        'deliver_ff' => '&facet.field=menu_cuisine_fct&facet.field=menu_fct',
        'takeout_ff' => '&facet.field=menu_cuisine_fct&facet.field=menu_fct',
        'dinein_ff' => '&facet.field=menu_cuisine_fct&facet.field=feature_fct&facet.field=menu_fct',
        'reservation_ff' => '&facet.field=menu_cuisine_fct&facet.field=feature_fct&facet.field=menu_fct'
    );

    public function getAutocomplete($req) {
        if ($req['ovt'] == 'food') {
            $this->ovt = 'food';
        }
        if ($req['DeBuG'] == 404) {
            $this->debug = 1;
            $this->req = $req;
        }
                    
        $this->searchUrls = new SearchUrls();
        $this->searchUrls->setClassVariables($req);
        //pr($this->searchUrls);
        $retData = array();
        switch ($req['sst']) {
            case 'all':
                $retData = $this->getDiscoverAc($req);
                break;
            case 'deliver':
                $retData = $this->getDeliverAc($req);
                break;
            case 'takeout':
                $retData = $this->getTakeoutAc($req);
                break;
            case 'dinein':
                $retData = $this->getDineinAc($req);
                break;
            case 'reservation':
                $retData = $this->getReservationAc($req);
                break;
        }
        return $retData;
    }

    private function getDeliverAc($req) {
        $baseUrls = $this->searchUrls->getAcDeliverUrls($req);
        if ($this->ovt == 'food') {
            $cui_url = $baseUrls[0] . self::$food_facet_fields['deliver_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = ''; //no top for delivery
            $name_url = $baseUrls[2];
            return $this->prepareFoodFacetData($cui_url, $top_url, $name_url);
        } else {
            $cui_url = $baseUrls[0] . self::$res_facet_fields['deliver_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = ''; //no top for delivery
            $name_url = $baseUrls[2];
            return $this->prepareResFacetData($cui_url, $top_url, $name_url);
        }
    }

    private function getTakeoutAc($req) {
        $baseUrls = $this->searchUrls->getAcTakeoutUrls($req);
        if ($this->ovt == 'food') {
            $cui_url = $baseUrls[0] . self::$food_facet_fields['takeout_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = ''; //no top for takeout
            $name_url = $baseUrls[2];
            return $this->prepareFoodFacetData($cui_url, $top_url, $name_url);
        } else {
            $cui_url = $baseUrls[0] . self::$res_facet_fields['takeout_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = ''; //no top for takeout
            $name_url = $baseUrls[2];
            return $this->prepareResFacetData($cui_url, $top_url, $name_url);
        }
    }

    private function getDineinAc($req) {
        //print_r($req);die;
        $baseUrls = $this->searchUrls->getAcDineinUrls($req);
        if ($this->ovt == 'food') {
            $cui_url = $baseUrls[0] . self::$food_facet_fields['dinein_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = $baseUrls[1] . '&facet.field=feature_fct&facet.prefix=' . $req ['term'];
            $name_url = $baseUrls[2];
            return $this->prepareFoodFacetData($cui_url, $top_url, $name_url);
        } else {
            $cui_url = $baseUrls[0] . self::$res_facet_fields['dinein_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = $baseUrls[1] . '&facet.field=feature_fct&facet.prefix=' . $req ['term'];
            $name_url = $baseUrls[2];
            return $this->prepareResFacetData($cui_url, $top_url, $name_url);
        }
    }

    private function getReservationAc($req) {
        $baseUrls = $this->searchUrls->getAcReservationUrls($req);
        if ($this->ovt == 'food') {
            $cui_url = $baseUrls[0] . self::$food_facet_fields['reservation_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = $baseUrls[1] . '&facet.field=feature_fct&facet.prefix=' . $req ['term'];
            $name_url = $baseUrls[2];
            return $this->prepareFoodFacetData($cui_url, $top_url, $name_url);
        } else {
            $cui_url = $baseUrls[0] . self::$res_facet_fields['reservation_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = $baseUrls[1] . '&facet.field=feature_fct&facet.prefix=' . $req ['term'];
            $name_url = $baseUrls[2];
            return $this->prepareResFacetData($cui_url, $top_url, $name_url);
        }
    }

    private function getDiscoverAc($req) {
        $baseUrls = $this->searchUrls->getAcDiscoverUrls($req);
        if ($this->ovt == 'food') {
            $cui_url = $baseUrls[0] . self::$food_facet_fields['discover_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = $baseUrls[1] . '&facet.field=feature_fct&facet.prefix=' . $req ['term'];
            $name_url = $baseUrls[2];
            return $this->prepareFoodFacetData($cui_url, $top_url, $name_url);
        } else {
            $cui_url = $baseUrls[0] . self::$res_facet_fields['discover_ff'] . '&facet.prefix=' . $req ['term'];
            $top_url = $baseUrls[1] . '&facet.field=feature_fct&facet.prefix=' . $req ['term'];
            $name_url = $baseUrls[2];
            return $this->prepareResFacetData($cui_url, $top_url, $name_url);
        }
    }

    private function prepareResFacetData($cui_url, $top_url = '', $name_url = '') {
        $retData = array();
        $cui_url = preg_replace('/\s+/', '%20', $cui_url);
        $urlResponse = SearchHelpers::getCurlUrlData($cui_url);
        if ($urlResponse['status_code'] == 200) {
            $responseArr = json_decode($urlResponse['data'], true);
            $usableData = $responseArr['facet_counts']['facet_fields'];
            foreach ($usableData as $facetFieldName => $data) {
                $count = min(array(10, count($data) - 1)); //atmost 3 suggestions per field
                for ($i = 0; $i < $count; $i+=2) {
                    $retData['data'][] = array(
                        'data_type' => self::$res_facet_map[$facetFieldName],
                        'res_name1' => $data[$i]
                            //'count' => $data[$i + 1]
                    );
                }
            }
            if ($this->debug == 1) {//debug
                $retData['data'][] = array('data_type' => 'req', 'res_name1' => $this->req);
                $retData['data'][] = array('data_type' => 'cui_url', 'res_name1' => SearchHelpers::getDebugUrl($cui_url));
            }
        } else {
            $retData['status_code']['error'] = $urlResponse['status_code'];
        }

        //top autosuggestion
        if (count($retData) < 5 && $top_url != '') {
            $top_url = preg_replace('/\s+/', '%20', $top_url);
            $urlResponse = SearchHelpers::getCurlUrlData($top_url);
            if ($urlResponse['status_code'] == 200) {
                $responseArr = json_decode($urlResponse['data'], true);
                $usableData = $responseArr['facet_counts']['facet_fields'];
                foreach ($usableData as $facetFieldName => $data) {
                    $count = min(array(10, count($data) - 1)); //atmost 3 suggestions per field
                    for ($i = 0; $i < $count; $i+=2) {
                        $retData['data'][] = array(
                            'data_type' => self::$res_facet_map[$facetFieldName],
                            'res_name1' => $data[$i]
                        );
                    }
                }
                if ($this->debug == 1) {
                    $retData['data'][] = array('data_type' => 'top_url', 'res_name1' => SearchHelpers::getDebugUrl($top_url));
                }
            }
        }

        //res_name arbit auto suggest if count is less than 5
        if (count($retData) < 5 && $name_url != '') {
            //show atmost 5 restaurant name in autosuggestion
            $name_url = $name_url . '&start=0&rows=5&fl=res_name&facet=off&qf=res_eng';
            $name_url = preg_replace('/\s+/', '%20', $name_url);
            $urlResponse = SearchHelpers::getCurlUrlData($name_url);
            if ($urlResponse['status_code'] == 200) {
                $responseArr = json_decode($urlResponse['data'], true);
                $usableData = $responseArr['response']['docs'];
                $d = array();
                foreach ($usableData as $data) {
                    $d[strtolower($data['res_name'])] = 'restaurant';
                }
                foreach ($d as $k => $v) {
                    $retData['data'][] = array('data_type' => $v, 'res_name1' => $k);
                }
            }
            //debugurl
            if ($this->debug == 1) {//debug
                $retData['data'][] = array('data_type' => 'name_url', 'res_name1' => SearchHelpers::getDebugUrl($name_url));
            }
        }
        if ($this->debug == 1) {//for debugging
            $retData['data'][] = array('data_type' => 'req', 'res_name1' => $this->req);
        }
        return $retData;
    }

    private function prepareFoodFacetData($cui_url, $top_url = '', $name_url = '') {

        //cuisines and menu autosuggest
        $cui_url = preg_replace('/\s+/', '%20', $cui_url);
        $urlResponse = SearchHelpers::getCurlUrlData($cui_url);
        if ($urlResponse['status_code'] == 200) {
            $responseArr = json_decode($urlResponse['data'], true);
            $usableData = $responseArr['facet_counts']['facet_fields'];
            $retData = array();
            foreach ($usableData as $facetFieldName => $group) {
                $count = min(array(6, count($group) - 1)); //atmost 5 suggestions per field
                for ($i = 0; $i < $count; $i+=2) {
                    $retData['data'][] = array(
                        'data_type' => self::$food_facet_map[$facetFieldName],
                        'res_name1' =>  preg_replace('/&amp;/', '&', $group[$i])//$group[$i]//iconv(mb_detect_encoding($group[$i], mb_detect_order(), true), "UTF-8", $group[$i])                        //'count' => $data[$i + 1]
                    );
                }
            }
            //debug
            if ($this->debug == 1) {
                $retData['data'][] = array('data_type' => '', 'res_name1' => SearchHelpers::getDebugUrl($cui_url));
            }
        } else {
            $retData['status_code']['error'] = $urlResponse['status_code'];
        }

        //top autosuggestion
        if (count($retData) < 5 && $top_url != '') {
            $top_url = preg_replace('/\s+/', '%20', $top_url);
            $urlResponse = SearchHelpers::getCurlUrlData($top_url);
            if ($urlResponse['status_code'] == 200) {
                $responseArr = json_decode($urlResponse['data'], true);
                $usableData = $responseArr['facet_counts']['facet_fields'];
                foreach ($usableData as $facetFieldName => $data) {
                    $count = min(array(10, count($data) - 1)); //atmost 2 suggestions per field
                    for ($i = 0; $i < $count; $i+=2) {
                        $retData['data'][] = array(
                            'data_type' => self::$food_facet_map[$facetFieldName],
                            'res_name1' => $data[$i]
                        );
                    }
                }
                if ($this->debug == 1) {
                    $retData['data'][] = array('data_type' => '', 'res_name1' => SearchHelpers::getDebugUrl($cui_url));
                }
            }
        }

        //res_name autosuggestion
        if (count($retData) < 1 && $name_url != '') {
            //atmost one restaurant name in autosuggestion
            $name_url = $name_url . '&start=0&rows=1&fl=score&group=true&group.field=menu_fct&facet=off&qf=menu_eng';
            $name_url = preg_replace('/\s+/', '%20', $name_url);
            //echo $sec_url;die;
            $urlResponse = SearchHelpers::getCurlUrlData($name_url);
            if ($urlResponse['status_code'] == 200) {
                $responseArr = json_decode($urlResponse['data'], true);
                $usableData = $responseArr['grouped']['menu_fct']['groups'];
                foreach ($usableData as $group) {
                    $retData['data'][] = array('data_type' => 'food', 'res_name1' => $group['groupValue']);
                }
            }
            if ($this->debug == 1) {
                $retData['data'][] = array('data_type' => '', 'res_name1' => SearchHelpers::getDebugUrl($cui_url));
            }
        }

        if ($this->debug == 1) {
            $retData['data'][] = array('data_type' => 'req', 'res_name1' => $this->req);
        }
        return $retData;
    }

}

?>