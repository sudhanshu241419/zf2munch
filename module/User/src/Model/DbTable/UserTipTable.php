<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserTipTable extends AbstractDbTable {
	protected $_table_name = "user_tips";
	protected $_array_object_prototype = 'User\Model\UserTip';
}