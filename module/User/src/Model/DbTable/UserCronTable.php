<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserCronTable extends AbstractDbTable {
	protected $_table_name = "cron_order";
	protected $_array_object_prototype = 'User\Model\UserCron';
}