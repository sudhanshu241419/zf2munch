<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\RestaurantBookmark;
use MCommons\StaticOptions;

class RestaurantBookmarkController extends AbstractRestfulController {
	public function getList() {
		$bookmarkModel = new RestaurantBookmark ();
		$session = $this->getUserSession ();
		$isLoggedIn = $session->isLoggedIn ();
		
		if ($isLoggedIn) {
			$bookmarkModel->user_id = $session->getUserId ();
		}
		
		// $bookmarkModel->user_id = $this->getQueryParams('user_id');
		$bookmarkModel->restaurant_id = $this->getQueryParams ( 'restaurant_id' );
		
		$bookmarkModel->restaurant_name = $this->getQueryParams ( 'restaurant_name' );
		$bookmarkModel->type = $this->getQueryParams ( 'type' );
		
		if (! $isLoggedIn) {
			return StaticOptions::getResponse ( $this->getServiceLocator (), array (
					'error' => "Unauthorized user." 
			), 404 );
		}
		if (! $bookmarkModel->restaurant_id || ! $bookmarkModel->restaurant_name || ! $bookmarkModel->type) {
			return StaticOptions::getResponse ( $this->getServiceLocator (), array (
					'error' => "Invalid restaurant detail." 
			), 404 );
		}
		
		if (! $bookmarkModel->type) {
			return StaticOptions::getResponse ( $this->getServiceLocator (), array (
					'error' => "Invalid bookmark type." 
			), 404 );
		}
		
		$bookmarkModel->created_on = StaticOptions::getDateTime ()->format ( StaticOptions::MYSQL_DATE_FORMAT );
		
		$data ['created_on'] = StaticOptions::getDateTime ()->format ( StaticOptions::MYSQL_DATE_FORMAT );
		
		$response = $bookmarkModel->addRestaurantBookMark ();
		if ($response) {
			$bookmarkCount = $bookmarkModel->getRestaurantBookmarkCount ( $bookmarkModel->restaurant_id );
			foreach ( $bookmarkCount as $key => $val ) {
				if ($val ['type'] == 'li' && $bookmarkModel->type == 'li')
					$response ['total_love_it'] = isset ( $val ['total_count'] ) ? $val ['total_count'] : 0;
				if ($val ['type'] == 'bt' && $bookmarkModel->type == 'bt')
					$response ['total_been_there'] = isset ( $val ['total_count'] ) ? $val ['total_count'] : 0;
			}
		}
		
		if (! $response) {
			return $this->sendError ( array (
					'error' => 'Unable to save restaurant bookmark' 
			), 500 );
		}
		return $response;
	}
}
