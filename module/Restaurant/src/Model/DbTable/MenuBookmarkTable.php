<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class MenuBookmarkTable extends AbstractDbTable {
	protected $_table_name = "menu_bookmarks";
	protected $_array_object_prototype = 'Restaurant\Model\MenuBookmark';
}