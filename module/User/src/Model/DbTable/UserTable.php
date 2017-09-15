<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserTable extends AbstractDbTable {
	protected $_table_name = "users";
	protected $_array_object_prototype = 'User\Model\User';
}