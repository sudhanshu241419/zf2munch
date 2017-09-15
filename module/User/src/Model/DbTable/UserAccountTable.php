<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserAccountTable extends AbstractDbTable {
	protected $_table_name = "user_account";
	protected $_array_object_prototype = 'User\Model\UserAccount';
}