<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class TagsTable extends AbstractDbTable {
	protected $_table_name = "tags";
	protected $_array_object_prototype = 'Restaurant\Model\Tags';
}
