<?php
namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserReservationInvitationTable extends AbstractDbTable {
	protected $_table_name = "user_reservation_invitation";
	protected $_array_object_prototype = 'Dashboard\Model\UserReservationInvitation';
}