<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class OrderAddonsTable extends AbstractDbTable {
	protected $_table_name = "user_order_addons";
	protected $_array_object_prototype = 'Dashboard\Model\OrderAddons';
}