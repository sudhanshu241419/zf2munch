<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantTable extends AbstractDbTable {
	protected $_table_name = "restaurants";
	protected $_array_object_prototype = 'Dashboard\Model\Restaurant';
}