<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Zend\Db\Sql\Predicate\Expression;
use Home\Model\City;
use User\Model\User;

class WebRestaurantSearchController extends AbstractRestfulController {
	const FORCE_LOGIN = true;
	public function get($id) {
		
		$city_model = new City ();
		$user_model = new User ();		
		$session = $this->getUserSession ();
		$getCityId = $session->getUserDetail('selected_location');
		$city_id= $getCityId['city_id'];
		
		$q = $this->getQueryParams ( 'q' );
		if (strlen ( $q ) < 3) {
			throw new \Exception ( 'You need to enter atleast 3 characters' );
		}
		$restaurantModel = new Restaurant ();
		$restaurantModel->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$options = array (
				'columns' => array (
						'restaurant_id' => 'id',
						'restaurant_name',
						'address',
						'delivery',
						'takeout',
						'reservations'
				),
				'like' => array (
						'field' => 'restaurant_name',
						'like' => '%' . $q . '%' 
				),
				'where' => new Expression ('(delivery = 1 OR takeout = 1 OR reservations = 1 OR dining = 1) AND (inactive = 0 AND closed =0) AND city_id='.$city_id),
				'limit' => 5
		);
		$response = $restaurantModel->find ( $options )->toArray ();
		foreach ($response as $key=>$value){
			if($response[$key]['delivery']){
				$response[$key]['type'] = 1;
				continue;
			}
			$response[$key]['type'] = (isset($response[$key]['takeout']) && $response[$key]['takeout'] == 1)?2:3;
		}
		return $response;
	}
}