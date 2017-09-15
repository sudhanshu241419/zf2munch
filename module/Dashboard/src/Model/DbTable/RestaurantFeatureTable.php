<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantFeatureTable extends AbstractDbTable {
	protected $_table_name = "restaurant_features";
	protected $_array_object_prototype = 'Dashboard\Model\RestaurantFeature';
}