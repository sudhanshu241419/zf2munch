<?php

namespace Bookmark\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class FeedBookmarkTable extends AbstractDbTable {
	protected $_table_name = "feed_bookmark";
	protected $_array_object_prototype = 'User\Model\FeedBookmark';
}