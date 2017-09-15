<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Config;
use MCommons\StaticOptions;

class WebRestaurantDomainController extends AbstractRestfulController {

    public function get($id) {
        if (isset($id)) {
            $cacheManager = StaticOptions::getRedisCache();
            $cacheKey = 'domain_key_'.$id;
            if($cacheManager) {
                $defaultTTL = $cacheManager->getOptions()->getTtl();
                $cacheManager->getOptions()->setTtl(86400);
                if($cacheManager->hasItem($cacheKey)) {
                    $cacheManager->getOptions()->setTtl($defaultTTL);
                    return $cacheManager->getItem($cacheKey);
                }
            }

            $config = new Config();
            $config->getDbTable()->setArrayObjectPrototype('ArrayObject');
            $joins = array();
            $joins [] = array(
                'name' => array(
                    'rest' => 'restaurants'
                ),
                'on' => 'restaurant_config.restaurant_id = rest.id',
                'columns' => array(
                    'restaurant_name',
                ),
                'type' => 'inner'
            );

            $options = array(
                
                'where' => array(
                    'restaurant_config.hostname' => $id,
                ),
                'joins' => $joins
            );
            $response = $config->find($options)->toArray();
            if ($response) {
                $response = $response[0];
            } else {
                $response = array('restaurant_id' => false, 'gakey' => '', 'gadomain' => '', 'restaurant_name' => '');
            }
            if($cacheManager) {
                $cacheManager->setItem($cacheKey,$response);
                $cacheManager->getOptions()->setTtl($defaultTTL);
            }

            return $response;
        }
    }
}
