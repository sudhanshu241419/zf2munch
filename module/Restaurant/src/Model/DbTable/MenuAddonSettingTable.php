<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MenuAddonSettingTable extends AbstractDbTable {
	protected $_table_name = "menu_addon_settings";
	protected $_array_object_prototype = 'Restaurant\Model\MenuAddonsSetting';
}