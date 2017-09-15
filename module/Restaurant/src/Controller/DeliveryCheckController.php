<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Search\CityDeliveryCheck;

class DeliveryCheckController extends AbstractRestfulController {

    public function get($id) {
        $response = array();
        $res_id = $id;
        $lat = $this->getQueryParams ('lat');
        $lng = $this->getQueryParams ('lng');
        if(!$lat || ! $lng){
            $response['can_deliver'] = FALSE;
        } else {
            $response['can_deliver'] = CityDeliveryCheck::canDeliver($res_id, $lat, $lng);
        }
        return $response;
    }

}
