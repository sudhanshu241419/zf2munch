<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Menu;
use Restaurant\RestaurantDetailsFunctions;
use MCommons\Caching;

class WebMenuController extends AbstractRestfulController {
    /*
     * this function will get menu details of restaurant
     */

    public function get($restaurant_id = 0) {
        //$memCached = new Caching();
        $memCached = $this->getServiceLocator()->get('memcached');
        $config = $this->getServiceLocator()->get('Config');
        $menuRes = '';
        if ($config['constants']['memcache'] && $memCached->getItem('menu_' . $restaurant_id)) {
            $menuRes = $memCached->getItem('menu_' . $restaurant_id);
        } else {
            // Get restaurant menu
            $menuModel = new Menu ();
            $restaurant = new \Restaurant\Model\Restaurant();
            $restDetails = $restaurant->findRestaurant(array('columns'=>array('menu_sort_order'),'where'=>array('id'=>$restaurant_id)));
            //pr($restDetails->menu_sort_order,1);
            RestaurantDetailsFunctions::$_bookmark_types = $menuModel->bookmark_types;
            RestaurantDetailsFunctions::$_isMobile = $this->isMobile();
            $response = $menuModel->restaurantMenues(array(
                    'columns' => array(
                        'restaurant_id' => $restaurant_id,
                        'user_deals' => 0
                    )
                ),$restDetails->menu_sort_order)->toArray();
            if (!empty($response)) {                
                $response = RestaurantDetailsFunctions::createWebNestedMenu($response, $restaurant_id);
                $response = RestaurantDetailsFunctions::knowLastLeaf($response);
                $response = RestaurantDetailsFunctions::formatResponse($response);
            } else {
                return array();
            }
            $menuRes = $response;
            $memCached->setItem('menu_' . $restaurant_id, $response, 0);
        }
        
        
        $dealSpecialMenu = $this->menuSpecificDeal($restaurant_id);
        $userDealSpecialMenu = $this->particularUserDealOnMenu($restaurant_id);
        if (!empty($dealSpecialMenu)) {
            $specialMenu2[0] = $dealSpecialMenu;
            $menuRes = array_merge($specialMenu2, $menuRes);
        }  
        
        if(!empty($userDealSpecialMenu)){
            $specialUserDeal[0] = $userDealSpecialMenu;
            $menuRes = array_merge($specialUserDeal, $menuRes);
        }
        $finalResponse = array(
            'menu' => $menuRes
        );
       
        return $finalResponse;
    }

    public function menuSpecificDeal($restaurant_id) {       
        
            $menuModel = new Menu ();
            $response = $menuModel->restaurantMenuesSpecific(array(
                    'columns' => array(
                        'restaurant_id' => $restaurant_id,                        
                    )
                ))->toArray();

            if (!empty($response)) {
                foreach ($response as $key => $val) {
                    $response[$key]['item_id'] = $val['category_id'];
                    $response[$key]['item_name'] = $val['category_name'];
                    $response[$key]['item_desc'] = $val['category_desc'];
                    unset($response[$key]['category_id']);
                    unset($response[$key]['category_name']);
                    unset($response[$key]['category_desc']);
                    unset($response[$key]['pid']);
                    $response[$key]['prices'] = $menuModel->restaurantMenuesSpecificPrice($val['category_id']);
                }
                $res['category_name'] = 'Dine & More Specials';
                $res['category_id'] = '9999999999';
                $res['prices'] = [];
                $res['sub_categories'] = [];
                $res['friend_loveit'] = '';
                $res['category_desc'] = 'Custom made meals and offers only available to Dine & More members.';
                $res['category_items'] = $response;
                return $res;
            }
            return array();        
    }
    
    public function particularUserDealOnMenu($restaurant_id){
        $userId = $this->getUserSession()->getUserId();
        if ($userId > 0) {
            $menuModel = new Menu ();
            $response = $menuModel->particularUserDealOnMenu(array(
                    'columns' => array(
                        'restaurant_id' => $restaurant_id,
                        'user_deals' => 1
                    )
                ))->toArray();

            if (!empty($response)) {
                foreach ($response as $key => $val) {
                    $response[$key]['item_id'] = $val['category_id'];
                    $response[$key]['item_name'] = $val['category_name'];
                    $response[$key]['item_desc'] = $val['category_desc'];
                    unset($response[$key]['category_id']);
                    unset($response[$key]['category_name']);
                    unset($response[$key]['category_desc']);
                    unset($response[$key]['pid']);
                    $response[$key]['prices'] = $menuModel->restaurantMenuesSpecificPrice($val['category_id']);
                }
                $res['category_name'] = 'Dine & More Specials';
                $res['category_id'] = '9999999999';
                $res['prices'] = [];
                $res['sub_categories'] = [];
                $res['friend_loveit'] = '';
                $res['category_desc'] = 'Custom made meals and offers only available to Dine & More members.';
                $res['category_items'] = $response;
                return $res;
            }
            return array();
        } else {
            return array();
        }
    }

}
