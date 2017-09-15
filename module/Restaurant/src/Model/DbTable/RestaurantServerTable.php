<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantServerTable extends AbstractDbTable {
	protected $_table_name = "restaurant_servers";
	protected $_array_object_prototype = 'Restaurant\Model\RestaurantServer';
}