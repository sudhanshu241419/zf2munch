<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Menu;

class TopMenuController extends AbstractRestfulController {
	private $bookmark_types;
	/*
	 * this function will get menu details of restaurant
	 */
	public function get($restaurant_id = 0) {
		
		// Get restaurant menu
		$menuModel = new Menu ();
		$limit = ( int ) $this->getQueryParams ( 'limit', 20 );
		$item = array('menu_id' => 'id','item_name','item_desc' );
		$response = $menuModel->getTopTwentyMenues ( array (
				'columns' => array (
						'restaurant_id' => $restaurant_id,
						'limit' => $limit 
				) 
		),$item )->toArray ();
		
		if (! $this->isMobile ()) {
			$response = array (
					'menus' => $response 
			);
		}
		
		return $response;
	}
}//End of class
