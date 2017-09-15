<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;

class BannersController extends AbstractRestfulController {

    public function getList() {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();        
       
        $banners = array();
        $userId = $session->getUserId();
        $restaurantServer = new \User\Model\RestaurantServer();
        $userDineAndMoreRestaurant = $restaurantServer->userDineAndMoreRestaurant($userId);
        $location = \MCommons\StaticOptions::getUserSession()->getUserDetail('selected_location');
       //var_dump($location['city_id']);
        if($location['city_id']==18848){
        if ($isLoggedIn && !empty($userDineAndMoreRestaurant) && $userId > 0) {
           
            foreach($userDineAndMoreRestaurant as $key => $val){
                $banners[$key]['restaurantId'] = $val['restaurant_id'];
                $banners[$key]['rest_name'] = $val['restaurant_name'];
                $banners[$key]['rest_tagline'] = $val['restaurant_name'];
                $banners[$key]['banner_for'] = "";
                $banners[$key]['banner_img'] = WEB_IMG_URL."munch_images/".strtolower($val['rest_code'])."/".$val['restaurant_image_name'];
                $banners[$key]['banner_img_6p'] = WEB_IMG_URL."munch_images/".strtolower($val['rest_code'])."/".$val['restaurant_image_name'];
                $banners[$key]['type'] = 1;
            }
            
        }else{
           
                $banners = array(
                    array('restaurantId' => '61086',
                        'rest_name' => 'Aria',
                        'rest_tagline' => 'Place an Order',
                        'banner_for' => 'order',
                        'banner_img' => WEB_URL . 'img/banner2@2x.png',
                        'banner_img_6p' => WEB_URL . 'img/banner2@3x.png',
                        'type' => 0
                    ),
                    array('restaurantId' => '57914',
                        'rest_name' => 'Bareburger',
                        'rest_tagline' => 'Taste the Difference',
                        'banner_for' => 'order',
                        'banner_img' => WEB_URL . 'img/banner@2x.png',
                        'banner_img_6p' => WEB_URL . 'img/banner@3x.png',
                        'type' => 0
                    ),
                    array('restaurantId' => '58252',
                        'rest_name' => 'IL Melograno',
                        'rest_tagline' => 'Place an Order',
                        'banner_for' => 'order',
                        'banner_img' => WEB_URL . 'img/banner3@2x.png',
                        'banner_img_6p' => WEB_URL . 'img/banne3@3x.png',
                        'type' => 0
                    ),
                );
                
            }
        }
            return $banners;
      
    }

}
