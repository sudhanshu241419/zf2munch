<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserOrderDetail;

class WebUnreviewedOrderDetailsController extends AbstractRestfulController {

    public function get($id) {
        $q = $this->getQueryParams('q');
        $userId = $this->getUserSession()->user_id;
        $options = array(
            'columns' => array(
                'item',
                'item_id'
            ),
            'where' => array(
                'status' => 1,
                'user_order_id' => $q
            )
        );
        $userOrderModel = new UserOrderDetail();
        $userOrderModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $userOrderModel->find($options)->toArray();
        return $response;
    }

}
