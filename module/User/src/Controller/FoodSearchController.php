<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Menu;

class FoodSearchController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function get($id) {
        $q = $this->getQueryParams('q',false);
        $restId = $this->getQueryParams('rest_id',false);
        $ignoreIds = $this->getQueryParams('selected_item_ids', 'false');
        if(!$restId){
             throw new \Exception('Restaurant id is required');
        }
        if (strlen($q) < 3) {
            throw new \Exception('You need to enter atleast 3 characters');
        }
        $ignoreIds = $ignoreIds == null ? 'false' : $ignoreIds;

        $menuModel = new Menu ();
        $menuModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'item_id' => 'id',
                'item' => 'item_name'
            ),
            'like' => array(
                'field' => 'item_name',
                'like' => '%' . $q . '%'
            ),
            //comment the upper line and vice versa
            'where' => new \Zend\Db\Sql\Predicate\Expression('restaurant_id = ' . $restId . ' AND id NOT IN (' . $ignoreIds . ')'),
            'limit' => 5
        );
        if (empty($restId)) {
            $options = array(
                'columns' => array(
                    'item_id' => 'id',
                    'item' => 'item_name'
                ),
                'like' => array(
                    'field' => 'item_name',
                    'like' => '%' . $q . '%'
                ),
                'limit' => 4
            );
        }
        $response = $menuModel->find($options)->toArray();
        return $response;
    }

}
