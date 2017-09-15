<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantDetailTable extends AbstractDbTable {
	protected $_table_name = "restaurants_details";
	protected $_array_object_prototype = 'Restaurant\Model\RestaurantDetail';
}