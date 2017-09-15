<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Restaurant\Model\Cuisine;
use Restaurant\Model\Tags;
use Restaurant\Model\Gallery;


class DinenmoreRestaurantsDetailsController extends AbstractRestfulController {

 
    public function getList() {
        $restaurantModel = new Restaurant();
        $joins = array();
        $tags = new Tags();
        
        $cuisine_data = "";
        $tagsDetails = $tags->getTagDetailByName("dine-more");
        $limit = $this->getQueryParams ('limit');
        $page = $this->getQueryParams('page');
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
                $offset = ($page - 1) * ($limit);
        }
        $joins[] = array(
            'name' => array(
                'rt' => 'restaurant_tags'
            ),
            'on' => 'restaurants.id = rt.restaurant_id',
            'columns' => array('tag_id'),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'id',
                'restaurant_name',
                'rest_code',
                'price',
                'latitude',
                'longitude',
                'landmark',
                'address',
                'zipcode',
                'restaurant_image_name'
                
            ),
            'joins' => $joins,
            'where' => array(
                'restaurants.inactive' => 0,
                'restaurants.closed' => 0,
                'rt.status'=>1,
                'rt.tag_id'=>$tagsDetails[0]['tags_id']
            ),
            'order' => 'restaurants.updated_on desc',
            //'limit' => $limit
        );
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $detailResponse = $restaurantModel->find($options)->toArray();
        
        $dinenmoreResponse = array();
        
        #Get cuisines of Restaurant#
        
        $config = $this->getServiceLocator()->get('Config');
        
        if ($detailResponse) {
            foreach ($detailResponse as $key => $details) {
                $dinenmoreResponse = $this->restaurantCuisine($key, $dinenmoreResponse, $details, $config,$cuisine_data);
            }
        }
        $final = array_slice($dinenmoreResponse, $offset, $limit);
        return array ('data' => $final, 'total'=>count($detailResponse));
    }

    private function restaurantCuisine($key, $dinenmoreResponse, $details, $config,$cuisine_data) {
        $restaurantCuisineModel = new Cuisine ();
        $restCode = strtolower($details['rest_code']);
        $galImage = isset($details['restaurant_image_name']) && $details['restaurant_image_name'] != '' ? $config['constants']['protocol'] . "://" . $config['constants']['imagehost'] . 'munch_images/' . $restCode . "/" . $details['restaurant_image_name'] : '';
        if ($galImage) {
            $dinenmoreResponse[$key]['res_id'] = $details['id'];
            $dinenmoreResponse[$key]['res_name'] = $details['restaurant_name'];
            $dinenmoreResponse[$key]['icon'] =  "restMarker.png";
            $dinenmoreResponse[$key]['res_price'] = $details['price'];           
            $dinenmoreResponse[$key]['image_path'] = $galImage;
            $dinenmoreResponse[$key]['restLag'] = $details['latitude'];
            $dinenmoreResponse[$key]['restLong'] = $details['longitude'];
            $dinenmoreResponse[$key]['rest_landmark'] = $details['landmark'];
            $dinenmoreResponse[$key]['rest_address'] = $details['address'].', Newyork, '.$details['zipcode'];
            $joins = array();
            $joins [] = array(
                'name' => array(
                    'c' => 'cuisines'
                ),
                'on' => 'c.id = restaurant_cuisines.cuisine_id',
                'columns' => array(
                    'name' => 'cuisine'
                ),
                'type' => 'inner'
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
            foreach($cuisineResponse as $v => $cuisine){
              $cuisine_data .= $cuisine['name'].', ';
            }
            $cuisine_data = substr($cuisine_data, 0, -2);
            $dinenmoreResponse[$key]['res_cuisine'] = $cuisine_data;
            //$dinenmoreResponse[$key]['res_cuisine'] = $cuisineResponse;
        }
        return $dinenmoreResponse;
    }

}
