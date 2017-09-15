<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;

class RestaurantController extends AbstractRestfulController {

    public $restaurantId;
    public $restaurantAddress;

    public function getList() {
        $restaurantModel = new \Dashboard\Model\Restaurant();
        $dashboardFunctions = new DashboardFunctions();
        $this->restaurantId = $dashboardFunctions->getRestaurantId();
        if (!$this->restaurantId) {
            throw new \Exception('Restaurant details not found', 400);
        }
        $data = [];
        $varRestaurantDetails = $restaurantModel->getRestaurantDetails($this->restaurantId);
        $image_url = !empty($varRestaurantDetails['restaurant_image_name']) ? IMAGE_PATH . strtolower($varRestaurantDetails['rest_code']) . "/" . $varRestaurantDetails['restaurant_image_name'] : '';
        $data = $varRestaurantDetails;
        $data['cover_image'] = $image_url;
        $resCuisineModel = new \Dashboard\Model\RestaurantCuisine();
        $resFeatureModel = new \Dashboard\Model\Feature();
        $varStoryModel = new \Dashboard\Model\Story();
        $resRestaurantFeatureModel = new \Dashboard\Model\RestaurantFeature();
        $data['search_data'] = $resCuisineModel->get_cuisine_types_and_popular_foods_and_trends();
        $data['cusines'] = $resCuisineModel->get_restaurant_cuisine_string($this->restaurantId);
        $data['cusines_id'] = $resCuisineModel->get_restaurant_cuisines_id($this->restaurantId);
        $data['type_of_restaurant'] = $resFeatureModel->get_feature_all_data();
        $data['features_id'] = $resRestaurantFeatureModel->get_restaurant_features_id($this->restaurantId);
        $data['features'] = $resFeatureModel->get_restaurant_features_string($this->restaurantId);
        $story = $varStoryModel->get_story($this->restaurantId);
        if (count($story) > 0) {
            $data['story'][0]['restaurant_desc'] = $story[0]['restaurant_desc'];
            $data['story'][0]['restaurant_history'] = $story[0]['restaurant_history'];
            $data['story'][0]['final_story'] = $story[0]['final_story'];
            $data['story'][0]['title'] = $story[0]['title'];
            $data['story'][0]['decor'] = $story[0]['decor'];
            $data['story'][0]['atmosphere'] = $story[0]['atmosphere'];
            $data['story'][0]['cuisine'] = $story[0]['cuisine'];
            $data['story'][0]['neighborhood'] = $story[0]['neighborhood'];
            $data['story'][0]['service'] = $story[0]['service'];
            $data['story'][0]['experience'] = $story[0]['experience'];
            $data['story'][0]['ambience'] = $story[0]['ambience'];
            $data['story'][0]['fun_facts'] = $story[0]['fun_facts'];
            $data['story'][0]['location'] = $story[0]['location'];
        }

        $title = $data['role'];
        if ($title == 'a') {
            $data['title'] = 'Admin';
        } elseif ($title == 'o') {
            $data['title'] = 'Owner';
        } elseif ($title == 'm') {
            $data['title'] = 'Manager';
        } else {
            $data['title'] = '';
        }
        $notification = new \Dashboard\Model\UserNotification();
        $options = array("where" => array('read_status' => 0));
        $totalPendigNotification = $notification->countPendingNotification($this->restaurantId);
        $data['pending_notification'] = $totalPendigNotification[0]['total'];
        $currentVersion = $this->getQueryParams("current_version",false);
        $fourceUp=\MCommons\StaticOptions::fourceUpdate($currentVersion);  
        $data['fource_update'] = $fourceUp;
        if(!$data['name']){
            $data['name'] = explode("@", $data['email'])[0];
        }
        
        return $data;
    }

    public function create($data) {
        //pr($data,1);
        $responce = [];
        $restaurantModel = new \Dashboard\Model\Restaurant();
        $resCuisineModel = new \Dashboard\Model\RestaurantCuisine();
        $resFeatureModel = new \Dashboard\Model\RestaurantFeature();
        $resAccountModel = new \Dashboard\Model\RestaurantAccounts();
        $dashboardFunctions = new DashboardFunctions();
        $this->restaurantId = $dashboardFunctions->getRestaurantId();
        $responce = $restaurantModel->updateRestaurantDetails($data, $this->restaurantId);
        $responce = $resCuisineModel->update_restaurant_cuisine($data['cuisines'], $this->restaurantId);
        $responce = $resFeatureModel->update_restaurant_features($data['features'], $this->restaurantId);
        $responce = $resAccountModel->update_details($data, $this->restaurantId);
        return $responce;
    } 
}
