<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Menu;
use Restaurant\RestaurantDetailsFunctions;
use MCommons\Caching;
use Bookmark\Model\FoodBookmark;

class WebMenuSpecificDealController extends AbstractRestfulController {
    /*
     * this function will get menu details of restaurant
     */

    public function get($restaurant_id = 0) {
        try{
        $memCached = $this->getServiceLocator()->get('memcached');
        $config = $this->getServiceLocator()->get('Config');
        $userId = $this->getUserSession()->getUserId();
        if($userId>0){        
//        if ($config['constants']['memcache'] && $memCached->getItem('menu_mob_special_' . $restaurant_id)) {
//            return $memCached->getItem('menu_mob_special_' . $restaurant_id);
//        } else {
            // Get restaurant menu
            $menuModel = new Menu ();
            $foodbookmark = new FoodBookmark ();
            RestaurantDetailsFunctions::$_bookmark_types = $menuModel->bookmark_types;
            RestaurantDetailsFunctions::$_isMobile = $this->isMobile();
            $response = $menuModel->restaurantMenuesSpecific(array(
                        'columns' => array(
                            'restaurant_id' => $restaurant_id,
                            'user_deals' => 1
                        )
                    ))->toArray();
            
            if (!empty($response)) {
            foreach($response as $key=>$val){
               $response[$key]['item_id']=$val['category_id']; 
               $response[$key]['item_name']=$val['category_name']; 
               $response[$key]['item_desc']=$val['category_desc']; 
               unset($response[$key]['category_id']);
               unset($response[$key]['category_name']);
               unset($response[$key]['category_desc']);
               unset($response[$key]['pid']);
              $response[$key]['prices']= $menuModel->restaurantMenuesSpecificPrice($val['category_id']);
              $bookmarkcount = $foodbookmark->getMenuBookmarkCount($restaurant_id,$val['category_id']);
              
                if ($bookmarkcount) {
                    foreach ($bookmarkcount as $bdata) {
                        $k = $bdata ['type'];
                        $bmdata [$k] = $bdata ['total_count'];                    
                    }
                    $response[$key] ['love_count'] = isset($bmdata ['lo'])?(string)$bmdata ['lo']:'0';
                    $response[$key] ['tried_count'] = isset($bmdata ['ti'])?(string)$bmdata ['ti']:'0';
                    $response[$key] ['craving_count'] = isset($bmdata ['wi'])?(string)$bmdata ['wi']:'0';
                    $response[$key] ['user_tried_it'] = false;
                    $response[$key] ['user_loved_it'] = false;
                    $response[$key] ['user_craving_it'] = false;
                    $response[$key] ['bookmarks_loaded'] = false;
                } else {
                    $response[$key] ['love_count'] ='0';
                    $response[$key] ['tried_count'] ='0';
                    $response[$key] ['craving_count'] ='0';
                    $response[$key] ['bookmarks_loaded'] = false;
                    $response[$key] ['user_tried_it'] = false;
                    $response[$key] ['user_loved_it'] = false;
                    $response[$key] ['user_craving_it'] = false;
                }
            }   
            } else {
                return array();
            }
           $memCached->setItem('menu_mob_special_' . $restaurant_id, $response, 0);
           $res['category_name']='Dine & More Specials'; 
           $res['category_id']='9999999999'; 
           $res['prices']=[]; 
           $res['sub_categories']=[]; 
           $res['friend_loveit']=''; 
           $res['category_desc']='Custom made meals and offers only available to Dine & More members.'; 
           $res['category_items']=$response;
           return $res;
        //}
        }else{
            return array();
        }
        } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On Menu Api');
           throw new \Exception($e->getMessage(),400);
        }
    }

}
