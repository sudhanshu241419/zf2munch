<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class StoryTable extends AbstractDbTable {
	protected $_table_name = "restaurant_stories";
	protected $_array_object_prototype = 'Restaurant\Model\Story';
}