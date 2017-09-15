<?php
namespace User\Model\DbTable;

use MCommons\Model\DbTable\AbstractDbTable;

class UserImportedContactListTable extends AbstractDbTable {
	protected $_table_name = "user_imported_contactlist";
	protected $_array_object_prototype = 'User\Model\UserImportedContactList';
}