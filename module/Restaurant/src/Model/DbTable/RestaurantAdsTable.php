<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantAdsTable extends AbstractDbTable {
	protected $_table_name = "restaurant_ads";
	protected $_array_object_prototype = 'Restaurant\Model\RestaurantAds';
}