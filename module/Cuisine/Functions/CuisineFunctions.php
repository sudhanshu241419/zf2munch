<?php

namespace Cuisines;

use MCommons\StaticOptions;

class CuisineFunctions {

    public static function getCuisineTypePopularFoodTrends($allCuisine, $isMobile = 0) {
        $data = array();
        $arrCuisinesList = array();
        if (!empty($allCuisine)) {
            $f = array('Pizza','Burgers','Sushi','Sandwiches & Wraps');
            $p = array('Gluten-Free','Organic','Vegan');
            foreach ($allCuisine as $key => $val) {
                $arrCuisinesList [$key] = $val;
                $arrCuisinesList [$key] ['image_icon'] = '';
                if ($val ['priority'] != 0) {
                    $arrCuisinesList [$key] ['is_popular'] = 1;
                } else {
                    $arrCuisinesList [$key] ['is_popular'] = 0;
                    unset($val ['priority']);
                }
                if (preg_match('/fast food/', strtolower($val ['type']))) {
                    $arrCuisinesList [$key]['image_icon'] = self::removeSpecialIcon($val['name']);
                    unset($arrCuisinesList [$key] ['type']); 
                    if(in_array($arrCuisinesList [$key]['name'], $f)){
                        $arrCuisinesList [$key]['is_popular'] = 1;
                    }else{
                        $arrCuisinesList [$key]['is_popular'] = 0;
                    }
                    $data['favorites'][] = $arrCuisinesList [$key];
                } else if (preg_match('/trends/', strtolower($val ['type']))) {
                    $arrCuisinesList [$key]['image_icon'] = self::removeSpecialIcon($val['name']);
                    unset($arrCuisinesList [$key] ['type']);
                    if(in_array($arrCuisinesList [$key]['name'], $p)){
                        $arrCuisinesList [$key]['is_popular'] = 1;
                    }else{
                        $arrCuisinesList [$key]['is_popular'] = 0;
                    }
                    $data['preferences'][] = $arrCuisinesList [$key];
                } else {
                    $arrCuisinesList [$key]['image_icon'] = self::removeSpecialIcon($val['name']);
                    unset($arrCuisinesList [$key] ['type']);
                    $data['region'][] = $arrCuisinesList [$key];
                    
                }
            }
        }
        return $data;
    }

    public static function getCuisineTypePopularFoodTrendsWeb($allCuisine, $isMobile = 0) {
        $data = array();
        $arrCuisinesList = array();
        if (!empty($allCuisine)) {

            foreach ($allCuisine as $key => $val) {
                $arrCuisinesList [$key] = $val;
                $arrCuisinesList [$key] ['image_icon'] = '';
                if ($val ['priority'] != 0) {
                    $arrCuisinesList [$key] ['is_popular'] = true;
                } else {
                    $arrCuisinesList [$key] ['is_popular'] = false;
                    unset($val ['priority']);
                }
                if (preg_match('/fast food/', strtolower($val ['type']))) {
                    $arrCuisinesList [$key] ['c_type'] = 'favorites';
                } else if (preg_match('/trends/', strtolower($val ['type']))) {
                    $arrCuisinesList [$key] ['c_type'] = 'preferences';
                } else {
                    if (preg_match('/ameri/', strtolower($val ['type']))) {
                        $arrCuisinesList [$key] ['sub_region'] = 'Americas';
                    } else {
                        $arrCuisinesList [$key] ['sub_region'] = $val ['type'];
                    }
                    $arrCuisinesList [$key] ['c_type'] = 'region';
                    unset($arrCuisinesList [$key] ['type']);
                }
            }
        }
        return $arrCuisinesList;
    }

    public static function removeSpecialIcon($str) {
        $order = array("_/_", "__", "_&_", "-");
        $replace = "_";
        return str_replace($order, $replace, preg_replace('/\s+/', '_', strtolower($str)));
    }

}
