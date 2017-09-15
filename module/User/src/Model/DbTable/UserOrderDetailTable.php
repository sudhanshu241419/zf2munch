<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserOrderDetailTable extends AbstractDbTable {
	protected $_table_name = "user_order_details";
	protected $_array_object_prototype = 'User\Model\UserOrderDetail';
}