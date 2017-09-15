<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserReviewImageTable extends AbstractDbTable {
	protected $_table_name = "user_review_images";
	protected $_array_object_prototype = 'User\Model\UserReviewImage';
}