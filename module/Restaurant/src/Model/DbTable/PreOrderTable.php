<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class PreOrderTable extends AbstractDbTable {
	protected $_table_name = "pre_order";
	protected $_array_object_prototype = 'Restaurant\Model\PreOrder';
}

