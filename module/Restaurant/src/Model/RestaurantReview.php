<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Expression;

class RestaurantReview extends AbstractModel {

    public $id;
    public $restaurant_id;
    public $source;
    public $date;
    public $reviewer;
    public $reviews;
    public $sentiments;
    public $review_type;
    public $source_url;
    public $is_read;
    protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantReviewTable';

    public function getRestaurantReviewCount($restaurant_id = 0) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $res = $this->find(array(
            'columns' => array(
                'total_count' => new Expression('COUNT(restaurant_id)'),
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,
                'status' => 1,
                'review_type' => 'N'
            )
            ));
        return $res->current();
    }

    public function restaurantTotalReview($restaurant_id = 0) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $res = $this->find(array(
            'columns' => array(
                'total_count' => new Expression('COUNT(restaurant_id)'),
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,
            )
            ));
        return $res->toArray()[0];
    }

    public function getRestaurantPositiveReview($restId = 0) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $res = $this->find(
            array(
                'columns' => array(
                    'reviewer' => 'reviewer',
                    'reviews' => 'reviews',
                ),
                'where' => array(
                    'restaurant_id' => $restId,
                    'sentiments' => 'Positive',
                    'status' => 1
                ),
                'limit' => 10,
            )
        );
        return $res->toArray();
    }

}
