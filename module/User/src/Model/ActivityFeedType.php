<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class ActivityFeedType extends AbstractModel
{

    public $id;

    public $feed_type;

    public $feed_message;

    public $feed_message_others;

    public $status;

    protected $_db_table_name = 'User\Model\DbTable\ActivityFeedTypeTable';

    protected $_primary_key = 'id';
   
    public function insert($data){
		$writeGateway = $this->getDbTable ()->getWriteGateway ();
		$rowsAffected = $writeGateway->insert ($data);
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		return $lastInsertId;
	}
       
}