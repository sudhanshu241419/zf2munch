<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class EmailSentTable extends AbstractDbTable {
	protected $_table_name = "restaurant_email_sent";
	protected $_array_object_prototype = 'Restaurant\Model\EmailSent';
}
