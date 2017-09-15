<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class SweepstakesCampaigns extends AbstractModel {
	public $id;
	public $name;
	public $start_on;
	public $end_date;
	public $status;
	public $rest_id;
    public $currentDateTime;
    public $campaignsStatus;
		
    protected $_db_table_name = 'Restaurant\Model\DbTable\SweepstakesCampaignsTable';
	protected $_primary_key = 'id';
	
	public function findCampaigns() {        
		$select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            'rest_id',
            'start_on',
            'end_date'
        ));
       
        $where = new Where();
        $where->like('rest_id', '%'.$this->rest_id.'%');//('rest_id', $this->rest_id);
        $where->equalTo('status', 1);
        $where->greaterThanOrEqualTo('end_date', $this->currentDateTime);       
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));
        $campaignsData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        
        if($campaignsData){
            $this->campaignsStatus = true;
        }else{
            $this->campaignsStatus = false;
        }
        return $campaignsData;
	}
   
}