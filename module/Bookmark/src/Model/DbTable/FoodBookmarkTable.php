<?php

namespace Bookmark\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class FoodBookmarkTable extends AbstractDbTable {
	protected $_table_name = "menu_bookmarks";
	protected $_array_object_prototype = 'Bookmark\Model\FoodBookmark';
}