<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use MCommons\StaticOptions;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;

class DealsCoupons extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $city_id;
	public $type;
	public $deal_for;
	public $title;
	public $description;
	public $fine_print;
	public $price;
	public $discount_type;
	public $discount;
	public $max_daily_quantity;
	public $start_on;
	public $end_date;
	public $expired_on;
	public $created_on;
	public $updated_at;
	public $image;
	public $status;
	public $trend;
	public $sold;
	public $redeemed;
	public $coupon_code;
	public $days;
	public $slots;
    public $menu_id;
    public $user_deals;
    public $minimum_order_amount;
    public $read;
    public $deal_used_type;
	protected $_db_table_name = 'Restaurant\Model\DbTable\DealsCouponsTable';
	protected $_primary_key = 'id';
	const CLOSE = '0';
	const LIVE = '1';
	const PAUSED = '2';
	const PROCESSING = '3';
	const USER_DATE_FORMAT = 'M d, Y';
	public function findDeals($options) {
		$dealsCoupons = $this->find ( $options );
		return $dealsCoupons;
	}
	/**
	 *
	 * @param number $restaurant_id        	
	 * @return array of deals or coupons with their keys
	 */
	public function findDetailedDeals($restaurant_id = 0) {
		$currDateTime = StaticOptions::getRelativeCityDateTime ( array (
				'restaurant_id' => $restaurant_id 
		) );
		$currentDateTime = $currDateTime->format ( 'Y-m-d H:i:s' );
		
		$select = new Select ();
		$select->from ( $this->getDbTable ()->getTableName () );
		
		$where = new Where ();
		$where->equalTo ( 'restaurant_id', $restaurant_id );
		$where->equalTo ( 'status', self::LIVE );
		$where->lessThanOrEqualTo ( 'start_on', $currentDateTime );
		$where->greaterThanOrEqualTo ( 'end_date', $currentDateTime );
		//$where->greaterThan ( 'max_daily_quantity', new Expression ( 'sold' ) );
		
		$select->where ( $where );
		
		$dealsCoupons = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select );
		$dealsCouponsArray = $dealsCoupons->toArray ();
		$response = array ();
		foreach ( $dealsCouponsArray as $keys => $values ) {
			$dataArr = array ();
			$response_data = array ();
			foreach ( $values as $key => $value ) {
				if (is_null ( $value )) {
					$value = '';
				}
				$dataArr [$key] = $value;
			}
			if ($dataArr ['discount_type'] == 'p') {
				$dataArr ['you_save'] = $dataArr ['price'] * $dataArr ['discount'] / 100;
				$dataArr ['net_amount'] = $dataArr ['price'] - $dataArr ['you_save'];
				$dataArr ['discount'] = $dataArr ['discount'] . '%';
			} else {
				$dataArr ['you_save'] = $dataArr ['discount'];
				$dataArr ['net_amount'] = $dataArr ['price'] - $dataArr ['discount'];
			}
			$dataArr ['start_on'] = date ( self::USER_DATE_FORMAT, strtotime ( $dataArr ['start_on'] ) );
			$dataArr ['end_date'] = date ( self::USER_DATE_FORMAT, strtotime ( $dataArr ['end_date'] ) );
			$dataArr ['expired_on'] = date ( self::USER_DATE_FORMAT, strtotime ( $dataArr ['expired_on'] ) );
			$dataArr ['created_on'] = date ( self::USER_DATE_FORMAT, strtotime ( $dataArr ['created_on'] ) );
			$dataArr ['updated_at'] = date ( self::USER_DATE_FORMAT, strtotime ( $dataArr ['updated_at'] ) );
			$response_data ['id'] = $dataArr ['id'];
			if ($dataArr ['type'] == 'deals') {
				$response_data ['is_deal'] = '1';
			} else {
				$response_data ['is_deal'] = '0';
			}
			$response_data ['image'] = $dataArr ['image'];
			$response_data ['title'] = $dataArr ['title'];
			$response_data ['description'] = $dataArr ['description'];
			if ($dataArr ['type'] == 'deals') {
				$response_data ['value'] = $dataArr ['price'];
				$response_data ['discount'] = $dataArr ['discount'];
				$response_data ['saving_amount'] = $dataArr ['you_save'];
				$response_data ['net_amount'] = $dataArr ['net_amount'];
				$response_data ['end_date'] = $dataArr ['end_date'];
				$response_data ['expired_on'] = $dataArr ['expired_on'];
			}
			$response [] = $response_data;
			unset ( $response_data );
			unset ( $dataArr );
		}
		return $response;
	}
	public function addDealsCoupons() {
		$data = $this->toArray ();
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		
		if (! $this->id) {
			$rowsAffected = $writeGateway->insert ( $data );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		// Get the last insert id and update the model accordingly
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		
		if ($rowsAffected >= 1) {
			if (! $this->id) {
				$this->id = $lastInsertId;
			}
			return $this->toArray ();
		}
		return false;
	}
	public function delete() {
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$data = array (
				'status' => 2 
		);
		if ($this->id == 0) {
			throw new \Exception ( "Invalid deals and coupons detail provided", 500 );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		return $rowsAffected;
	}
	public function findDealsCoupons($id = 0) {
		$dealsCoupons = $this->find ( array (
				'where' => array (
						'id' => $id 
				) 
		) )->current ();
		return $dealsCoupons;
	}
	public function updateDealsCoupons($data, $id) {
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->update ( $data, array (
				'id' => $id 
		) );
		if ($rowsAffected >= 1) {
			return $data;
		}
		return false;
	}
	/**
	 * Get Deals/coupons Count In According City
	 * @param unknown $cityId
	 * @param unknown $currentDate
	 * @return \ArrayObject
	 */
	public function getUserCityDealsCouponsCount($cityId,$currentDate){
	    $select = new Select();
	    $select->from($this->getDbTable()
	    		->getTableName());
	    $select->columns(array(
	    
	    		'total_deals' => new Expression('COUNT(id)')
	    ));
	    $where = new Where();
	    $where->equalTo('city_id', $cityId);
	    $where->lessThanOrEqualTo('start_on', $currentDate);
	    $where->greaterThanOrEqualTo('expired_on', $currentDate);
	    $select->where($where);
	    $totalDeals = $this->getDbTable()
	    ->setArrayObjectPrototype('ArrayObject')
	    ->getReadGateway()
	    ->selectWith($select)
	    ->current();
	    return $totalDeals;
	}
        public function getRestaurantsOffers($restId,$restStartDate,$restEndDate) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_deals' => new Expression('COUNT(restaurant_deals_coupons.id)')
        ));
        $select->join(array(
            'ud' => 'user_deals'
                ), 'ud.deal_id = restaurant_deals_coupons.id', array(
                ), $select::JOIN_INNER);
        $where = new Where();
        $where->equalTo('restaurant_deals_coupons.restaurant_id', $restId);
        $where->equalTo('restaurant_deals_coupons.status', '1');
        $where->between('restaurant_deals_coupons.created_on', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $totalDeals = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->current();
        return $totalDeals['total_deals'];
    }
    public function getRestaurantsOffersAvailed($restId,$restStartDate,$restEndDate) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(

        ));
        $select->join(array(
            'ud' => 'user_deals'
                ), 'ud.deal_id = restaurant_deals_coupons.id', array(
                    'total_availed' => new Expression('COUNT(ud.availed)')
                ), $select::JOIN_INNER);
        $where = new Where();
        $where->equalTo('restaurant_deals_coupons.restaurant_id', $restId);
        $where->equalTo('restaurant_deals_coupons.status', '1');
        $where->equalTo('ud.availed', '1');
        $where->between('restaurant_deals_coupons.created_on', $restStartDate, $restEndDate);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $totalAvailed = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select)
                ->current();
        return $totalAvailed['total_availed'];
    }

}