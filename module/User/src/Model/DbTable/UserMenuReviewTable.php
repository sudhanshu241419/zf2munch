<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserMenuReviewTable extends AbstractDbTable {
	protected $_table_name = "user_menu_reviews";
	protected $_array_object_prototype = 'User\Model\UserMenuReview';
}