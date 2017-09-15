<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class UserCheckin extends AbstractModel {
	public $id;
	public $user_id;
	public $restaurant_id;
	public $message;
	public $created_at;
	
	protected $_db_table_name = 'Dashboard\Model\DbTable\UserCheckinTable';
	protected $_primary_key = 'id';
    
    public function getTotalUsercheckin($userId,$restId)
	{
		$select = new Select();
		$select->from($this->getDbTable()->getTableName());
		$select->columns(array(	
				'total_checkin' => new Expression('COUNT(id)')
		));
		$select->where(array(
				'user_id' => $userId,
                                'restaurant_id' => $restId,
		));
		$totalCheckin = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
		return $totalCheckin->toArray();
	}
}
