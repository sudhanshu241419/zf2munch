<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class CalendarTable extends AbstractDbTable {
	protected $_table_name = "restaurant_calendars";
	protected $_array_object_prototype = 'Restaurant\Model\Calendar';
}