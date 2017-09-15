<?php

namespace Search\Model;

use MCommons\Model\AbstractModel;

class AbandonedCart extends AbstractModel {

    public $cart_data;
    public $exception;
    public $origin;
    public $happened_at;
    public $restaurant_id;
    
    protected $_db_table_name = 'Search\Model\DbTable\AbandonedCartTable';
    protected $_primary_key = 'id';

    /**
     * Maintaine failed order/reservation cart data to db
     * @param array $data having cart data, exception and origin
     * @return boolean
     */
    public function saveAbandonedCart($data) {
        $this->getDbTable()->getWriteGateway()->insert($data);
        return true;
    }
    public function getAbandonedCart($restId) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $data = $this->find(array(
                    'columns' => array('id',
                        'cart_data',
                        'exception',
                        'origin',
                        'created_at'),
                    'where' => array('restaurant_id' => $restId)
                ))->toArray();
        return $data;
    }

}
