<?php
namespace Home\Controller;
use MCommons\Controller\AbstractRestfulController;
use \Home\Model\RestaurantTag;
use MCommons\StaticOptions;
use Home\Model\City;
class PopularSearchTagController extends AbstractRestfulController {

     public function getList() {
        $cache = StaticOptions::getRedisCache();
        $popularTagsModel = new RestaurantTag();
        $session = $this->getUserSession ();

        $selectedLocation = $session->getUserDetail ( 'selected_location', array () );
        $cityId = $selectedLocation ['city_id'];

        $popularTagsKey = "populartags";
        if( $cache && $cache->hasItem($popularTagsKey)) {
            $cachePopularTags = $cache->getItem($popularTagsKey);
            if(isset($popularTags[$cityId])) {
                return $popularTags[$cityId];
            }
        }
        $cityModel = new City ();
        $cityDetails = $cityModel->cityDetails($cityId);
        $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
            'state_code' => $cityDetails [0] ['state_code']
        ));
        $currentCityDateTime = StaticOptions::getRelativeCityDateTime(array(
            'state_code' => $cityDetails [0] ['state_code']
        ));
        $cityDateTime->add(new \DateInterval("P1D"));
        $cityDateTime->setTime(0, 0, 0);        
        $ttl = $cityDateTime->getTimestamp() - $currentCityDateTime->getTimestamp();        
        $popularTags = $popularTagsModel->getPopularTags($cityId)->toArray();             
        $allTags = array();
         //$tags = array_unique($popularTags);
        $limit = count($popularTags)>15?15:count($popularTags);
        $fifteenPopularTags = array_rand($popularTags,$limit);
        foreach($fifteenPopularTags as $key => $val){
            $allTags[$key] = trim($popularTags[$val]['tag']);
        }
        if($cache) {
            if(!$cache->hasItem($popularTagsKey)) {
                $cache->setItem($popularTagsKey,array());
            }
            $cachePopularTags = $cache->getItem($popularTagsKey); 
            $cachePopularTags[$cityId] = $allTags;
            $cache->setItem($popularTagsKey, $cachePopularTags,$ttl);  
        }
        return $allTags;
    }

}

    
