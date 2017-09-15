<?php

namespace Cuisine\Controller;

use MCommons\Controller\AbstractRestfulController;
use Cuisine\Model\Cuisine;
use Cuisines\CuisineFunctions;
use Typeofplace\Model\Feature;
use Typeofplace\TypeofplaceFunctions;

class PopularCuisineController extends AbstractRestfulController {

    public function getList() {
        $CuisineModel = new Cuisine ();

        $allCuisine = $CuisineModel->getAllCuisine(array(
            'columns' => array(
                'id',
                'name' => 'cuisine',
                'type' => 'cuisine_type',
                'priority'
            ),
            'where' => array(
                'status' => 1,
                'search_status' => 1
            )
        ));
        $response = CuisineFunctions::getCuisineTypePopularFoodTrends($allCuisine, $this->isMobile());
        $popularCuisine = array();
        $i=0;
        foreach($response['favorites'] as $key => $val){
            if($val['is_popular']==1){
                $popularCuisine['favorites'][$i]['id'] = $val['id'];
                $popularCuisine['favorites'][$i]['name'] = $val['name'];
                $popularCuisine['favorites'][$i]['featurekey'] = $val['image_icon'];
                $i++;
            }
          
        }
        $i=0;
        foreach($response['preferences'] as $key => $val){
            if($val['is_popular']==1){
                $popularCuisine['preferences'][$i]['id'] = $val['id'];
                $popularCuisine['preferences'][$i]['name'] = $val['name'];
                $popularCuisine['preferences'][$i]['featurekey'] = $val['image_icon'];
                $i++;
            }
         
        }
        
        $FeatureModel = new Feature ();
        $featureData = $FeatureModel->getFeature()->toArray();
        $data = TypeofplaceFunctions::featureData($featureData);
        $i=0;
        
        foreach($data['features'] as $key => $val){
            if($val['features']==="Happy Hour"){
                $popularCuisine['features'][$i]['id'] = $val['id'];
                $popularCuisine['features'][$i]['name'] = $val['features'];
                $popularCuisine['features'][$i]['featurekey'] = $val['features'];
                $i++;
            }
            
            if($val['features']==="Open 24 hours"){
                $popularCuisine['features'][$i]['id'] = $val['id'];
                $popularCuisine['features'][$i]['name'] = $val['features'];
                $popularCuisine['features'][$i]['featurekey'] = $val['features'];
                $i++;
            }
          
        }
        $i=0;
        foreach($data['type_of_place'] as $key => $val){
            if($val['features']==="Pub"){
                $popularCuisine['type_of_place'][$i]['id'] = $val['id'];
                $popularCuisine['type_of_place'][$i]['name'] = $val['features'];
                $popularCuisine['type_of_place'][$i]['featurekey'] = $val['features_key'];
                $i++;
            }
          
        }
        
        if(!isset($popularCuisine['favorites'])){
            $popularCuisine['favorites'] = array();
        }
        if(!isset($popularCuisine['preferences'])){
            $popularCuisine['preferences'] = array();
        }
        if(!isset($popularCuisine['features'])){
            $popularCuisine['features'] = array();
        }
        if(!isset($popularCuisine['type_of_place'])){
            $popularCuisine['type_of_place'] = array();
        }
       return $popularCuisine;
    }

}
