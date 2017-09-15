<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class preOrderItemTable extends AbstractDbTable {
	protected $_table_name = "pre_order_item";
	protected $_array_object_prototype = 'Restaurant\Model\PreOrderItem';
}
