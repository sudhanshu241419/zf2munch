<?php

namespace Search\Model;

use MCommons\Model\AbstractModel;

class ResDealsCoupons extends AbstractModel {

    public $user_id;
    public $deal_id;
    public $date;
    protected $_db_table_name = 'Search\Model\DbTable\ResDealsCouponsTable';
    protected $_primary_key = 'id';

    public function getResUserDeals($res_id) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        
        $where =  new \Zend\Db\Sql\Where();
        $where->equalTo('restaurant_id', $res_id);
        $where->equalTo('user_deals', 1);
        $where->equalTo('status', 1);
        $where->equalTo('type', 'deals');
        $where->greaterThan('end_date', date('Y-m-d'));
        
        $options = array(
            'columns' => array(
                    'title',
                    'type',
                    'start_on',
                    'end_date',
                    'discount',
                    'discount_type',
                    'minimum_order_amount',
                    'days',
                    'slots',
                    'description',
                    'deal_for'
            ),
            'where' => $where
        );
        
        $deals = [];
        foreach ($this->find($options)->toArray() as $i => $row) {
            $deals[] = array(
                    'title' => $row['title'],
                    'type' => $row['type'],
                    'start_on' => $row['start_on'],
                    'end_date' => $row['end_date'],
                    'discount' => $row['discount'],
                    'discount_type' => $row['discount_type'],
                    'minimum_order_amount' => $row['minimum_order_amount'],
                    'days' => $row['days'],
                    'slots' => $row['slots'],
                    'description' => $row['description'],
                    'deal_for' => $row['deal_for']
                );
        }
        return $deals;
    }

}
