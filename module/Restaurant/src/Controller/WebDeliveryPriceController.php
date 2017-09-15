<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Postmates;

class WebDeliveryPriceController extends AbstractRestfulController {

    const DELIVERY_QUOTES = 'delivery_quotes';

    public function create($data) {

        if ($data) {
            $postmates = new Postmates();
            $result = $postmates->getDeliveryQuote($data);
            $priceObj = json_decode($result);
            return array('delivery_price' => $priceObj->fee, 'currency' => $priceObj->currency, 'kind' => $priceObj->kind);
        } else {
            throw new \Exception('Pickup and Dropoff address is not valid');
        }
    }

}
