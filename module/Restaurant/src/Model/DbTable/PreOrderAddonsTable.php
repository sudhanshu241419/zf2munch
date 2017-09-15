<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class PreOrderAddonsTable extends AbstractDbTable {
	protected $_table_name = "pre_order_addons";
	protected $_array_object_prototype = 'Restaurant\Model\PreOrderAddons';
}
