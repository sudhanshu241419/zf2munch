<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class OrderDetailTable extends AbstractDbTable {
	protected $_table_name = "user_order_details";
	protected $_array_object_prototype = 'Dashboard\Model\OrderDetail';
}