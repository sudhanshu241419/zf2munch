<?php

namespace Search\Controller;

use MCommons\Controller\AbstractRestfulController;
use Search\SearchFunctions;
use Solr\SearchEtc;

/**
 * Description of EtcSearchController
 * API for <b>featured</b> and <b>tagged</b> data.
 * Required Parameters are <b>reqtype</b> and <b>zip</b>
 * 
 *
 * @author arti
 */
class SearchEtcController extends AbstractRestfulController {
        
    public function getList() {
        $commonFunction = new \MCommons\CommonFunctions();
        $origReq = $this->getRequest()->getQuery()->toArray();
        
        if(!$this->valid($origReq)){
            return $this->getInvalidResponse('invalid reqtype or zip');
        }
        
        if(!isset($origReq['reqvalue'])){
            $origReq['reqvalue'] = '';
        }        
        $rows = ($origReq['reqtype'] == 'featured') ? 1 : 5; 
        
        $zip = $origReq['zip'];
        $lnt = $commonFunction->getLnt($zip);
        if(empty($lnt)){
            return $this->getInvalidResponse('invalid reqtype or zip');
        }
        
        $latlong = $lnt[0]['lat'].",".$lnt[0]['lng'];      
               
        $rawRequest = [
            'reqtype' => $origReq['reqtype'],
            'reqvalue' => $origReq['reqvalue'],
            'city_id' => 18848,
            'at' => 'street',
            'av' => 'street',
            'latlong' => $latlong, //'40.74407,-73.98522',
            'rows' => $rows,
        ];
        $searchRequest = SearchFunctions::cleanMobileSearchParams($rawRequest);

        $se = new SearchEtc();
        switch ($origReq['reqtype']) {
            case 'featured':
                $response = $se->getFeaturedData($searchRequest);
                break;
            case 'tagged':
                $response = $se->getTaggedData($searchRequest, $origReq['reqvalue']);
                break;
            default:
                $response = $this->getInvalidResponse('invalid reqtype');
                break;
        }
        return $response;
    }
    
    private function valid($req){
        if(!isset($req['reqtype']) || !isset($req['zip']) ){
            return false;
        }
        return true;
    }
    
    private function getInvalidResponse($errMsg){
        return ['status' => 'fail', 'data' => [], 'error' => $errMsg];
    }

}
