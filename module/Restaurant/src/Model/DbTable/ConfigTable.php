<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class ConfigTable extends AbstractDbTable {
	protected $_table_name = "restaurant_config";
	protected $_array_object_prototype = 'Restaurant\Model\Config';
}