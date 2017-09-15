<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\MenuBookmark;
use MCommons\StaticOptions;

class MenuBookmarkController extends AbstractRestfulController {
	public function getList() {
		$bookmarkModel = new MenuBookmark ();
		$session = $this->getUserSession ();
		$isLoggedIn = $session->isLoggedIn ();
		
		if ($isLoggedIn) {
			$bookmarkModel->user_id = $session->getUserId ();
		}
		
		// $bookmarkModel->user_id = $this->getQueryParams('user_id');
		$bookmarkModel->menu_id = $this->getQueryParams ( 'menu_id' );
		$bookmarkModel->type = $this->getQueryParams ( 'type' );
		
		if (! $isLoggedIn) {
			return StaticOptions::getResponse ( $this->getServiceLocator (), array (
					'error' => "Unauthorized user." 
			), 404 );
		}
		if (! $bookmarkModel->menu_id) {
			return StaticOptions::getResponse ( $this->getServiceLocator (), array (
					'error' => "Invalid menu id." 
			), 404 );
		}
		
		if (! $bookmarkModel->type) {
			return StaticOptions::getResponse ( $this->getServiceLocator (), array (
					'error' => "Invalid bookmark type." 
			), 404 );
		}
		
		$bookmarkModel->created_on = StaticOptions::getDateTime ()->format ( StaticOptions::MYSQL_DATE_FORMAT );
		
		$response = $bookmarkModel->addMenuBookMark ();
		
		if (! $response) {
			return $this->sendError ( array (
					'error' => 'Unable to save menu bookmark' 
			), 500 );
		}
		return $response;
	}
}
