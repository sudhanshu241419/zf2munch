<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Gallery;

class GalleryController extends AbstractRestfulController {
	 public function get($restaurant_id = 0) {
		$response = array ();
		$gallery = array ();
		$large = ( bool ) $this->getQueryParams ( 'large', false );
		$limit = ( int ) $this->getQueryParams ( 'limit', 0 );
        $restaurantModel = new \Restaurant\Model\Restaurant();
        $restaurantDetailOption = array('columns'=>array('rest_code'),'where'=>array('id'=>$restaurant_id));
        $restCode = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray();
        $limit = $this->getQueryParams('limit',SHOW_PER_PAGE);
        $page = $this->getQueryParams('page',1);
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
		/*
		 * Get gallery detail of restaurant
		 */
		$galleryModel = new Gallery ();
		$response = $galleryModel->getGallery ( $restaurant_id )->toArray ();
		foreach ( $response as $key => $val ) {
			if (! empty ( $val ['image'] ) && ! empty ( $val ['rest_code'] )) {
				$galleryArr = array ();
				if (! empty ( $val ['image'] )) {
					$imageName = substr ( $val ['image'], 0, - 4 );
					$imageName = ucwords ( str_replace ( '-', ' ', $imageName ) );
				} else {
					$imageName = '';
				}
				$galleryArr ['title'] = $imageName;
				
					$galleryArr ['image'] = $val ['image'];
					$galleryArr ['rest_code'] = strtolower ( $val ['rest_code'] );
					if ($large) {
						$galleryArr ['small_image'] = IMAGE_PATH . strtolower ( $val ['rest_code'] ) . DS . THUMB . DS . $val ['image'];
						$galleryArr ['large_image'] = IMAGE_PATH . strtolower ( $val ['rest_code'] ) . DS . $val ['image'];
					}
                $galleryArr ['type']='restaurant';
				$gallery [] = $galleryArr;
			}
		}
       
        ##########################User Images ###################
        
        $restaurantUserImagesModel = new \User\Model\UserRestaurantimage();
        $restaurantUserImagesModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'image',
                'title'=>'caption'
            ),
            'where' => array(
                'image_status' => 0,
                'status' => 1,
                'restaurant_id' => $restaurant_id,
                'image_type'=>'g'
            )
        );
        $userImages = $restaurantUserImagesModel->find($options)->toArray();
        $userUploadedImage = array();
        if (!empty($userImages)) {
            foreach ($userImages as $userImage) {
                $userUploadedImage [] = array(
                    'title' => '',
                    'image' => $userImage ['image'],
                    'rest_code'=>strtolower($restCode['rest_code']),
                    'type'=>'user'
                );
            }
        }
       
        #########################################################
        $final1 = array_merge($gallery, $userUploadedImage);
        $totalImage = count($final1);
        $final = array_slice($final1, $offset, $limit);
        $user_images = WEB_URL . USER_IMAGE_UPLOAD;
        $total = $totalImage;
        return array ('galleries' => $final,'user_images'=>$user_images,'total'=>$total);				
	}
}
