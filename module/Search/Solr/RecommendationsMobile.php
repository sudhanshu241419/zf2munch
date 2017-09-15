<?php

namespace Solr;

use Solr\SearchUrlsMobile;
use Solr\SearchHelpers;

class RecommendationsMobile {

    /**
     * @var  Solr\SearchUrlsMobile */
    public $su_mob;
    
    private $debug = 0;
    private $reco_date = '';
    private $request = array();
    private $res_count = 1;
    
    /**
     * RECO TYPES STRINGS
     */
    const RT_NO_Q_DT = 'no_q_dt';
    const RT_NEAR_TIME = 'neartime';
    const RT_PRICE = 'price';
    const RT_TAKEOUT = 'takeout';
    const RT_DELIVERY = 'delivery';
    const RT_RESERVATION = 'reservation';
    const RT_RES_NAME = 'res_name';


    /**
     * Show reco only if count is more than this threshold
     * @var int 
     */
    private $reco_threshold = 3;
    
    /**
     * has rows, city_id and pt parameters of solr query url
     * @var string 
     */
    private $url_fixed_part = '';

    /**
     * Append recommendations in mobile search api
     * @param array $retData append reco data in this array
     * @param SearchUrlsMobile $su instance of this class
     * @param array $req request array
     * @param int $resCount
     */
    public function getRecommendations(&$retData, SearchUrlsMobile $su, $req) {
        $this->setClassVars($su,$req, $retData['count']);
        $retData ['reco'] = array();
        switch ($req ['tab']) {
            case 'delivery' :
                $this->getDeliveryReco($retData, $req);
                break;
            case 'takeout' :
                $this->getTakeoutReco($retData, $req);
                break;
            case 'dinein' :
                $this->getDineinReco($retData, $req);
                break;
            case 'reservation' :
                $this->getReservationReco($retData, $req);
                break;
            default :
                break;
        }
    }
    
    /**
     * 
     * @param SearchUrlsMobile $su
     * @param array $req
     * @param int $resCount num results matching original search
     */
    private function setClassVars($su, $req, $resCount) {
        $this->request = $req;
        if ($resCount == 0) {
            $this->res_count = $resCount;
        }
        if ($req['DeBuG'] == 404) {
            $this->debug = 1;
        }
        $this->reco_date = $req['sdate'];
        $this->su_mob = $su;
        $this->url_fixed_part = 'rows=' . $req['rows'] . '&pt=' . $this->su_mob->atavfq->latlong;
    }

    private function getDeliveryReco(&$retData, $req) {
        // near time reco
        if ($this->su_mob->time < 2300) {
            $this->setDeliveryNearTimeReco($retData, $this->url_fixed_part);
        }
        if ($req['price'] > 0 && $req['price'] < 5) {
            $this->setDeliverPriceChangeReco($retData, $this->url_fixed_part, $req);
        }
        $this->setDeliverTakeoutReco($retData, $this->url_fixed_part);
        $this->setDeliverNoQCPReco($retData, $this->url_fixed_part);
        if (count($retData['data']) == 0) {
            $this->setResNameReco($retData, $req);
        }
    }

    private function getTakeoutReco(&$retData, $req) {
        // near time reco
        if ($this->su_mob->time < 2330) {
            $this->setTakeoutNearTimeReco($retData, $this->url_fixed_part);
        }
        if ($req['price'] > 0 && $req['price'] < 5) {
            $this->setTakeoutPriceChangeReco($retData, $this->url_fixed_part, $req);
        }
        $this->setTakeoutDeliverReco($retData, $this->url_fixed_part);
        $this->setTakeoutNoQCPReco($retData, $this->url_fixed_part);
        if (count($retData['data']) == 0) {
            $this->setResNameReco($retData, $req);
        }
    }

    private function getDineinReco(&$retData, $req) {
        $this->setDineinReserveReco($retData, $this->url_fixed_part, $req);
        if ($req['price'] > 0 && $req['price'] < 5) {
            $this->setDineinPriceChangeReco($retData, $this->url_fixed_part, $req);
        }
        $this->setDineinNoQCPReco($retData, $this->url_fixed_part, $req);
        if (count($retData['data']) == 0) {
            $this->setResNameReco($retData, $req);
        }
    }

    private function getReservationReco(&$retData, $req) {
        if ($req['price'] > 0 && $req['price'] < 5) {
            $this->setReservePriceChangeReco($retData, $this->url_fixed_part, $req);
        }
        $this->setReserveNoQCPReco($retData, $this->url_fixed_part, $req);
        if (count($retData['data']) == 0) {
            $this->setResNameReco($retData, $req);
        }
    }

