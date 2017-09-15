<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MenuAddonTable extends AbstractDbTable {
	protected $_table_name = "menu_addons";
	protected $_array_object_prototype = 'Restaurant\Model\MenuAddons';
}