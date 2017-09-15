<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class DashboardReservationTable extends AbstractDbTable {
	protected $_table_name = "user_reservations";
	protected $_array_object_prototype = 'Dashboard\Model\DashboardReservation';
}