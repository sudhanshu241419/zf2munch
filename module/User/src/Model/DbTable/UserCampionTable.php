<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserCampionTable extends AbstractDbTable {
	protected $_table_name = "user_campions";
	protected $_array_object_prototype = 'User\Model\UserCampions';
}