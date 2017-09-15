<?php
namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use DeliveryProvider;

class WebMunchLoggerController extends AbstractRestfulController {

    public function create($data) {
        $status = 'ordered';
        $restaurantId = 109;
        $restaurant = new Restaurant();
        $deliveryProviderModule = new DeliveryProvider($status, $restaurantId, $restaurant);
        $deliveryProviderModule->sendMailsToServiceProvider($data);
        return array(true);
    }

}

