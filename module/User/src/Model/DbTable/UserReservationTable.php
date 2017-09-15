<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserReservationTable extends AbstractDbTable {
	protected $_table_name = "user_reservations";
	protected $_array_object_prototype = 'User\Model\UserReservation';
}