<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;
use Home\Model\Location;
use Home\Model\State;
use MCommons\Caching;

class LocationController extends AbstractRestfulController {

    private $stateModel;

    public function __construct() {
        $this->stateModel = new State ();
        $this->locationModel = new Location ();
    }

    public function getList() {
        try{
        $memCached = $this->getServiceLocator()->get('memcached');
        $allStates = array();
        $config = $this->getServiceLocator()->get('Config');
        if ($config ['constants'] ['memcache'] && $memCached->getItem('location')) {
            return $memCached->getItem('location');
        } else {
            $states = $this->stateModel->getStates()->toArray();
            $allStates = ($states) ? $this->locationModel->refineLocationData($states) : $states;
            $memCached->setItem('location', $allStates, 0);
            return $allStates;
         }
         } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On Location Api');
            throw new \Exception($e->getMessage(),400);
        }
    }

}

/*$states = $this->stateModel->getStates ()->toArray ();
		return ($states) ? $this->locationModel->refineLocationData ( $states ) : $states;*/