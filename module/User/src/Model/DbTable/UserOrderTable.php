<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserOrderTable extends AbstractDbTable {
	protected $_table_name = "user_orders";
	protected $_array_object_prototype = 'User\Model\UserOrder';
}