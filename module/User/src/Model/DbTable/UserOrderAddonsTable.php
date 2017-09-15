<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserOrderAddonsTable extends AbstractDbTable {
	protected $_table_name = "user_order_addons";
	protected $_array_object_prototype = 'User\Model\UserOrderAddons';
}