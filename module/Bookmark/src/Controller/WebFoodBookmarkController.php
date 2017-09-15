<?php

namespace Bookmark\Controller;

use MCommons\Controller\AbstractRestfulController;
use Bookmark\Model\FoodBookmark;
use MCommons\StaticOptions;
use Restaurant\Model\Menu;
use User\UserFunctions;

class WebFoodBookmarkController extends AbstractRestfulController {

    public function create($data) {
        $bookmarkModel = new FoodBookmark ();
        $menuModel = new Menu ();
        $session = $this->getUserSession();
        $bookmarkModel->user_id = $session->getUserId();
        $description = "";
        $bookmarkModel->menu_id = $data ['menu_id'];
        $bookmarkModel->restaurant_id = $data ['restaurant_id'];        
        $bookmarkModel->type = $data ['type'];
        $currentDate = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $data ['restaurant_id']
                    ))->format('Y-m-d h:i');
        $this->debug = ($this->getQueryParams('DeBuG', '') == '404') ? TRUE : FALSE;
        $cache_key = 'bm_'.$bookmarkModel->menu_id. '_food';
        $cache_data = \Search\SearchFunctions::getCacheData($cache_key, $this->debug);
        if (!$bookmarkModel->user_id) {
            throw new \Exception("Invalid user", 400);
        }
        if (!$bookmarkModel->restaurant_id)
            throw new \Exception("Invalid restaurant id", 400);
        
        if (!$bookmarkModel->menu_id) {
            throw new \Exception("Invalid menu id", 400);
        }

        if (!$bookmarkModel->type) {
            throw new \Exception("Invalid bookmark type", 400);
        }
        
        $restaurantModel = new \Restaurant\Model\Restaurant();
        $restaurantDetailOption = array('columns' => array('rest_code','restaurant_name'), 'where' => array('id' => $data ['restaurant_id']));
        $restDetail = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray();
        
        $userModel = new \User\Model\User();
        $userDetailOption = array('columns' => array('first_name', 'last_name','email'), 'where' => array('id' => $session->getUserId()));
        $userDetail = $userModel->getUser($userDetailOption);
        
        
         $isMenuExists = $menuModel->isFoodExists($bookmarkModel->menu_id, $bookmarkModel->restaurant_id);
         $menuFind = $menuModel->getMenuDetail($bookmarkModel->menu_id);
         $item_name=(isset($menuFind['item_name']) && $menuFind['item_name']!='')?$menuFind['item_name']:'some food';
        if($bookmarkModel->type==='lo'){
            $identifier = 'loveFood';            
            $message = "Love is priceless and never pointless. You earned one point for loving ".$item_name." from ".$restDetail['restaurant_name'].".";
            $description = "loveit";
        }elseif($bookmarkModel->type==='wi'){
            $identifier = 'craveFood';
            $message = "You craved ".$item_name." from ".$restDetail['restaurant_name']."! Here is 1 point to hold you over.";
            $description = "craveit";
        }elseif($bookmarkModel->type==='ti'){
            $identifier = 'tryFood';
            $message = "You have tried ".$item_name." from ".$restDetail['restaurant_name']."! This calls for a celebration, here is 1 point!";
            $description = "tryit";
        }   
        $userFunctions = new UserFunctions();
        $points = $userFunctions->getAllocatedPoints($identifier);
       
       
       
        $bookmarkModel->created_on = StaticOptions::getDateTime()->format(StaticOptions::MYSQL_DATE_FORMAT);
        
        if ($isMenuExists) {
            
            $bookmarkModel->menu_name = $menuFind['item_name'];
        } 
        
        $bookmarkModel->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
        $options = array ('columns' => array ('menu_id','id'),
                          'where' => array (
                                'menu_id' => $bookmarkModel->menu_id,
                                'user_id' => $bookmarkModel->user_id,
                                'type' => $bookmarkModel->type
                             ) 
                          );
        $isAlreadyBookedmark = $bookmarkModel->find ( $options )->toArray();
        
        if(!empty($isAlreadyBookedmark)){
            $response = array();
            $response['menu_id']=$bookmarkModel->menu_id;
            $response['user_id']=$bookmarkModel->user_id;
            $response['type']=$bookmarkModel->type;  
            $bookmarkModel->id = $isAlreadyBookedmark[0]['id'];
            $rowEffected = $bookmarkModel->delete();             
            
            $userFunctions->takePoints($points, $bookmarkModel->user_id, $bookmarkModel->menu_id);
            $bookmarkCount = $bookmarkModel->getMenuBookmarkCountOfType($bookmarkModel->restaurant_id,$bookmarkModel->menu_id,$bookmarkModel->type);
            $totalBookMarkCountOfType = $bookmarkCount[0];
            if($totalBookMarkCountOfType['total_count']!= 0){
                if($bookmarkModel->type=="ti"){
                    $response ['tried_count'] = $totalBookMarkCountOfType['total_count'];
                    $cache_data['tried_count'] = $totalBookMarkCountOfType['total_count'];
                    $cache_data['user_tried_it'] = false;
                    $response ['user_tried_it'] = false;
                    $description = "tryit";
                }elseif($bookmarkModel->type=="lo"){
                    $response ['love_count'] = $totalBookMarkCountOfType['total_count'];
                    $cache_data['love_count'] = $totalBookMarkCountOfType['total_count'];
                    $cache_data['user_loved_it'] = false;
                    $response ['user_loved_it'] = false;
                    $description = "loveit";
                }elseif($bookmarkModel->type=="wi"){
                    $response ['craving_count'] = $totalBookMarkCountOfType['total_count'];
                    $cache_data['craving_count'] = $totalBookMarkCountOfType['total_count'];
                    $cache_data['user_craving_it'] = false;
                    $response ['user_craving_it'] = false;
                    $description = "craveit";
                }
                
            } else{
                if($bookmarkModel->type=="ti"){
                    $response ['tried_count'] = 0;
                    $cache_data['tried_count'] = "0";
                    $cache_data['user_tried_it'] = false;
                    $response ['user_tried_it'] = false;
                    $description = "tryit";
                }elseif($bookmarkModel->type=="lo"){
                    $response ['love_count'] = 0;
                    $cache_data['love_count'] = "0";
                    $cache_data['user_loved_it'] = false;
                    $response ['user_loved_it'] = false;
                    $description = "loveit";
                }elseif($bookmarkModel->type=="wi"){
                    $response ['craving_count'] = 0;
                    $cache_data['craving_count'] = "0";
                    $cache_data['user_craving_it'] = false;
                    $response ['user_craving_it'] = false;
                    $description = "craveit";
                }
            }            
          \Search\SearchFunctions::setCacheData($cache_key, $cache_data, 86400);
          $cleverTap = array(
                "user_id" => $session->getUserId(),
                "name" => $userDetail['first_name'],
                "email" => $userDetail['email'],
                "identity"=>$userDetail['email'],
                "restaurant_name" => $restDetail['restaurant_name'],
                "restaurant_id" => $data['restaurant_id'],
                "eventname" => "uncheck_bookmark",
                "point_redeemed" => $points ['points'],
                "is_register" => "yes",
                "date" => $currentDate,
                "type" => "food",
                "description" =>$description,
                "menu_item"=>$item_name,
                "event"=>1,
                "bookmark_type"=>$bookmarkModel->type
                );
         $userFunctions->createQueue($cleverTap, 'clevertap');
        }else {
            $response = $bookmarkModel->addBookmark();
            if ($response) {
                $userFunctions->givePoints($points, $bookmarkModel->user_id, $message, $bookmarkModel->menu_id);
                $bookmarkCount = $bookmarkModel->getMenuBookmarkCount($bookmarkModel->restaurant_id, $bookmarkModel->menu_id);
                $cleverTap = array(
                    "user_id" => $session->getUserId(),
                    "name" => $userDetail['first_name'],
                    "email" => $userDetail['email'],
                    "identity"=>$userDetail['email'],
                    "restaurant_name" => $restDetail['restaurant_name'],
                    "restaurant_id" => $data['restaurant_id'],
                    "eventname" => "bookmark",
                    "earned_points" => $points ['points'],
                    "is_register" => "yes",
                    "date" => $currentDate,
                    "type" => "food",
                    "description" =>$description,
                    "menu_item"=>$item_name,
                    "event"=>1,
                    "bookmark_type"=>$bookmarkModel->type
                );
                $userFunctions->createQueue($cleverTap, 'clevertap');
                
                foreach ($bookmarkCount as $key => $val) {
                    if ($val ['type'] == 'lo' && $bookmarkModel->type == 'lo') {
                        $response ['love_count'] = isset($val ['total_count']) ? $val ['total_count'] : 0;
                        $cache_data['love_count'] = isset($val ['total_count']) ? $val ['total_count'] : 0;
                        $cache_data['user_loved_it'] = true;
                        $response ['user_loved_it'] = true;
                        \Search\SearchFunctions::setCacheData($cache_key, $cache_data, 86400);
                        return $response;
                    }
                    if ($val ['type'] == 'ti' && $bookmarkModel->type == 'ti') {
                        $response ['tried_count'] = isset($val ['total_count']) ? $val ['total_count'] : 0;
                        $cache_data['tried_count'] = isset($val ['total_count']) ? $val ['total_count'] : 0;
                        $cache_data['user_tried_it'] = true;
                        $response ['user_tried_it'] = true;
                        \Search\SearchFunctions::setCacheData($cache_key, $cache_data, 86400);
                        return $response;
                    }
                    if ($val ['type'] == 'wi' && $bookmarkModel->type == 'wi') {
                        $response ['craving_count'] = isset($val ['total_count']) ? $val ['total_count'] : 0;
                        $cache_data['craving_count'] = isset($val ['total_count']) ? $val ['total_count'] : 0;
                        $cache_data['user_craving_it'] = true;
                        $response ['user_craving_it'] = true;
                        \Search\SearchFunctions::setCacheData($cache_key, $cache_data, 86400);
                        
                        $restaurantFunctions = new \Restaurant\RestaurantDetailsFunctions();
                        $restuarantAddress = $restaurantFunctions->restaurantAddress($data['restaurant_id']);
                        $salesData['owner_email'] = 'no-reply@munchado.com';
                        $salesData['email'] = $userDetail['email'];    
                        $salesData['restaurant_name'] = $restDetail['restaurant_name']; 
                        $salesData['restaurant_id'] = $data['restaurant_id']; 
                        $salesData['value']=$points ['points'];
                        $salesData['description'] = "craved_food_menu";
                        $salesData['contact_ext_event_type'] = "OTHER"; 
                        $salesData['location'] = $restuarantAddress;
                        $salesData['identifier']="event";              
                
                        //$userFunctions->createQueue($salesData,'Salesmanago');                       
                        
                        return $response;
                    }
                }
                
                unset($response ['type']);
                unset($response ['id']);
                unset($response ['user_id']);
                unset($response ['restaurant_id']);
            } else {
                throw new \Exception("Unable to save restaurant bookmark", 400);
            }
        }
        return $response;
    }
    
}
