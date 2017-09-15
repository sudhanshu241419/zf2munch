<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class EmailSubscriptionTable extends AbstractDbTable {
	protected $_table_name = "email_subscription";
	protected $_array_object_prototype = 'User\Model\EmailSubscription';
}