<?php

namespace Search\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class LogSearchTable extends AbstractDbTable {
	protected $_table_name = "log_search";
	protected $_array_object_prototype = 'Search\Model\LogSearch';
}