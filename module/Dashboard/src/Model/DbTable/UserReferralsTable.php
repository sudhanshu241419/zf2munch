<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserReferralsTable extends AbstractDbTable {
	protected $_table_name = "user_referrals";
	protected $_array_object_prototype = 'Dashboard\Model\UserReferrals';
}