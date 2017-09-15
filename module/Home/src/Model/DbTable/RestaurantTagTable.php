<?php

namespace Home\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantTagTable extends AbstractDbTable {
	protected $_table_name = "restaurant_tags";
	protected $_array_object_prototype = 'Home\Model\RestaurantTag';
}