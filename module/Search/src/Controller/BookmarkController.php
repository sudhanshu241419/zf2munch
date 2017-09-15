<?php

namespace Search\Controller;

use MCommons\Controller\AbstractRestfulController;
use Search\SearchFunctions;
use MCommons\StaticOptions;

class BookmarkController extends AbstractRestfulController {
    
    private $debug = false;
    
	public function getList() {
        
        $this->debug = ($this->getQueryParams('DeBuG', '') == '404') ? TRUE : FALSE;
        
        $items = StaticOptions::filterRequestParams($this->getQueryParams('items', ''));
        if (empty($items)) {
            throw new \Exception("invalid items parameter", 400);
        }
        
        $type = $this->getQueryParams('type', false);
        
        if(!$type){
            throw new \Exception("invalid type", 400);
        } 
        
        $result = array();
        $restId = explode(",", $items);//$restId contain restaurnt id or menu id
        foreach($restId as $key => $restaurantId){//$restaurantId contain restaurnt id or menu id
            $cache_key = 'bm_'.$restaurantId. '_' . $type;
            $cache_data = SearchFunctions::getCacheData($cache_key, $this->debug);
            if($cache_data){
                $result[] = $cache_data;
            }else{        
                $bm = SearchFunctions::getBookmarks($restaurantId, $type);
                if (!empty($bm)) {
                    SearchFunctions::setCacheData($cache_key, $bm, 86400);
                    $result[] = $bm;
                }
           }
        }
        return $result;
    }
    
    
    public function create($data) {
        $this->debug = ($this->getQueryParams('DeBuG', '') == '404') ? TRUE : FALSE;
        
        $items = isset($data['items'])?$data['items']:'';
        if (empty($items)) {
            throw new \Exception("invalid items parameter", 400);
        }
        
        $type = ($data['type']=='food' || $data['type']=='restaurant')?$data['type']:false;
        
        if(!$type){
            throw new \Exception("invalid type", 400);
        }         
        
        $result = array();
        $restId = explode(",", $items);//$restId contain restaurnt id or menu id
        foreach($restId as $key => $restaurantId){//$restaurantId contain restaurnt id or menu id
            $cache_key = 'bm_'.$restaurantId. '_' . $type;
            $cache_data = SearchFunctions::getCacheData($cache_key, $this->debug);
            if($cache_data){
                $result[] = $cache_data;
            }else{        
                $bm = SearchFunctions::getBookmarks($restaurantId, $type);
                if (!empty($bm)) {
                    SearchFunctions::setCacheData($cache_key, $bm, 86400);
                    $result[] = $bm;
                }
           }
        }
        return $result;
    }

}