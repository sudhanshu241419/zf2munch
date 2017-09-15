<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MenuPriceTable extends AbstractDbTable {
	protected $_table_name = "menu_prices";
	protected $_array_object_prototype = 'Restaurant\Model\MenuPrices';
}
