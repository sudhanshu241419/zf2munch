<?php

namespace Solr;

use Solr\SearchUrls;
use Solr\SearchHelpers;

class Recommendations {

    /** 
     * @var Solr\SearchUrls */
    public $su; //instance of SearchUrls class
    
    private $debug = 0;
    private $reco_date = '';
    private $request = array();
    private $reco_type_url = array();
    private $res_count = 1;

    public function getRecommendations(&$retData, SearchUrls $su, $req, $resCount = 1) {
        //echo $resCount;die;
        if ($resCount == 0) {
            $this->res_count = 0;
        }
        //print_r($req);die;
        $this->request = $req;
        if ($req['DeBuG'] == 404) {
            $this->debug = 1;
        }
        $this->reco_date = $req['sdate'];
        $this->su = $su;
        switch ($req ['sst']) {
            case 'deliver' :
                $this->getDeliverReco($retData, $req);
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
        if ($this->debug) {
            $retData['reco_urls'] = $this->reco_type_url;
        }
    }

    public function getDeliverReco(&$retData, $req) {
        // city_id filter
        $fixed_part = 'rows=0' . $this->su->nbd_cities;
        $fixed_part .= '&pt=' . $this->su->latlong;
        // near time reco
        if ($this->su->time < 2300) {
            $this->setDeliverNearTimeReco($retData, $fixed_part);
        }
        if ($req['price'] > 0 && $req['price'] < 5) {
            $this->setDeliverPriceChangeReco($retData, $fixed_part, $req);
        }
        $this->setDeliverTakeoutReco($retData, $fixed_part);
        $this->setDeliverNoQCPReco($retData, $fixed_part);
        if (count($retData['data']) == 0) {
            $this->setResNameReco($retData, $req);
        }
    }

    public function getTakeoutReco(&$retData, $req) {
        // city_id filter
        $fixed_part = 'rows=0' . $this->su->nbd_cities;
        $fixed_part .= '&pt=' . $this->su->latlong;
        // near time reco
        if ($this->su->time < 2330) {
            $this->setTakeoutNearTimeReco($retData, $fixed_part);
        }
        if ($req['price'] > 0 && $req['price'] < 5) {
            $this->setTakeoutPriceChangeReco($retData, $fixed_part, $req);
        }
        $this->setTakeoutDeliverReco($retData, $fixed_part);
        $this->setTakeoutNoQCPReco($retData, $fixed_part);
        if (count($retData['data']) == 0) {
            $this->setResNameReco($retData, $req);
        }
    }

    public function getDineinReco(&$retData, $req) {
        $fixed_part = 'rows=0' . $this->su->nbd_cities;
        $fixed_part .= '&pt=' . $this->su->latlong;
        $this->setDineinReserveReco($retData, $fixed_part, $req);
        if ($req['price'] > 0 && $req['price'] < 5) {
            $this->setDineinPriceChangeReco($retData, $fixed_part, $req);
        }
        $this->setDineinNoQCPReco($retData, $fixed_part, $req);
        if (count($retData['data']) == 0) {
            $this->setResNameReco($retData, $req);
        }
    }

    public function getReservationReco(&$retData, $req) {
        $fixed_part = 'rows=0' . $this->su->nbd_cities;
        $fixed_part .= '&pt=' . $this->su->latlong;
        if ($req['price'] > 0 && $req['price'] < 5) {
            $this->setReservePriceChangeReco($retData, $fixed_part, $req);
        }
        $this->setReserveNoQCPReco($retData, $fixed_part, $req);
        if (count($retData['data']) == 0) {
            $this->setResNameReco($retData, $req);
        }
    }

    private function setDeliverNearTimeReco(&$retData, $fixed_url_part) {
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$deliver_global_fq;
        $solrParams .= $this->su->search_q . $this->su->search_cui . $this->su->search_top;
        $solrParams .= $this->su->price_fq;
        $solrParams .= $this->su->atavfq->getDeliverAtAvFq();
        $urlWithouTime = $baseUrl . $fixed_url_part . $solrParams;
        $time_filters = $this->getDeliverNearTimeFilters();
        foreach ($time_filters as $filter) {
            $url = $urlWithouTime . $filter ['timfq'];
            // echo $url;die;
            $count = $this->getRecoUrlCount($url);
            if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
                $retData ['reco'] [] = array(
                    'date' => $this->reco_date,
                    'reco_for' => 'deliver',
                    'time' => $this->formatRecoTime($filter ['time']),
                    'count' => $count,
                    'reco_type' => 'neartime'
                );
            }
            if ($this->debug) {
                $this->reco_type_url[] = array(
                    'reco_for' => 'deliver',
                    'time' => $this->formatRecoTime($filter ['time']),
                    'reco_type' => 'neartime',
                    'count' => $count,
                    'url' => SearchHelpers::getDebugUrl($url)
                );
            }
        }
    }

