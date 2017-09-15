<?php
namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class PubnubNotificationTable extends AbstractDbTable {
	protected $_table_name = "pubnub_notification";
	protected $_array_object_prototype = 'Dashboard\Model\UserNotification';
}