<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserAvatarTable extends AbstractDbTable {
	protected $_table_name = "user_avatar";
	protected $_array_object_prototype = 'User\Model\UserAvatar';
}