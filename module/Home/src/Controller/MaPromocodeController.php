<?php

namespace Home\Controller;

use MCommons\Controller\AbstractRestfulController;

class MaPromocodeController extends AbstractRestfulController {

    public function getList() {
        $restaurantId = $this->getQueryParams("restaurantid",false);
        $promocode = $this->getQueryParams("promocode",false);
        $order_amount = $this->getQueryParams("orderamt",0);
        $userFunctions = new \User\UserFunctions();
        return $userFunctions->getPromocode($promocode,$restaurantId,$order_amount);       
    }

}
