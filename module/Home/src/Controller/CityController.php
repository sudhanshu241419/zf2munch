<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;
use Home\Model\City;

class CityController extends AbstractRestfulController {

    
    public function getList() {
        //$memCached = new Caching ();
        $cityModel = new City();
        $allCities = array();
        $memCached = $this->getServiceLocator()->get('memcached');
        $config = $this->getServiceLocator()->get('Config');
        if ($config ['constants'] ['memcache'] && $memCached->getItem('cities1')) {
            return $memCached->getItem('cities1');
        } else {
            $cities = $cityModel->getCityDetailsforSeoModule();
            $allCities = $cities;
            $memCached->setItem('cities1', $allCities, 0);
            return $allCities;
    }
    }

}