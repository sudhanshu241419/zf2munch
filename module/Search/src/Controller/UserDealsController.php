<?php

namespace Search\Controller;

use MCommons\Controller\AbstractRestfulController;
use Search\Model\UserDeals;
use Solr\SearchHelpers;
use MCommons\StaticOptions;

/**
 * Description of EtcSearchController
 * API for <b>featured</b> and <b>tagged</b> data.
 * Required Parameters are <b>reqtype</b> and <b>zip</b>
 * 
 *
 * @author arti
 */
class UserDealsController extends AbstractRestfulController {
    public $totalUnreadDeal;
    public function getList() {
        $response = ['status' => 'OK'];  
        $session = $this->getUserSession();        
        $resData = [];
        $currentDate = '';
        if($session->isLoggedIn()){    
            $userId = $session->getUserId();
            $userFunctions = new \User\UserFunctions();
            $locationData = $session->getUserDetail('selected_location');        
            $currentDate = $userFunctions->userCityTimeZone($locationData);
//            if($this->isMobile()){
//                $ud = new UserDeals();
//                $resIds = $ud->getUserDealsResIds($userId);
//                $resData = $this->getResData($resIds);            
//            }else{
                $resData = $this->userDeal($currentDate, $userId);
//            }
        }
        $response['data'] = $resData;
        $totalData = count($resData);
        $response['count'] = $totalData;
        $response['has_deal'] = $totalData;
        $response['unread_deal'] = $this->totalUnreadDeal;
        $response['image_base_path'] =  IMAGE_PATH;       
        $response['curr_time'] =$currentDate;
        return $response;
    }
    
    private function valid($req){
        if($req['uid'] == 0 ){
            return false;
        }
        return true;
    }
    
    
    private function getResData($resIds) {
        if (count($resIds) <= 0 ) {
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
        }
        return $result;
    }
    
    
    private function getInvalidResponse($errMsg){
        return ['status' => 'fail', 'data' => [], 'error' => $errMsg];
    }
    private function userDeal($currentDate, $userId) {  
        $isMobile = $this->isMobile();
        $userFunctions = new \User\UserFunctions();        
        $userFunctions->getUserDealData($currentDate, $userId, $isMobile);         
        $this->totalUnreadDeal = $userFunctions->totalUnreadDeals;
        $userFunctions->getOffer($currentDate,$userId); 
        return $userFunctions->prepairUserDealAndRestaurantData($isMobile);       
    }

}
