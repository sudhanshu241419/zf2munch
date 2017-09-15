<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserCardTable extends AbstractDbTable {
	protected $_table_name = "user_cards";
	protected $_array_object_prototype = 'User\Model\UserCard';
}