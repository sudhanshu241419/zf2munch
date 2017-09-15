<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserPromocodesTable extends AbstractDbTable {
	protected $_table_name = "user_promocodes";
	protected $_array_object_prototype = 'Restaurant\Model\UserPromocodes';
}