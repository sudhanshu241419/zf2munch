<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Http\Client;

class Location extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $lattitude;
	public $longitude;
	public $city_id;
	public $max_delivery_distance;
	public $neighborhood_latitude;
	public $neighborhood_longitude;
	protected $_db_table_name = 'Restaurant\Model\DbTable\LocationTable';
	protected $_primary_key = 'id';
	public function getLatLong(array $options = array()) {
		return $this->find ( $options );
	}
	public function googleLatLon($addressURL) {
		if (! $addressURL) {
			throw new \Exception ( 'Address not found', 404 );
		}
		$addressURL = urlencode ( $addressURL );
		$googleAPIURL = "http://maps.google.com/maps/api/geocode/json";
		$httpClient = new Client ( $googleAPIURL );
		$httpClient->setParameterGet ( array (
				'address' => $addressURL,
				'sensor' => 'true' 
		) );
		$response = $httpClient->dispatch ( $httpClient->getRequest () );
		if ($response->isSuccess ()) {
			return json_decode ( $response->getContent () );
		} else {
			throw new \Exception ( 'Location data not found', 404 );
		}
	}
	public function bingLatLonAPI($addressURL) {
		if (! $addressURL) {
			throw new \Exception ( 'Address not found', 404 );
		}
		// $addressURL=str_replace(' ','+',$addressURL);
		// $addressURL = urlencode ( $addressURL );
		$bingAPIURL = "http://dev.virtualearth.net/REST/v1/Locations";
		$httpClient = new Client ( $bingAPIURL );
		
		$httpClient->setParameterGet ( array (
				'q' => $addressURL,
				'key' => 'AnRJnO9zJTi_rIWS2SOXg_TWohbRqKVsLZmIDuao3Wx9zP2j-cj_sBCoyRRg6UoI' 
		) );
		$response = $httpClient->dispatch ( $httpClient->getRequest () );
		
		$result = $response->getBody ();
		if ($response->isSuccess ()) {
			return json_decode ( $result );
		} else {
			throw new \Exception ( 'Location data not found', 404 );
		}
	}
	public function parseGoogleResponse($gResponse) {
		$latLon = array ();
		$latLon ['city'] = '';
		$latLon ['state'] = '';
		$latLon ['country'] = '';
		$latLon ['zipcode'] = '';
		$latLon ['lat'] = '';
		$latLon ['lon'] = '';
		// Get Latitude
		if (! empty ( $gResponse->results [0]->geometry->location->lat )) {
			$latLon ['lat'] = $gResponse->results [0]->geometry->location->lat;
		}
		// Get Longitude
		if (! empty ( $gResponse->results [0]->geometry->location->lng )) {
			$latLon ['lon'] = $gResponse->results [0]->geometry->location->lng;
		}
		$addressData = $gResponse->results [0]->address_components;
		if (count ( $addressData ) > 0) {
			foreach ( $addressData as $key => $data ) {
				if ($data->long_name) {
					if ($data->types [0] == 'locality' && $data->types [1] == 'political') {
						$latLon ['city'] = $data->long_name;
					}
					if ($data->types [0] == 'administrative_area_level_1' && $data->types [1] == 'political') {
						$latLon ['state'] = $data->long_name;
					}
					if ($data->types [0] == 'country' && $data->types [1] == 'political') {
						$latLon ['country'] = $data->long_name;
					}
					if ($data->types [0] == 'postal_code') {
						$latLon ['zipcode'] = $data->long_name;
					}
				}
			}
		}
		
		return $latLon;
	}
	public function parseBingResponse($bing_response) {
		$latLon = array ();
		$latLon ['city'] = '';
		$latLon ['state'] = '';
		$latLon ['country'] = '';
		$latLon ['zipcode'] = '';
		$latLon ['lat'] = '';
		$latLon ['lon'] = '';
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->point->coordinates [0] )) {
			$latLon ['lat'] = $bing_response->resourceSets [0]->resources [0]->point->coordinates [0];
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->point->coordinates [1] )) {
			$latLon ['lon'] = $bing_response->resourceSets [0]->resources [0]->point->coordinates [1];
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->address->adminDistrict )) {
			$latLon ['state'] = $bing_response->resourceSets [0]->resources [0]->address->adminDistrict;
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->address->locality )) {
			$latLon ['city'] = $bing_response->resourceSets [0]->resources [0]->address->locality;
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->address->countryRegion )) {
			$latLon ['country'] = $bing_response->resourceSets [0]->resources [0]->address->countryRegion;
		}
		
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->address->postalCode )) {
			$latLon ['zipcode'] = $bing_response->resourceSets [0]->resources [0]->address->postalCode;
		}
		return $latLon;
	}
	public function executeLatLonAPI($addressURL) {
		$gResponse = $this->googleLatLon ( $addressURL );
		if ($gResponse->status == 'OK') {
			$latlon = $this->parseGoogleResponse ( $gResponse );
			return $latlon;
		} else {
			$bResponse = $this->bingLatLonAPI ( $addressURL );
			if ($bResponse->statusCode == 200) {
				$latlon = $this->parseBingResponse ( $bResponse );
				return $latlon;
			} else {
				return array (
						'lat' => 0,
						'lon' => 0,
						'state' => '',
						'city' => '',
						'country' => '',
						'zipcode' => '' 
				);
			}
		}
	}
	/**
	 *
	 * @param decimal $lat1        	
	 * @param decimal $lon1        	
	 * @param decimal $lat2        	
	 * @param decimal $lon2        	
	 * @param string $unit        	
	 * @return distance in decimal
	 */
	public function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit) {
		if (! empty ( $lat2 ) && ! empty ( $lon2 ) && ! empty ( $lat1 ) && ! empty ( $lon1 )) {
			$theta = $lon1 - $lon2;
			$dist = sin ( deg2rad ( $lat1 ) ) * sin ( deg2rad ( $lat2 ) ) + cos ( deg2rad ( $lat1 ) ) * cos ( deg2rad ( $lat2 ) ) * cos ( deg2rad ( $theta ) );
			$dist = acos ( $dist );
			$dist = rad2deg ( $dist );
			$miles = $dist * 60 * 1.1515;
			$unit = strtoupper ( $unit );
			
			if ($unit == "K") {
				return number_format ( ($miles * 1.609344), 2, '.', '' );
			} else if ($unit == "N") {
				return number_format ( ($miles * 0.8684), 2, '.', '' );
			} else {
				return number_format ( $miles, 2, '.', '' );
			}
		} else {
			return false;
		}
	}
	public function checkRestaurantDistance($restaurant_id, $address) {
		$deliveryData = array ();
		$restaurantLatLon = $this->getLatLong ( array (
				'columns' => array (
						'lattitude',
						'longitude',
						'max_delivery_distance' 
				),
				'where' => array (
						'restaurant_id' => $restaurant_id 
				) 
		) )->current ();
		// Get restaurant lat lon from location table
		$restaurantLat = 0;
		$restaurantLong = 0;
		$maxDelDistance = 0;
		print_r($restaurantLatLon);die;
		if (! empty ( $restaurantLatLon )) {
			$restaurantLat = $restaurantLatLon->lattitude;
			$restaurantLong = $restaurantLatLon->longitude;
			$maxDelDistance = $restaurantLatLon->max_delivery_distance;
		}
		// Get lat lon from input user delivery address
		$latLongValue = $this->executeLatLonAPI ( $address );
		$addLatitude = ! empty ( $latLongValue ['lat'] ) ? $latLongValue ['lat'] : '';
		$addLongitude = ! empty ( $latLongValue ['lon'] ) ? $latLongValue ['lon'] : '';
		$addCity = ! empty ( $latLongValue ['city'] ) ? $latLongValue ['city'] : '';
		$addState = ! empty ( $latLongValue ['state'] ) ? $latLongValue ['state'] : '';
		$addZipcode = ! empty ( $latLongValue ['zipcode'] ) ? $latLongValue ['zipcode'] : '';
		// Calculate distance between two lat lon
		$distanceinMiles = $this->calculateDistance ( $restaurantLat, $restaurantLong, $addLatitude, $addLongitude, '' );
		if ($maxDelDistance >= $distanceinMiles) {
			$deliveryData ['res_distance'] = $distanceinMiles;
			$deliveryData ['delivery_status'] = true;
			$deliveryData ['city'] = $addCity;
			$deliveryData ['state'] = $addState;
			$deliveryData ['zipcode'] = $addZipcode;
			return $deliveryData;
		} else {
			$deliveryData ['res_distance'] = $distanceinMiles;
			$deliveryData ['delivery_status'] = false;
			$deliveryData ['city'] = $addCity;
			;
			$deliveryData ['state'] = $addState;
			$deliveryData ['zipcode'] = $addZipcode;
			return $deliveryData;
		}
	}
}
