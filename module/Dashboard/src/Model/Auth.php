<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Predicate\Expression;


class Auth extends AbstractModel {
	protected $_db_table_name = 'Dashboard\Model\DbTable\AuthTable';
	public function delete() {
		$dateTime = new \DateTime ();
		$currentTimeStamp = $dateTime->getTimestamp ();
		$delete = new Delete ();
		$delete->from ( $this->getDbTable ()->getTableName () );
		$where = new \Zend\Db\Sql\Where ();
		$where->addPredicate ( new Expression ( "({$currentTimeStamp} - last_update_timestamp) > ttl" ) );
		$delete->where ( $where );
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->delete ( $where );
		return $rowsAffected;
	}

}
