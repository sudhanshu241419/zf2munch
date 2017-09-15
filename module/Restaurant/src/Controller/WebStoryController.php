<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Story;
use MCommons\Caching;

class WebStoryController extends AbstractRestfulController {
	public function get($restaurant_id) {
		//$memCached = new Caching();
        $memCached = $this->getServiceLocator()->get('memcached');
		$config = $this->getServiceLocator()->get ( 'Config' );
		if($config['constants']['memcache'] && $memCached->getItem('story_'.$restaurant_id)){
			return $memCached->getItem('story_'.$restaurant_id);
		}else{
			$response = array ();
			$storyModel = new Story ();
			$storyDetails = $storyModel->findDetailedStoryForWeb ( $restaurant_id, $this->isMobile () );
			$memCached->setItem('story_'.$restaurant_id,$storyDetails,0);
			return $storyDetails;
        }
	}

}
