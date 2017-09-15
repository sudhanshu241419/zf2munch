<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantReviewTable extends AbstractDbTable {
	protected $_table_name = "restaurant_reviews";
	protected $_array_object_prototype = 'Dashboard\Model\RestaurantReview';
}