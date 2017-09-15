<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class ActivityFeedTable extends AbstractDbTable {
	protected $_table_name = "activity_feed";
	protected $_array_object_prototype = 'Dashboard\Model\ActivityFeed';
}