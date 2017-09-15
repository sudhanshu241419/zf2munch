<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;

class WebOwnersResponseLoginController extends AbstractRestfulController {
	public function create($data) {
		/*if($data['restaurant_id'] == 3171){
			throw new \Exception('Invalid credentials provided');
		}*/
		return array (
			'success' => true 
		);
	}
}