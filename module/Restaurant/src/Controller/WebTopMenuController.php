<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Menu;

class WebTopMenuController extends AbstractRestfulController {

    private $bookmark_types;

    /*
     * this function will get menu details of restaurant
     */

    public function get($restaurant_id = 0) {

        // Get restaurant menu
        $menuModel = new Menu ();
        $limit = (int) $this->getQueryParams('limit', 20);
        $item = array('item_name');
        $response = $menuModel->getTopTwentyMenues(array(
                    'columns' => array(
                        'restaurant_id' => $restaurant_id,
                        'limit' => $limit
                    )
                        ), $item)->toArray();

        if (!$this->isMobile()) {
            $response = array(
                'menues' => $response
            );
        }

        return $response;
    }

}

//End of class
