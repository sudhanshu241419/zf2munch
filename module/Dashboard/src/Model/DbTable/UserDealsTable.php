<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserDealsTable extends AbstractDbTable {
	protected $_table_name = "user_deals";
	protected $_array_object_prototype = 'Dashboard\Model\UserDeals';
}