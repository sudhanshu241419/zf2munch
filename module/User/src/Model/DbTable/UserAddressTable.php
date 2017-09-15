<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserAddressTable extends AbstractDbTable {
	protected $_table_name = "user_addresses";
	protected $_array_object_prototype = 'User\Model\UserAddress';
}