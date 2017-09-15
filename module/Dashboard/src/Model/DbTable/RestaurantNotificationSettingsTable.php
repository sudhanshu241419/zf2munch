<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class RestaurantNotificationSettingsTable extends AbstractDbTable {
	protected $_table_name = "restaurant_notification_settings";
	protected $_array_object_prototype = 'Dashboard\Model\RestaurantNotificationSettings';
}