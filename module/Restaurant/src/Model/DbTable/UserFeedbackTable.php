<?php

namespace Restaurant\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserFeedbackTable extends AbstractDbTable {
	protected $_table_name = "user_feedback";
	protected $_array_object_prototype = 'Restaurant\Model\UserFeedback';
}