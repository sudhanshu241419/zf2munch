<?php
namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserInvitationTable extends AbstractDbTable {
	protected $_table_name = "user_reservation_invitation";
	protected $_array_object_prototype = 'User\Model\UserInvitation';
}