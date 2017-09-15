<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserCheckinTable extends AbstractDbTable {
	protected $_table_name = "user_checkin";
	protected $_array_object_prototype = 'User\Model\UserCheckin';
}