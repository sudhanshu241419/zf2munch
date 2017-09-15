<?php
namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserFriendsTable extends AbstractDbTable {
	protected $_table_name = "user_friends";
	protected $_array_object_prototype = 'User\Model\UserFriends';
}