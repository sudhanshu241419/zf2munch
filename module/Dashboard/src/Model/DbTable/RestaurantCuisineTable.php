<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantCuisineTable extends AbstractDbTable {
	protected $_table_name = "restaurant_cuisines";
	protected $_array_object_prototype = 'Dashboard\Model\RestaurantCuisine';
}