<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Home\Model\City;
use Restaurant\Model\Cuisine;
use Restaurant\Model\Calendar;
use MCommons\StaticOptions;
use Restaurant\Model\RestaurantBookmark;
use Restaurant\Model\Review;
use Restaurant\Model\Restaurant;
use Restaurant\RestaurantDetailsFunctions;

class DetailController extends AbstractRestfulController {
	public function get($restaurant_id = 0) {
		$response = array ();
		// Get Restaurant details and description
		$restaurantDetailModel = new Restaurant();
        $restaurantFunctions = new RestaurantDetailsFunctions ();
		$options = array('where'=>array('id'=>$restaurant_id));
		$resDetails = $restaurantDetailModel->findRestaurant ( $options );
		$resDetails = $resDetails ->toArray();
		if (! $resDetails) {
			return $this->sendError ( 'Restaurant details not found', 404 );
		}
		$city_id = $resDetails['city_id'];
					
		// Get Restaurant city
		$cityModel = new City ();
		$cityData = $cityModel->getCity ( array (
				'columns' => array (
						'city_name',
						'time_zone' 
				),
				'where' => array (
						'id' => $city_id 
				) 
		) )->current ();
		
		$cityName = '';
		if ($cityData) {
			$cityName = $cityData->city_name;
		}
		
        $response ['name'] = $resDetails ['restaurant_name'];
		$response ['price'] = $resDetails ['price'];
		$response ['is_chain'] = $resDetails ['is_chain'];
		$response ['accept_cc'] = $resDetails ['accept_cc'];
		$response ['delivery'] = $resDetails ['delivery'];
		$response ['takeout'] = $resDetails ['takeout'];
		$response ['dining'] = $resDetails ['dining'];
		$response ['phone_no'] = $resDetails ['phone_no'];
		$response ['minimum_delivery'] = $resDetails ['minimum_delivery'];
		$response ['delivery_area'] = $resDetails ['delivery_area'];
		$response ['reservations'] = $resDetails ['reservations'];
		$response ['restaurant_id'] = $resDetails ['id'];
		$response ['description'] = $resDetails ['description'];
		$response ['address'] = $resDetails ['address'];
		$response ['zipcode'] = $resDetails ['zipcode'];
		$response ['city_id'] = $resDetails ['city_id'];
		$response ['rest_code'] = $resDetails ['rest_code'];
        $response ['neighborhood'] = $resDetails ['neighborhood'];
			
		$response ['restaurant_address'] = $resDetails ['address'];
		$response ['restaurant_address'] .= $resDetails ['street'] ? ", " . $resDetails ['street'] : "";
		$response ['restaurant_address'] .= $cityName ? ", " . $cityName : '';
		$response ['restaurant_address'] .= $resDetails ['zipcode'] ? ", " . $resDetails ['zipcode'] : '';
		$response['polygon']=$restaurantFunctions->formatDeliveryGeo($resDetails['delivery_geo']);
		// Map Data
		$mapDetails = array ();
		$mapDetails ['latitude'] = $resDetails['latitude'];
		$mapDetails ['longitude'] = $resDetails['longitude'];
		
		$response ['map_data'] = $mapDetails;
		
		// Cuisine data
		$cuisineModel = new Cuisine ();
		$cuisineData = $cuisineModel->getRestaurantCuisine ( array (
				'columns' => array (
						'restaurant_id' => $restaurant_id 
				) 
		) )->toArray ();
		$cuisines = array ();
		$cuisineText = '';
		if (! empty ( $cuisineData )) {
			foreach ( $cuisineData as $cuisine ) {
				$cuisines [] = $cuisine ['cuisine'];
			}
			$cuisineText = implode ( ', ', $cuisines );
		}
		$response ['restaurant_cuisine'] = $cuisineText;
		
		// Check if restaurant open
		$calendarModel = new Calendar ();
		$currDateTime = StaticOptions::getRelativeCityDateTime ( array (
				'state_timezone' => $cityData->time_zone 
		) );
		$revMappedDay = array_flip ( StaticOptions::$dayMapping );
		$day = $revMappedDay [$currDateTime->format ( 'D' )];
		
		$isResOpen = $calendarModel->isRestaurantOpen ( $restaurant_id );
		
		$response ['is_restaurant_open'] = $isResOpen;
		
		// Get Restaurant bookmarks
		$restaurantBookmarkModel = new RestaurantBookmark ();
		$bookmarkTypes = $restaurantBookmarkModel->bookmark_types;
		
		$bookmarkData = $restaurantBookmarkModel->getRestaurantBookmarkCount ( $restaurant_id );
		$bmdata = array ();
		
		// Initializing Bookmark Types Count Values
		foreach ( $bookmarkTypes as $type ) {
			$bmdata [$type] = 0;
		}
		if (count ( $bookmarkData )) {
			foreach ( $bookmarkData as $bdata ) {
				$key = $bdata ['type'];
				$bmdata [$key] = $bdata ['total_count'];
			}
		}
		$response ['total_like_count'] = $bmdata ['li'];
		$response ['total_love_count'] = $bmdata ['lo'];
		$response ['total_been_count'] = $bmdata ['bt'];
		$response ['is_in_wishlist'] = $bmdata ['wl'];
		
		// Get restaurant review counts
		$restaurantReviewsModel = new Review ();
		$restaurantReviewsModel->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$restaurantReviewsData = $restaurantReviewsModel->find ( array (
				'columns' => array (
						'count' => $restaurantReviewsModel->getReviewCountExpression2 ( $restaurant_id ) 
				),
				'where' => array (
						'restaurant_id' => $restaurant_id 
				) 
		) );
		$restaurantReviewsData = $restaurantReviewsData->current ();
		$restaurantReviewsData = $restaurantReviewsData ? $restaurantReviewsData->offsetGet ( 'count' ) : 0;
		$response ['total_reviews_count'] = $restaurantReviewsData;
		// Get state current date and time
		$currDateObject = StaticOptions::getRelativeCityDateTime ( array (
				'restaurant_id' => $restaurant_id 
		) );
		
		$dateFormat = $this->getQueryParams ( 'datetime_format', 'Y-m-d' );
		$response ['restaurant_current_date'] = $currDateObject->format ( $dateFormat );
		$response ['date_format'] = $dateFormat;
		$response ['restaurant_current_time'] = $currDateObject->format ( 'H:i:s' );

        // below this dummy data
        $response['is_online_reservation'] = '1';
        $response['is_online_order'] = '1';
        $response['total_tried_count'] = '15';
				
		return $response;
	}
}
