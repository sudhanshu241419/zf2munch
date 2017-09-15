<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MenuBookmarkTable extends AbstractDbTable {
	protected $_table_name = "menu_bookmarks";
	protected $_array_object_prototype = 'Dashboard\Model\MenuBookmark';
}