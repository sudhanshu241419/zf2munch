<?php
namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class PubnubDashboardNotificationTable extends AbstractDbTable {
	protected $_table_name = "pubnub_dashboard_notification";
	protected $_array_object_prototype = 'Dashboard\Model\UserDashboardNotification';
}