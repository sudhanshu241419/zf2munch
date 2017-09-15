<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class AvatarTypeTable extends AbstractDbTable {
	protected $_table_name = "avatar";
	protected $_array_object_prototype = 'User\Model\Avatar';
}