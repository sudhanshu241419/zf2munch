<?php

namespace Typeofplace;

class TypeofplaceFunctions {

    public static function featureData($featureData) {
        $data = array();
        if (!empty($featureData)) {
            foreach ($featureData as $f_data) {
                if ($f_data ['feature_type'] == 'Social Presence' || $f_data ['feature_type'] == 'Biz Info')
                    continue;

                $key = strtolower(str_replace(" ", "_", $f_data ['feature_type']));
                $key = str_replace("restaurant_features", "features", $key);

                unset($f_data ['feature_type']);
                $f_data['features'] = ($f_data['features']=='Open 24 hours')?  ucwords($f_data['features']):$f_data['features'];
                //$f_data['features_key'] = ($f_data['features']=='Pub')?"Pub":$f_data['features_key'];
                //$f_data['features'] = ($f_data['features']=='Pub')?"Pub/Bar":$f_data['features'];
                $data [$key] [] = $f_data;
            }
        }

        return $data;
    }

    public static function webFeatureData($featureData) {
        $data = array();
        foreach ($featureData as $f_data) {
            if ($f_data ['feature_type'] == 'Social Presence' || $f_data ['feature_type'] == 'Biz Info')
                continue;

            $key = strtolower(str_replace(" ", "_", $f_data ['feature_type']));
            $key = str_replace("restaurant_features", "features", $key);
            $f_data["c_type"] = $key;
            $f_data["name"] = $f_data['features_key'];
            $f_data['value'] = $f_data['features'];
            unset($f_data ['feature_type']);
            unset($f_data ['features']);
            unset($f_data ['features_key']);
            $data [] = $f_data;
        }
        return $data;
    }

}
