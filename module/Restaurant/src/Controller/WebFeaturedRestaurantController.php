<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Restaurant\Model\Cuisine;
use MCommons\StaticOptions;
use Zend\Db\Sql\Predicate\Expression;

class WebFeaturedRestaurantController extends AbstractRestfulController {

    private $mc_ts_key = 'feat_res_time';
    private $countUserRestaurantServer = 0;

    public function getList() {
        $udetailResponse = array();
        $restaurant = new Restaurant();
        $limit = 3;
        
        $config = $this->getServiceLocator()->get('Config');
        if (!$this->getUserSession()->isLoggedIn()) {
            $memCached = $this->getServiceLocator()->get('memcached');
            if ($config['constants']['memcache'] && $memCached->getItem('featured')) {
                if ($memCached->hasItem($this->mc_ts_key)) {
                    if ((time() - $memCached->getItem($this->mc_ts_key)) < 86400) {                     
                        return $memCached->getItem('featured');
                    }
                }
            }           
            
            $udetailResponse = $restaurant->getFeaturedRestaurant($limit);
        }else{
            $udetailResponse = $this->featuredUserDineAndMoreRestaurant();      
        
            if ($this->countUserRestaurantServer < 3) {  
                $restaurantIds = array_map(function ($ar) {return $ar['id'];}, $udetailResponse);
                if($this->countUserRestaurantServer==0){
                    $udetailResponse = $restaurant->getFeaturedRestaurant($limit);
                }else{
                    $requiredFRest = 3 - $this->countUserRestaurantServer;
                    $fdetailResponse = $restaurant->getFeaturedRestaurant($requiredFRest);
                    $udetailResponse = array_merge($udetailResponse, $fdetailResponse);
                }
            }
        }
        
        #Get cuisines of Restaurant#
        $this->cuisines($udetailResponse);
        
        if (!$this->getUserSession()->isLoggedIn()) {
            $memCached->setItem('featured', $udetailResponse);
            $memCached->setItem($this->mc_ts_key, time());
        }
        
        return $udetailResponse;
    }

    public function featuredUserDineAndMoreRestaurant() {
        $userId = $this->getUserSession()->getUserId();
        $restaurantServer = new \User\Model\RestaurantServer();
        $userRestaurantServer = $restaurantServer->featuresUserDineAndMoreRestaurant($userId, 3, 1);
        $this->countUserRestaurantServer = count($userRestaurantServer);
        return $userRestaurantServer;
    }

    public function featuredDineAndMoreRestaurant($limit,$restaurantIds=array()) {
        $restaurants = new Restaurant();
        return $restaurants->dineAndMoreRestaurant($limit, 1, $restaurantIds);
    }

    public function cuisines(&$detailResponse) {
        $restaurantCuisineModel = new Cuisine ();
        $config = $this->getServiceLocator()->get('Config');
        if ($detailResponse) {
            foreach ($detailResponse as $key => $details) {
                $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus($details['id']);
                $detailResponse[$key]['has_delivery'] = intval($details['has_delivery']);
                if (isset($details['code'])) {
                    $firstLetterRestaurantName = substr($details ['restaurant_name'], 0, 1);
                    $detailResponse[$key]['code'] = $firstLetterRestaurantName . $details ['id'] . "00";
                }else{
                    $detailResponse[$key]['code'] = "";
                }
                if ($detailResponse[$key]['has_delivery'] == 1) {
                    $detailResponse[$key]['has_delivery'] = ($currentDayDelivery) ? intval(1) : intval(0);
                }
                $accept_cc_phone = (int) ($details ['accept_cc_phone']);
                $menu_without_price = (int) ($details ['menu_without_price']);
                if ($menu_without_price || !$accept_cc_phone) {
                    $detailResponse[$key] ['has_delivery'] = intval(0);
                    $detailResponse[$key] ['has_takeout'] = intval(0);
                }
                $minDelivery = explode(".", $details['minimum_delivery']);
                if ($minDelivery[1] > 0) {
                    $detailResponse[$key]['minimum_delivery'] = $details['minimum_delivery'];
                } else {
                    $detailResponse[$key]['minimum_delivery'] = $minDelivery[0];
                }
                $restCode = strtolower($details['res_code']);
                $detailResponse[$key]['restaurant_image_name'] = $config['constants']['protocol'] . "://" . $config['constants']['imagehost'] . 'munch_images/' . $restCode . "/" . $details['restaurant_image_name'];
                $detailResponse[$key]['link'] = PROTOCOL . $config['constants']['web_url'] . "/restaurants/" . $details['restaurant_name'] . "/" . $details['id'] . "/menu";
                $joins = array();
                $joins [] = array(
                    'name' => array(
                        'c' => 'cuisines'
                    ),
                    'on' => 'c.id = restaurant_cuisines.cuisine_id',
                    'columns' => array(
                        'name' => 'cuisine'
                    ),
                    'type' => 'left'
                );
                $options = array(
                    'columns' => array(),
                    'where' => array(
                        'restaurant_cuisines.status' => 1,
                        'restaurant_cuisines.restaurant_id' => $details['id'],
                    ),
                    'joins' => $joins
                );
                $restaurantCuisineModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
                $cuisineResponse = $restaurantCuisineModel->find($options)->toArray();
                $detailResponse[$key]['cuisines'] = $cuisineResponse;
            }
        }
    }
    
}
