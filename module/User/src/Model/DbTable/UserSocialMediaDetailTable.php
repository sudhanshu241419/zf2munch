<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserSocialMediaDetailTable extends AbstractDbTable {
	protected $_table_name = "user_social_media_details";
	protected $_array_object_prototype = 'User\Model\UserSocialMediaDetails';
}