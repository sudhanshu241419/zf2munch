<?php

namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class FeedCommentTable extends AbstractDbTable {
	protected $_table_name = "feed_comment";
	protected $_array_object_prototype = 'User\Model\FeedComment';
}