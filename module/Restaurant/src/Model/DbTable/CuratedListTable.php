<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class CuratedListTable extends AbstractDbTable {
	protected $_table_name = "restaurant_curated_list";
	protected $_array_object_prototype = 'Restaurant\Model\CuratedList';
}