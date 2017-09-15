<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;

class Review extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $source;
	public $date;
	public $reviewer;
	public $reviews;
	public $sentiments;
	public $review_type;
	public $source_url;
	protected $_db_table_name = 'Restaurant\Model\DbTable\ReviewTable';
	protected $_primary_key = 'id';
	const NORMAL = 'N';
	const CONSOLIDATED = 'C';
	const REVIEW_STATUS = 1;
	public function findReviews(array $options = array()) {
		$reviewResult = $this->find ( $options );
		return $reviewResult;
	}
	/**
	 *
	 * @param number $restaurant_id        	
	 * @param self::NORMAL|self::CONSOLIDATED $review_type        	
	 * @return \Zend\Db\Sql\Expression
	 */
	public function getReviewCountExpression($restaurant_id = 0, $review_type = self::NORMAL) {
		$subQ = new Select ();
		$subQ->from ( $this->getDbTable ()->getTableName () );
		$subQ->columns ( array (
				'customer_review_count' => new Expression ( 'COUNT(*)' ) 
		) )->where ( array (
				'restaurant_id' => $restaurant_id,
				'review_type' => $review_type 
		) );
		$reviewCount = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $subQ );
		return $reviewCount;
	}
	public function getReviewCountExpression2($restaurant_id = 0, $review_type = self::NORMAL) {
		$subQ = new Select ();
		$subQ->from ( $this->getDbTable ()->getTableName () );
		$subQ->columns ( array (
				'customer_review_count' => new Expression ( 'COUNT(*)' ) 
		) )->where ( array (
				'restaurant_id' => $restaurant_id,
				'review_type' => $review_type 
		) );
		$subQueryExpression = new \Zend\Db\Sql\Expression ( "(" . str_replace ( '"', '`', $subQ->getSqlString ( $this->getPlatform ( 'READ' ) ) ) . ")" );
		return $subQueryExpression;
	}
	public function getReviews(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$reviews = $this->find ( $options )->toArray ();
		return $reviews;
	}
}