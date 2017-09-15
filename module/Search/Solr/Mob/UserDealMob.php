<?php

namespace Solr\Mob;

use Search\Model\UserDeals;
use Solr\SearchHelpers;
use MCommons\StaticOptions;

class UserDealMob {

    public function getUserDeals($origReq) {
        //pr($origReq, 1);
        $response = ['status' => 'OK'];
        if (!$this->valid($origReq)) {
            return $this->getInvalidResponse('invalid uid');
        }

        $ud = new UserDeals();
        $resIds = $ud->getUserDealsResIds($origReq['uid']);
        $response['data'] = $this->getResData($resIds);
        $response['count'] = count($resIds);
        return $response;
    }

    private function valid($req) {
        if ($req['uid'] == 0) {
            return false;
        }
        return true;
    }

    private function getResData($resIds) {
        if (count($resIds) <= 0) {
            return [];
        }
        $url = StaticOptions::getSolrUrl();
        $url .= 'hbr/hbsearch?pt=40.7127,-74.0059';
        $url .= '&fq=res_id:("' . implode('"+OR+"', $resIds) . '")';
        $result = [];
        $output = SearchHelpers::getCurlUrlData($url);
        if ($output['status_code'] == 200) {
            $responseArr = json_decode($output['data'], true);
            $result = $responseArr['response']['docs'];
            $this->updateResUserDeals($result);
        }
        return $result;
    }

    private function getInvalidResponse($errMsg) {
        return ['status' => 'fail', 'data' => [], 'error' => $errMsg];
    }
    
    private function updateResUserDeals(&$result){
        $rdc = new \Search\Model\ResDealsCoupons();
        foreach ($result as $i => $res) {
            $result[$i]['deals'] = $rdc->getResUserDeals(intval($res['res_id']));
        }
    }

}
