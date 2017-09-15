<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class RestaurantEvent extends AbstractModel {
  public $restaurantEventId;
  public $restaurantEventName;
  public $restaurantEventDesc;
  public $restaurantEventStartDate;
  public $restaurantEventEndDate;
  public $restaurantEventStatus;
  public $restaurant_id;
  public $menu_id;
  public $created_on;
  public $updated_on;
  protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantEventTable';
  public function getRestaurantPromotionEvent($promotionId,$currentDate){
        $select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
            'restaurantEventId',
            'restaurantEventName',
            'restaurantEventDesc',
            'restaurantEventStartDate',
            'restaurantEventEndDate',
            'restaurantEventStatus',
            'restaurant_id', 
		) );
        $select->join ( array ('pre' => 'promotion_restaurant_event'),
            'pre.restaurant_id = restaurant_events.restaurant_id',
             array('restaurantEventId'),
            $select::JOIN_INNER
        );

        $select->join (array('r' => 'restaurants'),
           'r.id=restaurant_events.restaurant_id',
            array(
                'restaurant_name',	
                'price',
				'address',
				'zipcode',
				'city_id',				
				'rest_code',
                'restaurant_image_name'
            ),
            $select::JOIN_INNER
        );
        $select->where ( array (
				'pre.promotionId' => $promotionId,
                'restaurant_events.restaurantEventStatus' => "1",
		) );   
       $select->where->greaterThanOrEqualTo('restaurant_events.restaurantEventEndDate', $currentDate);
       $openNightData = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
       return $openNightData;
    }
}