    private function setTakeoutNearTimeReco(&$retData, $fixed_part) {
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$takeout_global_fq;
        $solrParams .= $this->su->search_q . $this->su->search_cui . $this->su->search_top;
        $solrParams .= $this->su->price_fq;
        $solrParams .= $this->su->atavfq->getTakeoutAtAvFq();
        $urlWithouTime = $baseUrl . $fixed_part . $solrParams;
        $time_filters = $this->getTakeoutNearTimeFilters();
        foreach ($time_filters as $filter) {
            $url = $urlWithouTime . $filter ['timfq'];
            // echo $url;die;
            $count = $this->getRecoUrlCount($url);
            if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
                $retData ['reco'] [] = array(
                    'date' => $this->reco_date,
                    'reco_for' => 'takeout',
                    'time' => $this->formatRecoTime($filter ['time']),
                    'count' => $count,
                    'reco_type' => 'neartime'
                );
            }
            if ($this->debug) {
                $this->reco_type_url[] = array(
                    'reco_for' => 'takeout',
                    'time' => $this->formatRecoTime($filter ['time']),
                    'reco_type' => 'neartime',
                    'count' => $count,
                    'url' => SearchHelpers::getDebugUrl($url)
                );
            }
        }
    }

    private function getDeliverNearTimeFilters() {
        $times = array();
        // time restrictions
        $day = $this->su->day; // solr field for open time query
        $time = $this->su->time;
        $i = 0;
        do {
            $i++;
            $times [] = array(
                'time' => $time,
                'timfq' => $this->su->getDeliverTimeFq($day, $time));
            $add = $time % 100 == 0 ? 30 : 70;
            $time += $add;
        } while ($i < 3);
        return $times;
    }

    private function getTakeoutNearTimeFilters() {
        $times = array();
        // time restrictions
        $day = $this->su->day; // solr field for open time query
        $time = $this->su->time;
        $i = 0;
        do {
            $i++;
            $times [] = array(
                'time' => $time,
                'timfq' => $this->su->getTakeoutTimeFq($day, $time));
            $add = $time % 100 == 0 ? 30 : 70;
            $time += $add;
        } while ($i < 3);
        return $times;
    }

    private function setDeliverPriceChangeReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$deliver_global_fq;
        $solrParams .= $this->su->search_q . $this->su->search_cui . $this->su->search_top;
        $recoPrice = $req['price'] + 1;
        $solrParams .= '&fq=r_price_num:[*+TO+' . $recoPrice . ']';
        $solrParams .= $this->su->atavfq->getDeliverAtAvFq();
        $solrParams .= $this->su->getDeliverTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        //echo $url;die;
        $count = $this->getRecoUrlCount($url);
        //echo $count;die;
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'date' => $this->reco_date,
                'reco_for' => 'deliver',
                'time' => $this->formatRecoTime($this->su->time),
                'count' => $count,
                'reco_type' => 'price',
                'price' => $recoPrice
            );
        }

        if ($this->debug) {
            $this->reco_type_url[] = array(
                'reco_for' => 'deliver',
                'time' => $this->formatRecoTime($this->su->time),
                'reco_type' => 'price',
                'count' => $count,
                'url' => SearchHelpers::getDebugUrl($url)
            );
        }
    }

    private function setTakeoutPriceChangeReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$takeout_global_fq;
        $solrParams .= $this->su->search_q . $this->su->search_cui . $this->su->search_top;
        $recoPrice = $req['price'] + 1;
        $solrParams .= '&fq=r_price_num:[*+TO+' . $recoPrice . ']';
        $solrParams .= $this->su->atavfq->getTakeoutAtAvFq();
        $solrParams .= $this->su->getTakeoutTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        //echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'date' => $this->reco_date,
                'reco_for' => 'takeout',
                'time' => $this->formatRecoTime($this->su->time),
                'count' => $count,
                'reco_type' => 'price',
                'price' => $recoPrice
            );
        }

        if ($this->debug) {
            $this->reco_type_url[] = array(
                'reco_for' => 'takeout',
                'time' => $this->formatRecoTime($this->su->time),
                'reco_type' => 'price',
                'price' => $recoPrice,
                'count' => $count,
                'url' => SearchHelpers::getDebugUrl($url)
            );
        }
    }

    private function setDineinPriceChangeReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$dinein_global_fq;
        $solrParams .= SearchHelpers::getMealsFq($req['rrt']);
        $solrParams .= $this->su->search_q . $this->su->search_cui . $this->su->search_top;
        $recoPrice = $req['price'] + 1;
        $solrParams .= '&fq=r_price_num:[*+TO+' . $recoPrice . ']';
        $solrParams .= $this->su->atavfq->getDineinAtAvFq();
        $solrParams .= $this->su->getDineinTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        //echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'date' => $this->reco_date,
                'reco_for' => 'dinein',
                'count' => $count,
                'reco_type' => 'price',
                'price' => $recoPrice
            );
        }

        if ($this->debug) {
            $this->reco_type_url[] = array(
                'reco_for' => 'dinein',
                'reco_type' => 'price',
                'price' => $recoPrice,
                'count' => $count,
                'url' => SearchHelpers::getDebugUrl($url)
            );
        }
    }

    private function setReservePriceChangeReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$reserve_global_fq;
        $solrParams .= SearchHelpers::getMealsFq($req['rrt']);
        $solrParams .= $this->su->search_q . $this->su->search_cui . $this->su->search_top;
        $recoPrice = $req['price'] + 1;
        $solrParams .= '&fq=r_price_num:[*+TO+' . $recoPrice . ']';
        $solrParams .= $this->su->atavfq->getReserveAtAvFq();
        $solrParams .= $this->su->getReservationTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        //echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'url' => ($this->debug) ? SearchHelpers::getDebugUrl($url) : '',
                'date' => $this->reco_date,
                'reco_for' => 'reserve',
                'count' => $count,
                'reco_type' => 'price',
                'price' => $recoPrice
            );
        }
    }

    private function setDeliverTakeoutReco(&$retData, $fixed_part) {
        //print_r($request);die;
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$takeout_global_fq;
        $solrParams .= $this->su->search_q . $this->su->search_cui . $this->su->search_top;
        $solrParams .= $this->su->price_fq;
        $solrParams .= $this->su->atavfq->getTakeoutAtAvFq();
        $solrParams .= $this->su->getTakeoutTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        // echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'url' => ($this->debug) ? SearchHelpers::getDebugUrl($url) : '',
                'date' => $this->reco_date,
                'reco_for' => 'deliver',
                'time' => $this->formatRecoTime($this->su->time),
                'count' => $count,
                'reco_type' => 'takeout'
            );
        }
    }

    private function setTakeoutDeliverReco(&$retData, $fixed_part) {
        //print_r($request);die;
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$deliver_global_fq;
        $solrParams .= $this->su->search_q . $this->su->search_cui . $this->su->search_top;
        $solrParams .= $this->su->price_fq;
        $solrParams .= $this->su->atavfq->getDeliverAtAvFq();
        $solrParams .= $this->su->getTakeoutTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        // echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'url' => ($this->debug) ? SearchHelpers::getDebugUrl($url) : '',
                'date' => $this->reco_date,
                'reco_for' => 'deliver',
                'time' => $this->formatRecoTime($this->su->time),
                'count' => $count,
                'reco_type' => 'takeout'
            );
        }
    }

    private function setDineinReserveReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$reserve_global_fq;
        $solrParams .= SearchHelpers::getMealsFq($req['rrt']);
        $solrParams .= $this->su->price_fq;
        $solrParams .= $this->su->search_q . $this->su->search_cui . $this->su->search_top;
        $solrParams .= $this->su->atavfq->getReserveAtAvFq();
        $solrParams .= $this->su->getDineinTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        //echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'url' => ($this->debug) ? SearchHelpers::getDebugUrl($url) : '',
                'date' => $this->reco_date,
                'reco_for' => 'dinein',
                'count' => $count,
                'reco_type' => 'reserve'
            );
        }
    }

    private function setDeliverNoQCPReco(&$retData, $fixed_part) {
        //print_r($request);die;
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$deliver_global_fq;
        $solrParams .= $this->su->atavfq->getDeliverAtAvFq();
        $solrParams .= $this->su->getDeliverTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        // echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'date' => $this->reco_date,
                'reco_for' => 'deliver',
                'time' => $this->formatRecoTime($this->su->time),
                'count' => $count,
                'reco_type' => 'no_q_dt'
            );
        }
        if ($this->debug) {
            $this->reco_type_url[] = array(
                'reco_for' => 'deliver',
                'reco_type' => 'no_q_dt',
                'time' => $this->formatRecoTime($this->su->time),
                'count' => $count,
                'url' => SearchHelpers::getDebugUrl($url)
            );
        }
    }

    private function setTakeoutNoQCPReco(&$retData, $fixed_part) {
        //print_r($request);die;
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$takeout_global_fq;
        $solrParams .= $this->su->atavfq->getTakeoutAtAvFq();
        $solrParams .= $this->su->getTakeoutTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        // echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'date' => $this->reco_date,
                'reco_for' => 'takeout',
                'time' => $this->formatRecoTime($this->su->time),
                'count' => $count,
                'reco_type' => 'no_q_dt'
            );
        }

        if ($this->debug) {
            $this->reco_type_url[] = array(
                'reco_for' => 'takeout',
                'reco_type' => 'no_q_dt',
                'time' => $this->formatRecoTime($this->su->time),
                'count' => $count,
                'url' => SearchHelpers::getDebugUrl($url)
            );
        }
    }

    private function setDineinNoQCPReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$dinein_global_fq;
        $solrParams .= SearchHelpers::getMealsFq($req['rrt']);
        $solrParams .= $this->su->atavfq->getDineinAtAvFq();
        $solrParams .= $this->su->getDineinTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        //echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'date' => $this->reco_date,
                'reco_for' => 'dinein',
                'count' => $count,
                'reco_type' => 'no_q_dt'
            );
        }

        if ($this->debug) {
            $this->reco_type_url[] = array(
                'reco_for' => 'dinein',
                'reco_type' => 'no_q_dt',
                'date' => $this->reco_date,
                'count' => $count,
                'url' => SearchHelpers::getDebugUrl($url)
            );
        }
    }

    private function setReserveNoQCPReco(&$retData, $fixed_part, $req) {
        $baseUrl = $this->su->res_url;
        $solrParams = SearchHelpers::$reserve_global_fq;
        $solrParams .= SearchHelpers::getMealsFq($req['rrt']);
        $solrParams .= $this->su->atavfq->getReserveAtAvFq();
        $solrParams .= $this->su->getReservationTimeFq();
        $url = $baseUrl . $fixed_part . $solrParams;
        //echo $url;die;
        $count = $this->getRecoUrlCount($url);
        if ($count > 3 || ($this->res_count == 0 && $count > 0)) {
            $retData ['reco'] [] = array(
                'date' => $this->reco_date,
                'reco_for' => 'reserve',
                'count' => $count,
                'reco_type' => 'no_q_dt'
            );
        }

        if ($this->debug) {
            $this->reco_type_url[] = array(
                'reco_for' => 'reserve',
                'reco_type' => 'no_q_dt',
                'date' => $this->reco_date,
                'count' => $count,
                'url' => SearchHelpers::getDebugUrl($url)
            );
        }
    }

    private function getRecoUrlCount($unescapedurl) {
        $count = 0;
        $url = preg_replace('/\s+/', '%20', $unescapedurl);
        $output = SearchHelpers::getCurlUrlData($url);
        if ($output ['status_code'] == 200) {
            $responseArr = json_decode($output ['data'], true);
            $count = $responseArr ['response'] ['numFound'];
        }
        return $count;
    }

    private function setResNameReco(&$retData, $req) {
        //print_r($req);die;
        $fixed = 'rows=1&fl=res_name,res_id' . $this->su->nbd_cities;
        $fixed .= '&pt=' . $this->su->latlong;
        $baseUrl = $this->su->res_url;
        $q = str_replace('||', " ", $req['sq']);
        $query = '&q=res_fct:("' . $q . '")';
        $url = $baseUrl . $fixed . $query;
        // echo $url;die;
        $data = $this->getRecoResNameId($url);
        $count = $data['count'];
        if ($count > 0) {
            $retData ['reco'] [] = array(
                'date' => $this->reco_date,
                'reco_for' => $req['sst'],
                'time' => $this->formatRecoTime($this->su->time),
                'count' => $count,
                'reco_type' => 'res_name',
                'res_data' => array('res_name' => $data['res_name'], 'res_id' => $data['res_id'])
            );
        }

        if ($this->debug) {
            $this->reco_type_url[] = array(
                'reco_for' => $req['sst'],
                'reco_type' => 'res_name',
                'date' => $this->reco_date,
                'count' => $count,
                'url' => SearchHelpers::getDebugUrl($url)
            );
        }
    }

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
        $data = array('count' => $count, 'res_name' => $res_name, 'res_id' => $res_id);
        return $data;
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

}

?>
