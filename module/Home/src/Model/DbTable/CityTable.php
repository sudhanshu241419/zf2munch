<?php

namespace Home\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class CityTable extends AbstractDbTable {
	protected $_table_name = "cities";
	protected $_array_object_prototype = 'Home\Model\City';
}