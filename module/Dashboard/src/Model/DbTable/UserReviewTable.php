<?php

namespace Dashboard\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserReviewTable extends AbstractDbTable {
	protected $_table_name = "user_reviews";
	protected $_array_object_prototype = 'Dashboard\Model\UserReview';
}