<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserEatingHabitsTable extends AbstractDbTable {
	protected $_table_name = "user_eating_habits";
	protected $_array_object_prototype = 'User\Model\UserEatingHabits';
}