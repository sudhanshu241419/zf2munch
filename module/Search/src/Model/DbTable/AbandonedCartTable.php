<?php

namespace Search\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class AbandonedCartTable extends AbstractDbTable {
	protected $_table_name = "abandoned_cart";
	protected $_array_object_prototype = 'Search\Model\AbandonedCart';
}