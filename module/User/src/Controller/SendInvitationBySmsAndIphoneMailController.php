<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class CreditCardController extends AbstractRestfulController {
	public function getList() {
		$session = $this->getUserSession ();
		$isLoggedIn = $session->isLoggedIn ();
		if ($isLoggedIn) {
			$user_id = $session->getUserId ();
		} else {
			throw new \Exception ( "User unavailable", 400 );
		}
		$response = array("url"=>"http://munch-local.com/api/user/details?mob=true&refferedid=".md5($user_id));
		
		return $response;
	}	
}