<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Story;
use MCommons\StaticOptions;

class StoryController extends AbstractRestfulController {
    public $config;
    public function get($restaurant_id) {
		$response = array ();
		$storyModel = new Story ();
        $cssPath=$this->getQueryParams('csspath',false);
        $this->config = $this->getServiceLocator()->get('Config');
        $currentTime = StaticOptions::getRelativeCityDateTime(array(
                                'restaurant_id' => $restaurant_id
                            ))->format(StaticOptions::COOKIE);
        $storyDetails = $storyModel->findDetailedStory ( $restaurant_id, $this->isMobile (),$this->config,$cssPath,$currentTime);
		return $response = array (
					'story' => $storyDetails 
			);	
	}
}
