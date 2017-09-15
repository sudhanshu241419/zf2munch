<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class Image extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $image;
	public $image_url;
	public $image_type;
	public $created_on;
	public $updated_at;
	public $status;
	public $image_dimension;
	protected $_db_table_name = 'Restaurant\Model\DbTable\ImageTable';
	protected $_primary_key = 'id';
	public function getRestaurantGallery($rest_code = 0, array $options = array(), $isMobile = false) {
		$imageResult = $this->find ( $options );
		$images = array ();
		$imageArr = array ();
		foreach ( $imageResult as $image ) {
			$image = ! empty ( $image->image ) ? $image->image : '';
			if ($isMobile) {
				$imageArr ['image'] = $image;
                $imageArr['type']='restaurant';
			} else {
				$imageArr ['large_image'] = IMAGE_PATH . strtolower ( $rest_code ) . DS . $image;
				$imageArr ['small_image'] = IMAGE_PATH . strtolower ( $rest_code ) . DS . THUMB . DS . $image;
			}
			
			$imageName = substr ( $image, 0, - 4 );
			$imageName = ucwords ( str_replace ( '-', ' ', $imageName ) );
			$imageArr ['title'] = $imageName;
			
			$images [] = $imageArr;
		}
		return $images;
	}
	protected function getMappedDay($abbr) {
		return isset ( $this->dayMapping [$abbr] ) ? $this->dayMapping [$abbr] : '';
	}
	
	/**
	 * Generate the desired output string
	 *
	 * @param string $time        	
	 * @param string $format        	
	 * @param string $outputFormat        	
	 * @return string
	 */
	protected function getFormattedDateTime($time, $format, $outputFormat) {
		try {
			$dateTime = \DateTime::createFromFormat ( $format, $time );
			return $dateTime->format ( $outputFormat );
		} catch ( \Exception $ex ) {
			return '';
		}
	}
}