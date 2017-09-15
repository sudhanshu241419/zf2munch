<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantSeatsTable extends AbstractDbTable {
	protected $_table_name = "restaurant_seats";
	protected $_array_object_prototype = 'Dashboard\Model\RestaurantSeats';
}
