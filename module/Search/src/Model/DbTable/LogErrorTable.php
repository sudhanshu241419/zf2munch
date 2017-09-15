<?php

namespace Search\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class LogErrorTable extends AbstractDbTable {
	protected $_table_name = "log_error";
	protected $_array_object_prototype = 'Search\Model\LogError';
}