<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class LocationTable extends AbstractDbTable {
	protected $_table_name = "restaurants_location";
	protected $_array_object_prototype = 'Restaurant\Model\Location';
}