<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantEventTable extends AbstractDbTable {
	protected $_table_name = "restaurant_events";
	protected $_array_object_prototype = 'Restaurant\Model\RestaurantEvent';
}
