<?php

namespace Search\Controller;

use MCommons\Controller\AbstractRestfulController;
use Search\SearchFunctions;
use Solr\SearchBanners;

/**
 * Description of SearchBannersController
 * APIs for search banner & retargeting banners 
 * 
 * @author arti
 */
class SearchBannersController extends AbstractRestfulController {
        
    public function getList() {
        $origReq = $this->getRequest()->getQuery()->toArray();
        
        if(!$this->valid($origReq)){
            return $this->getInvalidResponse('invalid reqtype or reqval');
        }
        
        $sb = new SearchBanners();
        switch ($origReq['reqtype']) {
            case 'tagged':
                $response = $sb->getTaggedData($origReq);
                break;
            case 'retarget':
                $response = $sb->getRetargetData($origReq);
                break;
            case 'gallery':
                if(!isset($origReq['reqval']) || !is_numeric($origReq['reqval'])){
                    return $this->getInvalidResponse('reqval missing or not a valid restaurant id.');
                }
                $response = $sb->getGallaryData(intval($origReq['reqval']));
                break;
            default:
                $response = $this->getInvalidResponse('invalid reqtype');
                break;
        }
        return $response;
    }
    
    private function valid($req){
        if(!isset($req['reqtype'])){
            return false;
        }
        return true;
    }
    
    private function getInvalidResponse($errMsg){
        return ['status' => 'FAIL', 'data' => [], 'error' => $errMsg];
    }

}

