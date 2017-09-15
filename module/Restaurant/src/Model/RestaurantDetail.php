<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;

class RestaurantDetail extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $area;
	public $phone_no;
	public $email;
	public $mobile_no;
	public $source_url;
	public $phone_no2;
	public $fax;
	public $accept_cc;
	public $delivery;
	public $facebook_account;
	public $neighborhood;
	public $payment_modes;
	public $price;
	public $specials_offers;
	public $specials_offers_description;
	public $specials_offers_price;
	public $specials_offers_title;
	public $takeout;
	public $images_ambience;
	public $images_menu_item;
	public $images_outside;
	public $images_parking;
	public $delta;
	public $minimum_delivery;
	public $delivery_area;
	public $delivery_time;
	public $small_turnaround_time;
	public $large_turnaround_time;
	public $max_partysize;
	public $video_embed_code;
	public $delivery_charge;
	protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantDetailTable';
	
	/**
	 * Get details of restaurant based on the restaurant ID
	 *
	 * @param number $restaurant_id        	
	 * @return Ambigous <\ArrayObject,false>
	 */
	public function getRestaurantDetail($restaurant_id = 0) {
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		$select->columns ( array (
				'grade',
				'email',
				'grade_description',
				'price',
				'is_chain',
				'accept_cc',
				'delivery',
				'takeout',
				'dining',
				'delivery_charge',
				'phone_no',
				'minimum_delivery',
				'delivery_area',
				'delivery_time',
				'delivery_charge',
				'reservations' 
		) );
		
		$select->join ( array (
				'rs' => 'restaurants' 
		), 'rs.id = restaurants_details.restaurant_id', array (
				'restaurant_name',
				'restaurant_id' => 'id',
				'description',
				'address',
				'zipcode',
				'city_id',
				'state_id',
				'country_id',
				'street',
				'rest_code' 
		), $select::JOIN_INNER );
		$select->where ( array (
				'restaurants_details.restaurant_id' => $restaurant_id 
		) );
		$resDetail = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->current ();
		return $resDetail;
	}
}