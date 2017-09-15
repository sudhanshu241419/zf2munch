<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use MCommons\StaticOptions;

class WebRestaurantSeoController extends AbstractRestfulController {

    public function getList() {
        $restaurantModel = new Restaurant ();
        $state = $this->getQueryParams('state');
        $city = $this->getQueryParams('city');
        $cityName = str_replace('-', ' ', $city);
        $page = $this->getQueryParams('page');
        $cityModel = new \Home\Model\City();
        $cityData = $cityModel->citySearch(array(
            'columns' => array(
                'id'
            ),
            'where' => array(
                'state_code' => strtoupper($state)
            ),
            'like' => array(
                'field' => 'city_name',
                'like' => $cityName
            )
        ));
        if (!empty($cityData)) {
            $cityData = array_pop($cityData);
        }
        if ($cityData && !empty($cityData)) {
            $restaurant = new \Restaurant\Model\Restaurant();
            if (!$page) {
                $count = $restaurant->getRestaurantCountByCity($cityData['id']);
                return array('count' => $count);
            }
            $list = $restaurant->getRestaurantListByCity($cityData['id'], $page);
            return array('list' => $list);
        }
        return array('count' => 0);
    }

}
