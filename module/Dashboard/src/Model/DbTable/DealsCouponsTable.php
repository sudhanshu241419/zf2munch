<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class DealsCouponsTable extends AbstractDbTable {
	protected $_table_name = "restaurant_deals_coupons";
	protected $_array_object_prototype = 'Dashboard\Model\DealsCoupons';
}