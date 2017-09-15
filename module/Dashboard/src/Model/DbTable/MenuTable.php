<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MenuTable extends AbstractDbTable {
	protected $_table_name = "menus";
	protected $_array_object_prototype = 'Dashboard\Model\Menu';
}