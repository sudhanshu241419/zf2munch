<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class CuisineTable extends AbstractDbTable {
	protected $_table_name = "restaurant_cuisines";
	protected $_array_object_prototype = 'Restaurant\Model\Cuisine';
}