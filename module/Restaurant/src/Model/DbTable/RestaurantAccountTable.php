<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantAccountTable extends AbstractDbTable {
	protected $_table_name = "restaurant_accounts";
	protected $_array_object_prototype = 'Restaurant\Model\RestaurantAccounts';
}