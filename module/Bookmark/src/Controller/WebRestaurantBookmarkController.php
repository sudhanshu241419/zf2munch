<?php

namespace Bookmark\Controller;

use MCommons\Controller\AbstractRestfulController;
use Bookmark\Model\RestaurantBookmark;
use MCommons\StaticOptions;
use Restaurant\Model\Restaurant;
use User\UserFunctions;

class WebRestaurantBookmarkController extends AbstractRestfulController {

    public function create($data) {
        $bookmarkModel = new RestaurantBookmark ();
        $restModel = new Restaurant();
        $session = $this->getUserSession();
        $bookmarkModel->user_id = $session->getUserId();
        $data = StaticOptions::filterRequestParams($data);
        $bookmarkModel->restaurant_id = $data ['restaurant_id'];
        $bookmarkModel->type = $data ['type'];
        $isRestExists = $restModel->isRestaurantExists($bookmarkModel->restaurant_id);
        $this->debug = ($this->getQueryParams('DeBuG', '') == '404') ? TRUE : FALSE;
        $cache_key = 'bm_'.$bookmarkModel->restaurant_id. '_restaurant';
        $cache_data = \Search\SearchFunctions::getCacheData($cache_key, $this->debug);
        
        if (!$isRestExists) {
            throw new \Exception("Invalid restaurant", 400);
        }
        if (!$bookmarkModel->user_id) {
            throw new \Exception("Invalid user", 400);
        }
        if (empty($bookmarkModel->restaurant_id) || empty($bookmarkModel->type)) {
            throw new \Exception("Invalid restaurant detail", 400);
        }

        if (!$bookmarkModel->type) {
            throw new \Exception("Invalid bookmark type", 400);
        }
        
        $userModel = new \User\Model\User();
        $userDetailOption = array('columns' => array('first_name', 'last_name','email'), 'where' => array('id' => $session->getUserId()));
        $userDetail = $userModel->getUser($userDetailOption);        
        $currentDate = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $data ['restaurant_id']
                    ))->format('Y-m-d h:i');
        if ($isRestExists) {
            $restaurant = $restModel->findByRestaurantId(array(
                'column' => array('restaurant_name'),
                'where' => array('id' => $bookmarkModel->restaurant_id)
            ));

            $bookmarkModel->restaurant_name = isset($restaurant['restaurant_name']) ? $restaurant['restaurant_name'] : "";
        }
        $bookmarkModel->created_on = StaticOptions::getDateTime()->format(StaticOptions::MYSQL_DATE_FORMAT);
        // check for existing record
         $isAlreadyBookedmark = $bookmarkModel->isAlreadyBookmark(array(
            'type' => $bookmarkModel->type,
            'restaurant_id' => $bookmarkModel->restaurant_id,
            'user_id' => $bookmarkModel->user_id
                ));
         $userFunctions = new UserFunctions();
         $description = "";
         if($bookmarkModel->type==='lo'){
                $identifier = 'loveRestaurant';
                $message = "Your love is what makes the world go round. We value it at 1 point per restaurant.";
                $description = "loveit";
            }elseif($bookmarkModel->type==='wl'){
                $identifier = 'craveitrestaurant';
                $message = "You’re craving ".$bookmarkModel->restaurant_name." something fierce. That’s worth a point.";
                $description = "craveit";
            }elseif($bookmarkModel->type==='bt'){
               $identifier = 'beenthererestaurant';
               $message = "You told us you’ve been to ".$bookmarkModel->restaurant_name.". Your tale of food conquest has earned you 1 point.";
               $description = "beenthere";
            }
         $points = $userFunctions->getAllocatedPoints($identifier);
         
        if (!empty($isAlreadyBookedmark)) {
            $response = array();
            $response['restaurant_id']=$bookmarkModel->restaurant_id;
            $response['user_id']=$bookmarkModel->user_id;
            $response['type']=$bookmarkModel->type;  
            $bookmarkModel->id = $isAlreadyBookedmark[0]['id'];
            $rowEffected = $bookmarkModel->delete();              
            $userFunctions->takePoints($points, $bookmarkModel->user_id, $bookmarkModel->restaurant_id);
            $bookmarkCount = $bookmarkModel->getRestaurantBookmarkCountOfType($bookmarkModel->restaurant_id,$bookmarkModel->type);
            $resBookmarkCount = $bookmarkCount[0];
            if($resBookmarkCount['total_count']!=0){
                if ($bookmarkModel->type == 'lo') {
                   $response ['love_count'] = $resBookmarkCount['total_count'];
                   $cache_data['love_count'] = $resBookmarkCount['total_count'];
                   $cache_data['user_loved_it'] = false;
                   $response ['user_loved_it'] = false;                       
                } elseif ($bookmarkModel->type == 'bt') {
                   $response ['been_count'] = $resBookmarkCount['total_count'];
                   $cache_data['been_count'] = $resBookmarkCount['total_count'];
                   $cache_data['user_been_there'] = false;
                   $response ['user_been_there'] = false;                        
                } elseif($bookmarkModel->type == 'wl') {
                   $response ['crave_count'] = $resBookmarkCount['total_count'];
                   $response ['user_crave_it'] = false;                        
                }
            }else{
                 if($bookmarkModel->type=="bt"){
                    $response ['been_count'] = 0;
                    $cache_data['been_count'] = 0;
                    $cache_data['user_been_there'] = false;
                    $response ['user_been_there'] = false;
                }elseif($bookmarkModel->type=="lo"){
                    $response ['love_count'] = 0;
                    $cache_data['love_count'] = 0;
                    $cache_data['user_loved_it'] = false;
                    $response ['user_loved_it'] = false;
                }elseif($bookmarkModel->type=="wl"){
                    $response ['crave_count'] = 0;
                    $response ['user_crave_it'] = false;
                }
            }
            \Search\SearchFunctions::setCacheData($cache_key, $cache_data, 86400);
            $cleverTap = array(
                "user_id" => $session->getUserId(),
                "name" => $userDetail['first_name'],
                "email" => $userDetail['email'],
                "identity"=>$userDetail['email'],
                "restaurant_name" => $bookmarkModel->restaurant_name,
                "restaurant_id" => $data ['restaurant_id'],
                "eventname" => "uncheck_bookmark",
                "point_redeemed" => $points ['points'],
                "is_register" => "yes",
                "date" => $currentDate,
                "type" => "restaurant",
                "description" =>$description,
                "event"=>1,
                "bookmark_type"=>$bookmarkModel->type
            );
            $userFunctions->createQueue($cleverTap, 'clevertap');
        }else{
            $response = $bookmarkModel->addRestaurantBookMark();

            if ($response) {
                        
                $userFunctions->givePoints($points, $bookmarkModel->user_id, $message, $bookmarkModel->restaurant_id);
                $bookmarkCount = $bookmarkModel->getRestaurantBookmarkCount($bookmarkModel->restaurant_id);
                $cleverTap = array(
                    "user_id" => $session->getUserId(),
                    "name" => $userDetail['first_name'],
                    "email" => $userDetail['email'],
                    "identity"=>$userDetail['email'],
                    "restaurant_name" => $bookmarkModel->restaurant_name,
                    "restaurant_id" => $data ['restaurant_id'],
                    "eventname" => "bookmark",
                    "earned_points" => $points ['points'],
                    "is_register" => "yes",
                    "date" => $currentDate,
                    "type" => "restaurant",
                    "description" =>$description,
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
                    if ($val ['type'] == 'bt' && $bookmarkModel->type == 'bt') {
                        $response ['been_count'] = isset($val ['total_count']) ? $val ['total_count'] : 0;
                        $cache_data['been_count'] = isset($val ['total_count']) ? $val ['total_count'] : 0;
                        $cache_data['user_been_there'] = true;
                        $response ['user_been_there'] = true;
                        \Search\SearchFunctions::setCacheData($cache_key, $cache_data, 86400);
                        return $response;
                    }
                    if ($val ['type'] == 'wl' && $bookmarkModel->type == 'wl') {
                        $response ['crave_count'] = isset($val ['total_count']) ? $val ['total_count'] : 0;
                        $response ['user_crave_it'] = true;
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
