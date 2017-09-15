<?php
namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserFriendsInvitationTable extends AbstractDbTable {
	protected $_table_name = "user_invitations";
	protected $_array_object_prototype = 'User\Model\UserInvitation';
}