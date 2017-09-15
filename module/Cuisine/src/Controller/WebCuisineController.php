<?php

namespace Cuisine\Controller;

use MCommons\Controller\AbstractRestfulController;
use Cuisine\Model\Cuisine;
use Cuisines\CuisineFunctions;
use MCommons\Caching;

class WebCuisineController extends AbstractRestfulController {
    public function getList() {
        //$this->getServiceLocator()->get('memcached')->setItem('foo', 'bar');
        //echo $this->getServiceLocator()->get('memcached')->getItem('foo');
        //$memCached = new Caching ();
        $memCached = $this->getServiceLocator()->get('memcached');
        $CuisineModel = new Cuisine ();
        $config = $this->getServiceLocator()->get('Config');
        if ($config['constants']['memcache'] && $memCached->getItem('webcuisine')) {
            return $memCached->getItem('webcuisine');
        } else {
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
            $response = CuisineFunctions::getCuisineTypePopularFoodTrendsWeb($allCuisine);
            $memCached->setItem('webcuisine', $response, 0);
            return $response;
        }
    }

}
