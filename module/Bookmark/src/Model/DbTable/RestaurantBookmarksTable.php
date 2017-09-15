<?php

namespace Bookmark\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantBookmarksTable extends AbstractDbTable {
	protected $_table_name = "restaurant_bookmarks";
	protected $_array_object_prototype = 'Bookmark\Model\RestaurantBookmark';
}
