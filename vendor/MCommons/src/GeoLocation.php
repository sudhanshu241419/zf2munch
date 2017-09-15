<?php
namespace MCommons;

use Home\Model\City;

class GeoLocation
{

    public static $_ip_address;

    public function __construct()
    {
        self::$_ip_address = self::get_real_ip();
    }

    public function get_real_ip()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            return $_SERVER["HTTP_FORWARDED"];
        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }

    public static function get_user_loc_by_ip_address()
    {
        // api url to get user location based on IP Address.
        $user_loc_data = array();
        $user_loc_data['latitude'] = '';
        $user_loc_data['longitude'] = '';
        $user_loc_data['country'] = '';
        $user_loc_data['state'] = '';
        $user_loc_data['state_code'] = '';
        $user_loc_data['city'] = '';
        
        $location_data = @geoip_record_by_name(self::$_ip_address);
        if ($location_data && ! empty($location_data) && ! empty($location_data['city']) && ! empty($location_data['state_code'])) {
            $user_loc_data['latitude'] = $location_data['latitude'];
            $user_loc_data['longitude'] = $location_data['longitude'];
            $user_loc_data['country'] = $location_data['country_name'];
            $user_loc_data['state'] = $location_data['region'];
            $user_loc_data['state_code'] = $location_data['region'];
            $user_loc_data['city'] = $location_data['city'];
        } else {
            return false;
            /*
             * $query_string = "?GetLocation&template=php3.txt&IpAddress=" . self::$_ip_address; $file_path = GOOGLE_MAP_API . $query_string; $geo_data = @get_meta_tags($file_path); $geo_data = (object) $geo_data; $user_loc_data['latitude'] = isset($geo_data->latitude) ? $geo_data->latitude : ''; $user_loc_data['longitude'] = isset($geo_data->longitude) ? $geo_data->longitude : ''; $user_loc_data['country'] = isset($geo_data->country) ? $geo_data->country : ''; $user_loc_data['state'] = isset($geo_data->region) ? $geo_data->region : ''; $user_loc_data['state_code'] = isset($geo_data->regioncode) ? $geo_data->regioncode : ''; $user_loc_data['city'] = isset($geo_data->city) ? $geo_data->city : '';
             */
            // $user_loc_data['city_id'] = isset($geo_data->cityid) ? $geo_data->cityid : '';
        }
        $cityModel = new City();
        $cityData = $cityModel->fetchCityIdByOptions(array(
            'cities.city_name' => $user_loc_data['city'],
            'cities.state_code' => $user_loc_data['state_code']
        ));
        if (! empty($cityData)) {
            $user_loc_data['city_id'] = $cityData['city_id'];
            $user_loc_data['city_code'] = "";
        }
        return $user_loc_data;
    }
}