<?php

namespace Home\Model;

use MCommons\Model\AbstractModel;

class Location extends AbstractModel {
	private static $cityTableFields = array (
			'cty_name' => 'city_name',
			'cty_latitude' => 'latitude',
			'cty_longitude' => 'longitude',
			'cty_id' => 'city_id',
                        'cty_status'=>'status',
                        'cty_browseonly'=>'is_browse_only'
	);
	private static $cityPrefix = 'cty_';
	private static $temporaryPlaceholder = array ();
	public function refineLocationData($states = array()) {
		$locationData = array ();
		$index = 0;
		foreach ( $states as $state ) {
			foreach ( $state as $key => $val ) {
				if (strpos ( $key, 'cty_' ) !== false) {
					$locationData [$index] ['cities'] [self::$cityTableFields [$key]] = $val;
				} else {
					$locationData [$index] [$key] = $val;
				}
			}
			$index ++;
		}
		return $this->reArrangeStateCity ( $locationData );
	}
	private function reArrangeStateCity($locationData = array()) {
		$output = array ();
		foreach ( $locationData as $data ) {
			
			if (! empty ( $output [$data ['id']] ) && count ( $output [$data ['id']] ) != 0) {
				$output [$data ['id']] ['cities'] [] = $data ['cities'];
			} else {
				$index = $data ['id'];
				$output [$index] ['state'] = $data ['state'];
				$output [$index] ['state_code'] = $data ['state_code'];
				$output [$index] ['cities'] [] = $data ['cities'];
			}
		}
		$output = array_values ( $output );
		return $output;
	}
}