<?php
namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserOrderInvitationTable extends AbstractDbTable {
	protected $_table_name = "user_order_invitation";
	protected $_array_object_prototype = 'User\Model\UserOrderInvitation';
}