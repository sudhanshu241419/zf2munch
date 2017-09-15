<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class ReportAbuseTable extends AbstractDbTable {
	protected $_table_name = "report_abuse_restaurants";
	protected $_array_object_prototype = 'User\Model\ReportAbuse';
}