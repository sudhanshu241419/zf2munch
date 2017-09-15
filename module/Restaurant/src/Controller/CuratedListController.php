<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\CuratedList;

class CuratedListController extends AbstractRestfulController {
	public function getList() {
		$curatedList = new CuratedList();
        $limit = $this->getQueryParams('limit',10);
        $page = $this->getQueryParams('page',1);        
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
        $memCached = $this->getServiceLocator()->get('memcached');
        $config = $this->getServiceLocator()->get('Config');
        if ($config['constants']['memcache'] && $memCached->getItem('curatedlist')) {
            $cl =$memCached->getItem('curatedlist');   
            $totalList = count($cl);
            $response['curated_list'] = array_slice($cl,$offset,$limit);
            $response['total_records']=$totalList;
        } else {
        $options = array('columns'=>array('id','phrase','food_items','cuisine','type_of_place','curated_image','curated_video'),'where'=>array('status'=>1));
         $cl = $curatedList->getCuratedList($options);
         $totalList = count($cl);
         $response['curated_list'] = array_slice($cl,$offset,$limit);
         $response['total_records']=$totalList;
         $memCached->setItem('curatedlist', $cl, 0);
        }
        return $response;
	}
}