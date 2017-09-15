<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class RestaurantLocation extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $lattitude;
	public $longitude;
	public $city_id;
	public $max_delivery_distance;
	public $neighborhood_latitude;
	public $neighborhood_longitude;
	protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantLocationTable';
	
	/**
	 * Get details of restaurant based on the restaurant ID
	 *
	 * @param number $restaurant_id        	
	 * @return Ambigous <\ArrayObject,false>
	 */
	public function getrestaurantbyDistance($restaurant_id = 0, $address = NULL) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'lattitude',
				'longitude' 
		) );
		
		$select->join ( array (
				'rd' => 'restaurants_details' 
		), 'rd.restaurant_id = restaurants_location.restaurant_id', array (
				'delivery_area' 
		), $select::JOIN_INNER );
		$select->where ( array (
				'rd.restaurant_id' => $restaurant_id 
		) );
		
		$reslocation = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current ();
		
		// print_r($restaurantLatLon); exit;
		if (! empty ( $reslocation )) {
			$restaurantLat = $reslocation ['lattitude'];
			$restaurantLong = $reslocation ['longitude'];
			$max_del_distance = $reslocation ['delivery_area'];
		} else {
			$restaurantLat = 0;
			$restaurantLong = 0;
			$max_del_distance = 0;
		}
		
		if ($address) {
			$address = str_replace ( ' ', '+', $address );
			$latLongValue = $this->latLonAPI ( $address );
		} else {
			$latLongValue = '';
		}
		
		if (is_array ( $latLongValue )) {
			$lattitude = ! empty ( $latLongValue ['lat'] ) ? $latLongValue ['lat'] : '';
			$longitude = ! empty ( $latLongValue ['lon'] ) ? $latLongValue ['lon'] : '';
			$city = ! empty ( $latLongValue ['city'] ) ? $latLongValue ['city'] : '';
			$state = ! empty ( $latLongValue ['state'] ) ? $latLongValue ['state'] : '';
			$route = ! empty ( $latLongValue ['route'] ) ? $latLongValue ['route'] : '';
			$country = ! empty ( $latLongValue ['country'] ) ? $latLongValue ['country'] : '';
			if (! empty ( $latLongValue ['zipcode'] )) {
				$zipcode = $latLongValue ['zipcode'];
			} else {
				$zipcode = '';
			}
			
			$distanceinMiles = self::calculatedistance ( $restaurantLat, $restaurantLong, $lattitude, $longitude, '' );
			if ($max_del_distance >= $distanceinMiles) {
				$delivery_data ['res_distance'] = number_format ( $distanceinMiles, 2 );
				$delivery_data ['delivery_confirm_msg'] = 'This Restaurant Delivers to You!';
				$delivery_data ['delivery_confirm_status'] = true;
				$delivery_data ['route'] = $route;
				$delivery_data ['city'] = $city;
				$delivery_data ['state'] = $state;
				$delivery_data ['country'] = $country;
				$delivery_data ['zipcode'] = $zipcode;
				if (strpos ( $address, $_COOKIE ['city_name'] ) === 0 || strpos ( $address, urlencode ( $_COOKIE ['city_name'] ) ) === 0) {
					$delivery_data ['delivery_confirm_msg'] = 'Sorry We are unable to deliver in this area!';
					$delivery_data ['delivery_confirm_status'] = false;
				}
				return $delivery_data;
			} else {
				$delivery_data ['res_distance'] = number_format ( $distanceinMiles, 2 );
				$delivery_data ['delivery_confirm_msg'] = 'Sorry We are unable to deliver in this area!';
				$delivery_data ['delivery_confirm_status'] = false;
				$delivery_data ['route'] = $route;
				$delivery_data ['city'] = $city;
				;
				$delivery_data ['state'] = $state;
				$delivery_data ['country'] = $country;
				$delivery_data ['zipcode'] = '';
				return $delivery_data;
			}
		} else {
			$delivery_data ['res_distance'] = '';
			$delivery_data ['delivery_confirm_msg'] = 'Sorry We are unable to deliver in this area!';
			$delivery_data ['delivery_confirm_status'] = false;
			$delivery_data ['route'] = '';
			$delivery_data ['city'] = '';
			$delivery_data ['state'] = '';
			$delivery_data ['country'] = '';
			$delivery_data ['zipcode'] = '';
			
			return $delivery_data;
		}
	}
	public function latLonAPI($addressURL) {
		$response_a = $this->googlelatLonAPI ( $addressURL );
		if ($response_a->status == 'OK') {
			$latlon = $this->parse_google_response ( $response_a );
			return $latlon;
		} else {
			$bing_response = $this->binglatLonAPI ( $addressURL );
			if (empty ( $bing_response ))
				return null;
			if ($bing_response->statusCode == 200) {
				$latlon = $this->parse_bing_response ( $bing_response );
				
				return $latlon;
			} else {
				$bing_response = self::binglatLonAPI ( $addressURL );
				if ($bing_response->statusCode == 200) {
					$latlon = $this->parse_bing_response ( $bing_response );
					return $latlon;
				} else {
					return null;
				}
			}
		}
	}
	public function googlelatLonAPI($addressURL) {
		// $addressURL='Geary street, San Francisco, CA, United States';
		$url = "http://maps.google.com/maps/api/geocode/json?address=" . urlencode ( $addressURL ) . "&sensor=false";
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_PROXYPORT, 3128 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		$response = curl_exec ( $ch );
		curl_close ( $ch );
		$response_a = json_decode ( $response );
		return $response_a;
	}
	public function parse_google_response($response_a) {
		$latlon = array ();
		$latlon ['route'] = '';
		$latlon ['city'] = '';
		$latlon ['state'] = '';
		$latlon ['country'] = '';
		$latlon ['zipcode'] = '';
		$latlon ['lat'] = '';
		$latlon ['lon'] = '';
		if (! empty ( $response_a->results [0]->geometry->location->lat )) {
			$latlon ['lat'] = $response_a->results [0]->geometry->location->lat;
		}
		if (! empty ( $response_a->results [0]->geometry->location->lng )) {
			$latlon ['lon'] = $response_a->results [0]->geometry->location->lng;
		}
		$addressData = $response_a->results [0]->address_components;
		if (count ( $addressData ) > 0) {
			foreach ( $addressData as $key => $data ) {
				if ($data->long_name) {
					if ($data->types [0] == 'route') {
						$latlon ['route'] = $data->long_name;
					}
					if ($data->types [0] == 'locality' && $data->types [1] == 'political') {
						$latlon ['city'] = $data->long_name;
					}
					if ($data->types [0] == 'administrative_area_level_1' && $data->types [1] == 'political') {
						$latlon ['state'] = $data->short_name;
					}
					if ($data->types [0] == 'country' && $data->types [1] == 'political') {
						$latlon ['country'] = $data->long_name;
					}
					if ($data->types [0] == 'postal_code') {
						$latlon ['zipcode'] = $data->long_name;
					}
				}
			}
		}
		
		return $latlon;
	}
	public function binglatLonAPI($addressURL) {
		$latlon = array ();
		$url = 'http://dev.virtualearth.net/REST/v1/Locations?q=' . $addressURL . '&o=json&key=AnRJnO9zJTi_rIWS2SOXg_TWohbRqKVsLZmIDuao3Wx9zP2j-cj_sBCoyRRg6UoI';
		$geo_response = @file_get_contents ( $url );
		$response_a = json_decode ( $geo_response );
		return $response_a;
	}
	public function parse_bing_response($bing_response) {
		$latlon = array ();
		$latlon ['route'] = '';
		$latlon ['city'] = '';
		$latlon ['state'] = '';
		$latlon ['country'] = '';
		$latlon ['zipcode'] = '';
		$latlon ['lat'] = '';
		$latlon ['lon'] = '';
		// print_r($bing_response);
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->point->coordinates [0] )) {
			$latlon ['lat'] = $bing_response->resourceSets [0]->resources [0]->point->coordinates [0];
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->point->coordinates [1] )) {
			$latlon ['lon'] = $bing_response->resourceSets [0]->resources [0]->point->coordinates [1];
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->address->adminDistrict )) {
			$latlon ['state'] = $bing_response->resourceSets [0]->resources [0]->address->adminDistrict;
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->address->locality )) {
			$latlon ['city'] = $bing_response->resourceSets [0]->resources [0]->address->locality;
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->address->countryRegion )) {
			$latlon ['country'] = $bing_response->resourceSets [0]->resources [0]->address->countryRegion;
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->address->addressLine )) {
			$latlon ['route'] = $bing_response->resourceSets [0]->resources [0]->address->addressLine;
		}
		if (! empty ( $bing_response->resourceSets [0]->resources [0]->address->postalCode )) {
			$latlon ['zipcode'] = $bing_response->resourceSets [0]->resources [0]->address->postalCode;
		}
		
		return $latlon;
	}
	public function calculatedistance($lat1, $lon1, $lat2, $lon2, $unit) {
		if (! empty ( $lat2 ) && ! empty ( $lon2 ) && ! empty ( $lat1 ) && ! empty ( $lon1 )) {
			$theta = $lon1 - $lon2;
			$dist = sin ( deg2rad ( $lat1 ) ) * sin ( deg2rad ( $lat2 ) ) + cos ( deg2rad ( $lat1 ) ) * cos ( deg2rad ( $lat2 ) ) * cos ( deg2rad ( $theta ) );
			$dist = acos ( $dist );
			$dist = rad2deg ( $dist );
			$miles = $dist * 60 * 1.1515;
			$unit = strtoupper ( $unit );
			
			if ($unit == "K") {
				return ($miles * 1.609344);
			} else if ($unit == "N") {
				return ($miles * 0.8684);
			} else {
				return $miles;
			}
		} else {
			return 5000;
		}
	}
}
