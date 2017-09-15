<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserActionSettingTable extends AbstractDbTable {
	protected $_table_name = "user_action_settings";
	protected $_array_object_prototype = 'Dashboard\Model\UserActionSetting';
}