<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserPointsTable extends AbstractDbTable {
	protected $_table_name = "user_points";
	protected $_array_object_prototype = 'User\Model\UserPoints';
}