    private function setDeliveryNearTimeReco(&$retData, $fixed_url_part) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$deliver_global_fq;
        $solrParams .= $this->su_mob->search_q . $this->su_mob->search_cui . $this->su_mob->search_top;
        $solrParams .= $this->su_mob->price_fq;
        $solrParams .= $this->su_mob->atavfq->getDeliverAtAvFq();
        $urlWithouTime = $baseUrl . $fixed_url_part . $solrParams;
        $time_filters = $this->getDeliverNearTimeFilters();
        foreach ($time_filters as $filter) {
            $url = $urlWithouTime . $filter ['timefq'];
            $recoData = $this->getRecoUrlData($url);
            if ($recoData['count'] > $this->reco_threshold || ($this->res_count == 0 && $recoData['count'] > 0)) {
                $time = $this->formatRecoTime($filter ['time']);
                $retData ['reco'][] = $this->getGenericReco(self::RT_NEAR_TIME, 'delivery', $this->reco_date, $time, $recoData, $url);
            }
        }
    }

    private function setTakeoutNearTimeReco(&$retData, $fixed_part) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$takeout_global_fq;
        $solrParams .= $this->su_mob->search_q . $this->su_mob->search_cui . $this->su_mob->search_top;
        $solrParams .= $this->su_mob->price_fq;
        $solrParams .= $this->su_mob->atavfq->getTakeoutAtAvFq();
        $urlWithouTime = $baseUrl . $fixed_part . $solrParams;
        $time_filters = $this->getTakeoutNearTimeFilters();
        foreach ($time_filters as $filter) {
            $url = $urlWithouTime . $filter ['timefq'];
            $recoData = $this->getRecoUrlData($url);
            if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
                $time = $this->formatRecoTime($filter ['time']);
                $retData ['reco'][] = $this->getGenericReco(self::RT_NEAR_TIME, 'takeout', $this->reco_date, $time, $recoData, $url);
            }
        }
    }

    private function getDeliverNearTimeFilters() {
        $times = array();
        // time restrictions
        $day = $this->su_mob->day; // solr field for open time query
        $time = $this->su_mob->time;
        $i = 0;
        do {
            $i++;
            $times [] = array(
                'time' => $time,
                'timefq' => SearchHelpers::getDeliverTimeFq($day, $time));
            $add = $time % 100 == 0 ? 30 : 70;
            $time += $add;
        } while ($i < 3);
        return $times;
    }

    private function getTakeoutNearTimeFilters() {
        $times = array();
        // time restrictions
        $day = $this->su_mob->day; // solr field for open time query
        $time = $this->su_mob->time;
        $i = 0;
        do {
            $i++;
            $times [] = array(
                'time' => $time,
                'timefq' => SearchHelpers::getTakeoutTimeFq($day, $time));
            $add = $time % 100 == 0 ? 30 : 70;
            $time += $add;
        } while ($i < 3);
        return $times;
    }

    private function setDeliverPriceChangeReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$deliver_global_fq;
        $solrParams .= $this->su_mob->search_q . $this->su_mob->search_cui . $this->su_mob->search_top;
        $recoPrice = $req['price'] + 1;
        $solrParams .= '&fq=r_price_num:[*+TO+' . $recoPrice . ']';
        $solrParams .= $this->su_mob->atavfq->getDeliverAtAvFq();
        $solrParams .= SearchHelpers::getDeliverTimeFq($this->su_mob->day, $this->su_mob->time);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $time = $this->formatRecoTime($this->su_mob->time);
            $retData ['reco'] [] = $this->getPriceChangeReco($recoPrice, 'delivery', $this->reco_date, $time, $recoData, $url);
        }
    }

    private function setTakeoutPriceChangeReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$takeout_global_fq;
        $solrParams .= $this->su_mob->search_q . $this->su_mob->search_cui . $this->su_mob->search_top;
        $recoPrice = $req['price'] + 1;
        $solrParams .= '&fq=r_price_num:[*+TO+' . $recoPrice . ']';
        $solrParams .= $this->su_mob->atavfq->getTakeoutAtAvFq();
        $solrParams .= SearchHelpers::getTakeoutTimeFq($this->su_mob->day, $this->su_mob->time);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count']> 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $time = $this->formatRecoTime($this->su_mob->time);
            $retData ['reco'] [] = $this->getPriceChangeReco($recoPrice, 'takeout', $this->reco_date, $time, $recoData, $url);
        }
    }

    private function setDineinPriceChangeReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$dinein_global_fq;
        $solrParams .= $this->getMealsFq($req);
        $solrParams .= $this->su_mob->search_q . $this->su_mob->search_cui . $this->su_mob->search_top;
        $recoPrice = $req['price'] + 1;
        $solrParams .= '&fq=r_price_num:[*+TO+' . $recoPrice . ']';
        $solrParams .= $this->su_mob->atavfq->getDineinAtAvFq();
        $solrParams .= SearchHelpers::getDineinTimeFq($this->su_mob->day);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count']> 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $time = $this->formatRecoTime($this->su_mob->time);
            $retData ['reco'] [] = $this->getPriceChangeReco($recoPrice, 'dinein', $this->reco_date, $time, $recoData, $url);
        }
    }

    private function setReservePriceChangeReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$reserve_global_fq;
        $solrParams .= SearchHelpers::getMealsFq($req['subtab']);;
        $solrParams .= $this->su_mob->search_q . $this->su_mob->search_cui . $this->su_mob->search_top;
        $recoPrice = $req['price'] + 1;
        $solrParams .= '&fq=r_price_num:[*+TO+' . $recoPrice . ']';
        $solrParams .= $this->su_mob->atavfq->getReserveAtAvFq();
        $solrParams .= SearchHelpers::getReservationTimeFq($this->su_mob->day);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $time = $this->formatRecoTime($this->su_mob->time);
            $retData ['reco'] [] = $this->getPriceChangeReco($recoPrice, 'reservation', $this->reco_date, $time, $recoData, $url);
        }
    }

    private function setDeliverTakeoutReco(&$retData, $fixed_part) {
        //print_r($request);die;
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$takeout_global_fq;
        $solrParams .= $this->su_mob->search_q . $this->su_mob->search_cui . $this->su_mob->search_top;
        $solrParams .= $this->su_mob->price_fq;
        $solrParams .= $this->su_mob->atavfq->getTakeoutAtAvFq();
        $solrParams .= SearchHelpers::getTakeoutTimeFq($this->su_mob->day,  $this->su_mob->time);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $time = $this->formatRecoTime($this->su_mob->time);
            $retData ['reco'] [] = $this->getGenericReco(self::RT_TAKEOUT, 'delivery', $this->reco_date, $time, $recoData, $url);
        }
    }

    private function setTakeoutDeliverReco(&$retData, $fixed_part) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$deliver_global_fq;
        $solrParams .= $this->su_mob->search_q . $this->su_mob->search_cui . $this->su_mob->search_top;
        $solrParams .= $this->su_mob->price_fq;
        $solrParams .= $this->su_mob->atavfq->getDeliverAtAvFq();
        $solrParams .= SearchHelpers::getTakeoutTimeFq($this->su_mob->day,  $this->su_mob->time);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $time = $this->formatRecoTime($this->su_mob->time);
            $retData ['reco'] [] = $this->getGenericReco(self::RT_DELIVERY, 'takeout', $this->reco_date, $time, $recoData, $url);
        }
    }

    private function setDineinReserveReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$reserve_global_fq;
        $solrParams .= SearchHelpers::getMealsFq($req['subtab']);;
        $solrParams .= $this->su_mob->price_fq;
        $solrParams .= $this->su_mob->search_q . $this->su_mob->search_cui . $this->su_mob->search_top;
        $solrParams .= $this->su_mob->atavfq->getReserveAtAvFq();
        $solrParams .= SearchHelpers::getDineinTimeFq($this->su_mob->day);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $time = 0;
            $retData ['reco'] [] = $this->getGenericReco(self::RT_RESERVATION, 'dinein', $this->reco_date, $time, $recoData, $url);
        }
    }

    private function setDeliverNoQCPReco(&$retData, $fixed_part) {
        $tab = 'delivery';
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$deliver_global_fq;
        $solrParams .= $this->su_mob->atavfq->getDeliverAtAvFq();
        $solrParams .= SearchHelpers::getDeliverTimeFq($this->su_mob->day,  $this->su_mob->time);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $time = $this->formatRecoTime($this->su_mob->time);
            $retData ['reco'] [] = $this->getGenericReco(self::RT_NO_Q_DT, 'delivery', $this->reco_date, $time, $recoData, $url);
        }
    }

    private function setTakeoutNoQCPReco(&$retData, $fixed_part) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$takeout_global_fq;
        $solrParams .= $this->su_mob->atavfq->getTakeoutAtAvFq();
        $solrParams .= SearchHelpers::getTakeoutTimeFq($this->su_mob->day, $this->su_mob->time);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $time = $this->formatRecoTime($this->su_mob->time);
            $retData ['reco'] [] = $this->getGenericReco(self::RT_NO_Q_DT, 'takeout', $this->reco_date, $time, $recoData, $url);
        }
    }

    private function setDineinNoQCPReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$dinein_global_fq;
        $solrParams .= SearchHelpers::getMealsFq($req['subtab']);
        $solrParams .= $this->su_mob->atavfq->getDineinAtAvFq();
        $solrParams .= SearchHelpers::getDineinTimeFq($this->su_mob->day);
        $url = $baseUrl . $fixed_part . $solrParams;
        //echo $url;die;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $retData ['reco'] [] = $this->getGenericReco(self::RT_NO_Q_DT, 'dinein', $this->reco_date, 0, $recoData, $url);
        }
    }

    private function setReserveNoQCPReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su_mob->res_url;
        $solrParams = SearchHelpers::$reserve_global_fq;
        $solrParams .= SearchHelpers::getMealsFq($req['subtab']);
        $solrParams .= $this->su_mob->atavfq->getReserveAtAvFq();
        $solrParams .= SearchHelpers::getReservationTimeFq($this->su_mob->day);
        $url = $baseUrl . $fixed_part . $solrParams;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $retData ['reco'] [] = $this->getGenericReco(self::RT_NO_Q_DT, 'reservation', $this->reco_date, 0, $recoData, $url);
        }
    }

    /**
     * 
     * @param string $unescapedurl solr url
     * @return array with keys count and data
     */
    private function getRecoUrlData($unescapedurl) {
        $count = 0;
        $data = array();
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        $output = SearchHelpers::getCurlUrlData($url);
        if ($output ['status_code'] == 200) {
            $responseArr = json_decode($output ['data'], true);
            $count = $responseArr ['response'] ['numFound'];
            $data = $responseArr ['response'] ['docs'];
            foreach($data as $i => $res){
                $data[$i]['distance'] = round($data[$i]['distance'], 2);
            }
        }
        return array('count'=>$count, 'data' => $data);
    }

    private function setResNameReco(&$retData, $req) {
        $fixed = 'rows=1' ;
        $fixed .= '&pt=' . $this->su_mob->atavfq->latlong;
        $baseUrl = $this->su_mob->res_url;
        $query = '&q=res_fct:("' . $req['q'] . '")';
        $url = $baseUrl . $fixed . $query;
        $recoData = $this->getRecoUrlData($url);
        if ($recoData['count'] > 3 || ($this->res_count == 0 && $recoData['count'] > 0)) {
            $retData ['reco'] [] = $this->getGenericReco(self::RT_RES_NAME, $req['tab'], $this->reco_date, 0, $recoData, $url);
        }
    }

    /**
     * 
     * @param string $unescapedurl
     * @return array with keys count, res_name, and res_id
     */
    private function getRecoResNameId($unescapedurl) {
        $count = 0;
        $res_name = '';
        $res_id = '';
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        $output = SearchHelpers::getCurlUrlData($url);
        if ($output ['status_code'] == 200) {
            $responseArr = json_decode($output ['data'], true);
            $count = $responseArr ['response'] ['numFound'];
            if ($count > 0) {
                $res_name = $responseArr ['response'] ['docs'][0]['res_name'];
                $res_id = $responseArr ['response'] ['docs'][0]['res_id'];
            }
        }
        return array('count' => $count, 'res_name' => $res_name, 'res_id' => $res_id);
    }

    private function formatRecoTime($time) {
        $ret = strval($time);
        $len = strlen($ret);
        if ($len == 3) {
            return '0' . $ret;
        } elseif ($len == 1) {
            return '000' . $ret;
        } elseif ($len == 2) {
            return '00' . $ret;
        } else {
            return $ret;
        }
    }
    
    private function getPriceChangeReco($recoPrice, $tab, $date, $time, $recoData, $url = '') {
        $data = array(
            'reco_type' => 'price',
            'price' => $recoPrice,
            'tab' => $tab,
            'date' => $date,
            'time' => $time,
            'count' => $recoData['count'],
            'data' => $recoData['data'],
        );
        if ($this->debug) {
            $data['url'] = SearchHelpers::getDebugUrl($url);
        }
        return $data;
    }
    
    /**
     * 
     * @param type $recoType
     * @param type $tab
     * @param type $date
     * @param type $time
     * @param type $recoData
     * @param type $url
     * @return type
     */
    private function getGenericReco($recoType, $tab, $date, $time, $recoData, $url = '') {
        $reco = array(
            'reco_type' => $recoType,
            'tab' => $tab,
            'date' => $date,
            'time' => $time,
            'count' => $recoData['count'],
            'data' => $recoData['data'],
        );
        if ($this->debug) {
            $reco['url'] = SearchHelpers::getDebugUrl($url);
        }
        return $reco;
    }

}
