<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;


class PointSourceDetails extends AbstractModel
{

    public $id;
    public $name;
    public $points;
    public $csskey;
    public $created_at;
    protected $_db_table_name = 'Dashboard\Model\DbTable\PointSourceDetailsTable';

    const RESERVE_A_TABLE = 3;
    const ORDER_PLACED_TAKEOUT = 1;
    const RESERVATION_ACCEPT = 17;
    const EARLY_BIRD_SPECIAL_DAYS = 30;
    const DINE_MORE_EARLY_BIRD_POINT = 100;
    const DINE_MORE_RESERVATION_POINT = 100;

    public function getPointSource(array $options = array())
    {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $point = $this->find($options)->toArray();
        return $point;
    }

    public function getPoint($ids)
    {
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            'points',
            'name'
        )
        );
        $where = new Where();
        $where->in('id',$ids);
        $select->where($where);
       // var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $points = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->toArray();
        
        return $points;
    }
     public function getPointsSourceDetail($id=0) {
        $select = new Select ();
        $select->from ( $this->getDbTable ()->getTableName () );
        $select->columns ( array (
                'id',
                'name',
                'points',
                'csskey',
                'created_at',
                'identifier'
        ) );
        $res = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
        $response = array ();
        foreach ( $res as $keys => $values ) {
            $response [] = $values;
        }
        return $response;
    }
    public function getPointsSourceDetailApp($id=0) {
        $select = new Select ();
        $select->from ( $this->getDbTable ()->getTableName () );
        $select->columns ( array (
                'id',
                'name',
                'points',
                'csskey',
                'created_at',
                'identifier'
                
        ) );
        $where = new Where();
        $where->in('points_for',array('bt','ap'));
        $where->equalTo('dstatus','1');
        $select->where($where);
        $select->order('dindex ASC');
        $res = $this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' )->getReadGateway ()->selectWith ( $select )->toArray();
        $responseapp = array ();
        foreach ( $res as $keys => $values ) {
            $responseapp [] = $values;
        }
        return $responseapp;
    }
    public function getPointSourceDetail(array $options = array())
    {
    	$this->getDbTable()->setArrayObjectPrototype('ArrayObject');
    	return $this->find($options)->current();
    }
    public function getPointsOnCssKey($key) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$points = $this->find ( array (
				'columns' => array (
						'id',
						'points' 
				),
				'where' => array (
						'identifier' => $key 
				) 
		) )->current();
        if(is_object($points)){
            $points->getArrayCopy();
        }
		return $points;
	}
        
}