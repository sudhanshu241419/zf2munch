<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class DashboardOrderTable extends AbstractDbTable {
	protected $_table_name = "user_orders";
	protected $_array_object_prototype = 'Dashboard\Model\DashboardOrder';
}

