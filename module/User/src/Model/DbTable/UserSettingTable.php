<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserSettingTable extends AbstractDbTable {
	protected $_table_name = "user_settings";
	protected $_array_object_prototype = 'User\Model\UserSetting';
}