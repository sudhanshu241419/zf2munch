<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserSettingsTable extends AbstractDbTable {
	protected $_table_name = "user_settings";
	protected $_array_object_prototype = 'Dashboard\Model\UserSettings';
}