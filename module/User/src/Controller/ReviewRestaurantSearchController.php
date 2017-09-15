<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Zend\Db\Sql\Predicate\Expression;
use Home\Model\City;
use User\Model\User;
use MCommons\StaticOptions;

class ReviewRestaurantSearchController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function get($id) {

        $city_model = new City ();
        $user_model = new User ();
        $session = $this->getUserSession();
        $getCityId = $session->getUserDetail('selected_location');        
        $city_id = isset($getCityId['city_id'])?$getCityId['city_id']:18848;

        $q = $this->getQueryParams('q');
        if (strlen($q) < 3) {
            throw new \Exception('You need to enter atleast 3 characters');
        }
        $restaurantModel = new Restaurant ();
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'restaurant_id' => 'id',
                'restaurant_name',                
                'address',
                'delivery',
                'takeout',
                'reservations',
                'dinein'=>'dining',
                'menu_without_price',
                'accept_cc_phone',
                'rest_code',
                'restaurant_primary_image'=>'restaurant_image_name'
            ),
            'like' => array(
                'field' => 'restaurant_name',
                'like' => '%' . $q . '%'
            ),
            'where' => new Expression('(delivery = 1 OR takeout = 1 OR reservations = 1 OR dining = 1) AND (inactive = 0 AND closed =0) AND city_id=' . $city_id),
            'limit' => 5
        );
        $response = $restaurantModel->find($options)->toArray();
        
        if($response){
            foreach ($response as $key => $value) {           
                $currentDayDelivery = StaticOptions::getPerDayDeliveryStatus ( $value['restaurant_id'] );
                $response[$key]['delivery'] = ($currentDayDelivery)?"1":"0";
                $accept_cc_phone = (int) ($value ['accept_cc_phone']);
                $menu_without_price = (int) ($value ['menu_without_price']);
                if ($menu_without_price || !$accept_cc_phone) {
                    $response[$key]['delivery'] = "0";
                    $response[$key]['takeout'] = "0";
                }
                $response[$key]['rest_code'] = strtolower($value['rest_code']);
            }
        }else{
            $response = array();
        }
        return $response;
    }

}
