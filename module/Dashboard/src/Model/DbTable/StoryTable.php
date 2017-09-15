<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class StoryTable extends AbstractDbTable {
	protected $_table_name = "restaurant_stories";
	protected $_array_object_prototype = 'Dashboard\Model\Story';
}