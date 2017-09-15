<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserTable extends AbstractDbTable {
	protected $_table_name = "users";
	protected $_array_object_prototype = 'Dashboard\Model\User';
}