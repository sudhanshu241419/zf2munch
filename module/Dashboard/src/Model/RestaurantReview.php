<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

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
    CONST POSITIVE_CLASS = 'OkIcon';
    CONST NEGATIVE_CLASS = 'negativeIcon';
    CONST AVERAGE_CLASS = 'avgIcon';
    
    protected $_db_table_name = 'Dashboard\Model\DbTable\RestaurantReviewTable';

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

    public function getDashboardTotalOthersReviews($restaurantId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_review' => new Expression('COUNT(id)')
        ));
        $select->where(array(
            'restaurant_id' => $restaurantId
        ));

        //var_dump($select->getSqlString($this->getPlatform('READ')));

        $totalReview = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $totalReview;
    }

    public function getDashboardRestaurantOtherReviews($options) {
        $rlists = array();
        $sorting = "date DESC";
        if ($options['orderby'] == 'source') {
            $sorting = "source ASC";
        } else if ($options['orderby'] == 'date') {
            $sorting = "date DESC";
        } else if ($options['orderby'] == 'sentiments') {
            $sorting = "sentiments DESC";
        }
        $records = $this->getRestaurantReviews($options, $sorting);
        if (!empty($records)) {
            foreach ($records as $key => $value) {
                $value['date'] = date('M d, Y ', strtotime($value['date']));
                $sentiments = strtolower($value['sentiments']);
                if ($sentiments == 'positive') {
                    $value['sentiments_class'] = self::POSITIVE_CLASS;
                } else if ($sentiments == 'negative') {
                    $value['sentiments_class'] = self::NEGATIVE_CLASS;
                } else {
                    $value['sentiments_class'] = self::AVERAGE_CLASS;
                }
                if (strlen($value['reviews']) > 63)
                    $value['reviews'] = substr($value['reviews'], 0, 63) . ' ...';
                $rlists[$key] = $value;
            }
        }
        $reviews = array_slice($rlists, $options['offset'], $options['limit']);
        return $reviews;
    }
    public function getRestaurantReviews($options, $sorting) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $options['restaurant_id']);
        $select->where($where);
        $select->order($sorting);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $totalReview = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $totalReview;
    }
    public function getRestaurantReviewDetails($id) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('id', $id);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reviews = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        //pr($reviews,1);
        if(!empty($reviews)){
        $reviews['date_readable'] = date("M d, Y",strtotime($reviews['date']));
        $reviews['source_url'] = isset($reviews['source_url']) ? $reviews['source_url'] : '';
        return $reviews ; 
      }else{
          return array();
      }
    }

}
