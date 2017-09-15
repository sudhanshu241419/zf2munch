<?php
namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class CronOrderTable extends AbstractDbTable {
	protected $_table_name = "cron_order";
	protected $_array_object_prototype = 'Dashboard\Model\CronOrder';
}

