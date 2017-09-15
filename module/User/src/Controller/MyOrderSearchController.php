<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Zend\Db\Sql\Predicate\Expression;

class MyOrderSearchController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function get($id) {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $userId = $session->getUserId();
        if (!$isLoggedIn) {
            throw new Exception('Ivalid user');
        }
        $q = $this->getQueryParams('q');
        if (strlen($q) < 3) {
            throw new \Exception('You need to enter atleast 3 characters');
        }
        $restaurantModel = new Restaurant ();
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'uo' => 'user_orders'
            ),
            'on' => new Expression("restaurants.id = uo.restaurant_id AND uo.user_id=" . $userId),
            'columns' => array(
                'id',
                'delivery_date' => 'delivery_time',
                'total_amount',
                'status',
                'order_date' => 'created_at',
                'order_type1',
                'order_type',
                'is_reviewed',
                'review_id'
            ),
            'type' => 'inner'
        );

        $options = array(
            'columns' => array(
                'restaurant_id' => 'id',
                'restaurant_name',
                'is_restaurant_exists' => new Expression('if(inactive = 1 or closed = 1,"No","Yes")'),
            ),
            'like' => array(
                'field' => 'restaurant_name',
                'like' => '%' . $q . '%'
            ),
            'where' => new Expression('(delivery = 1 OR takeout = 1)'),
            'joins' => $joins,
            'limit' => 10,
            'order' => array(
                'uo.created_at' => 'desc'
            )
        );
        $response = $restaurantModel->find($options)->toArray();

        $orderDetailModel = new \User\Model\UserOrderDetail ();
        $orderDetailModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        foreach ($response as $key => $ord) {
            $itemDetailOption = array(
                'columns' => array(
                    'order_item_id' => 'id',
                    'item_name' => 'item',
                    'item_qty' => 'quantity'
                ),
                'where' => array('user_order_id' => $ord['id'])
            );
            $itemDetails = $orderDetailModel->find($itemDetailOption)->toArray();
            $response[$key]['is_reviewed'] = (int)$response[$key]['is_reviewed'];
            $response[$key]['review_id'] = (int)$response[$key]['review_id'];
            $response[$key]['item_list'] = $itemDetails;
        }

        return $response;
    }

}
