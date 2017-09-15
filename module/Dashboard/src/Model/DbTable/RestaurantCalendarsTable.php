<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantCalendarsTable extends AbstractDbTable {
	protected $_table_name = "restaurant_calendars";
	protected $_array_object_prototype = 'Dashboard\Model\RestaurantCalendars';
}
