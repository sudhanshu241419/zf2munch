<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Menu;
use Restaurant\RestaurantDetailsFunctions;

class WebMenuDealsController extends AbstractRestfulController {

    public function get($id) {
        if (strpos($id, ',') !== false) {
            $id = explode(',', $id);
        }
        $options = array(
            'columns' => array(
                'restaurant_id',
                'id'
            ),
            'where' => array(
                'id' => $id
            )
        );
        $menu = new Menu ();
        $menu->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $menu->find($options)->toArray();
        $menuDeals = array();
        $idFinal = array();
        if (!empty($response)) {
            foreach ($response as $res) {
                $idFinal[] = $res['restaurant_id'];
            }
        }
        $deals = array();
        if (!empty($idFinal)) {
            $idFinal = implode(',', $idFinal);
            $restaurantFunctions = new RestaurantDetailsFunctions();
            $deals = array_values($restaurantFunctions->getDealsForRestaurant($idFinal));
        }
        foreach ($response as $res) {
            $restra = array_filter($deals, function($deal) use ($res) {
                return $deal['restaurant_id'] == $res['restaurant_id'];
            });
            if (count($restra)) {
                $restra = array_pop($restra);
                array_push($menuDeals, array(
                    'title' => $restra['title'],
                    'type' => $restra['type'],
                    'discount' => $restra['discount'],
                    'discount_type' => $restra['discount_type'],
                    'minimum_order_amount' => $restra['minimum_order_amount'],
                    'menu_id' => $res['id']
                ));
            }
        }
        return $menuDeals;
    }

}
