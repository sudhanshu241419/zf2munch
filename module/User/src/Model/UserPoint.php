<?php
namespace User\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use MCommons\StaticOptions;
use Zend\Db\Sql\Expression;

class UserPoint extends AbstractModel
{

    public $id;

    public $user_id;

    public $point_source;

    public $points;

    public $created_at;

    public $status;

    public $points_descriptions;

    public $ref_id;
    
    public $redeemPoint;
    
    public $promotionId=NULL;
    
    public $restaurant_id = 0;
    
    

    protected $_db_table_name = 'User\Model\DbTable\UserPointTable';

    protected $_primary_key = 'id';
    
    public function getUsersPointsDetailList($options = array(),$activityDate=false)
    {
        if (is_numeric($options['limit'])) {
            $limit = $options['limit'];
        }
        $orderBy = array(
            //'point_source ASC ',
            //'created_at DESC'
            'id DESC'
        );
        if ($options['orderby'] == 'activity_date') {
            $orderBy = 'id DESC';
        } elseif ($options['orderby'] == 'activity_type') {
            $orderBy = 'point_source ASC';
        } elseif ($options['orderby'] == 'points') {
            $orderBy = 'points ASC';
        }
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            'id',
            'user_id',
            'point_source',
            'points',            
            'redeemPoint',
            'promotionId',
            'status',
            'ref_id',
            'points_descriptions',
            'status',
            'created_at',
            'restaurant_id'
        ));
        $where = new Where();
        $where->equalTo('user_id', $options['userId']);
        $where->equalTo('status', 1);
        if($activityDate['fromDate'] && $activityDate['toDate']){
            $where->between('user_points.created_at', $activityDate['fromDate'], $activityDate['toDate']);
        }elseif($activityDate['fromDate']){
            $where->greaterThanOrEqualTo('user_points.created_at',$activityDate['fromDate']);
        }elseif($activityDate['toDate']){
            $where->lessThanOrEqualTo('user_points.created_at', $activityDate['toDate']);
        }
        $select->where($where);
        $select->join(array(
            'ps' => 'point_source_detail'
        ), 'ps.id = user_points.point_source', array(
            'csskey'
        ), $select::JOIN_LEFT);
        $select->order($orderBy);
        //var_dump($select->getSqlString($this->getPlatform('READ')));
        $result = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->toArray();
        $archievePoints = array_slice($result, $options['offset'], $options['limit']);
        return $archievePoints;
    }
    
    
     public function getUsersPointsDetailListNew($options = array(),$activityDate=false)
    {
        if (is_numeric($options['limit'])) {
            $limit = $options['limit'];
        }
        $orderBy = array(
            //'point_source ASC ',
            //'created_at DESC'
            'id DESC'
        );
        if ($options['orderby'] == 'activity_date') {
            $orderBy = 'id DESC';
        } elseif ($options['orderby'] == 'activity_type') {
            $orderBy = 'point_source ASC';
        } elseif ($options['orderby'] == 'points') {
            $orderBy = 'points ASC';
        }
        
        #################################
//        $sub = new Select('comment_vote');
//        $sub->columns(array('negativeVote' => new \Zend\Db\Sql\Expression('COUNT(comment_vote.id)')), FALSE)->where(array('vote' => -1 , 'comment_vote.commentId' => 'comment.id'));
//        $subquery = new \Zend\Db\Sql\Expression("({$sub->getSqlString()})");
//        $predicate = new \Zend\Db\Sql\Predicate\Expression("({$sub->getSqlString()})");
//
//
//        $sql = new Sql($this->adapter);
//        $select = $sql->select()->from('comment');
//        $select->columns(array('commentId','comment', 'nagetiveVoteCount' => $subquery));
//        echo $select->getSqlString();
        
        #################################
        $sub = new Select();
        $sub->from($this->getDbTable()
            ->getTableName());
        $sub->columns(array(
                'id',
                'user_id',
                'point_source',
                'points',            
                'redeemPoint',  
                'balance'=>new Expression('@csum := ( @csum + points ) - redeemPoint'),
                'status',
                'ref_id',
                'points_descriptions',
                'status',
                'created_at'                
                ));
        $where = new Where();
        $where->equalTo('user_id', $options['userId']);
        $where->equalTo('status', 1);
        if($activityDate){
            $where->between('created_at', $activityDate['fromDate'], $activityDate['toDate']);
        }
        $sub->where($where);
         var_dump($sub->getSqlString($this->getPlatform('READ')));
         die;
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns($subquery);
//        $where = new Where();
//        $where->equalTo('user_id', $options['userId']);
//        $where->equalTo('status', 1);
//        if($activityDate){
//            $where->between('created_at', $activityDate['fromDate'], $activityDate['toDate']);
//        }
//        $select->where($where);
        $select->join(array(
            'ps' => 'point_source_detail'
        ), 'ps.id = user_points.point_source', array(
            'csskey'
        ), $select::JOIN_INNER);
        $select->order($orderBy);
       
        $result = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->toArray();
        
        pr($result,1);
        $archievePoints = array_slice($result, $options['offset'], $options['limit']);
        return $archievePoints;
    }

    public function getUserTotalArchiveCount($userId)
    {
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            
            'total_count' => new Expression('COUNT(id)')
        ));
        $where = new Where();
        $where->equalTo('user_id', $userId);
        $where->equalTo('status', 1);
        
        $select->where($where);
        $total = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->current();
        return $total;
    }
    public function updateUserRef($userId,$pointSorce,$refId,$status){
        $data = array(
        		'status' => $status
        );
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $dataUpdated = $writeGateway->update($data, array(
        		'user_id' => $userId,
                'point_source' => $pointSorce,
                'ref_id' => $refId
        ));
        if ($dataUpdated == 0) {
        	return array('error'=>'Refrence detail not found');
        } else {
        	return array('success' => 'true');
        }
    }
    public function getUserPointDetail(array $options = array())
    {
    	$this->getDbTable()->setArrayObjectPrototype('ArrayObject');
    	return $this->find($options)->current();
    }
    public function updateAttributes($data)
    {
    	$writeGateway = $this->getDbTable()->getWriteGateway();
    	$rowsAffected = $writeGateway->update($data, array(
    			'id' => $this->id
    	));
    	return true;
    }
    public function createPointDetail($data){
    		$writeGateway = $this->getDbTable ()->getWriteGateway ();
    		$rowsAffected = $writeGateway->insert ( $data );
    		
    		if($rowsAffected){
    			return true;
    		}
   	}
   	public function getUserTotalArchiveCountNew($userId)
   	{
   		$select = new Select();
   		$select->from($this->getDbTable()
   				->getTableName());
   		/* $select->columns(array(
   	
   				'total_count' => new Expression('COUNT(id)')
   		)); */
   		$where = new Where();
   		$where->equalTo('user_points.user_id', $userId);
   		$where->equalTo('user_points.status', 1);
   		$select->join(array(
   				'ps' => 'point_source_detail'
   		), 'ps.id = user_points.point_source', array(
   				'csskey'
   		), $select::JOIN_INNER);
   		$select->where($where);
//    		/var_dump($select->getSqlString($this->getPlatform('READ')));die;
   		$total = $this->getDbTable()
   		->setArrayObjectPrototype('ArrayObject')
   		->getReadGateway()
   		->selectWith($select)
   		->toArray();
   		return $total;
   	}
   	public function getAllUserInvitation(array $options = array())
   	{
   		$this->getDbTable()->setArrayObjectPrototype('ArrayObject');
   		$UserInvitation = $this->find($options)->toArray();
   		return $UserInvitation;
   	}
   	public function updateAcceptedInvition($reservationId)
   	{
   		$data = "";
   		$userModel = new User();
   		$allInvitation = $this->getAllUserInvitation(array(
   				'columns' => array(
   						'id',
   						'status'
   				),
   				'where' => array(
   						'ref_id' => $reservationId,
   						'status' => 1
   				)
   		));
   		if ($allInvitation) {
   			foreach ($allInvitation as $key => $value) {
   				foreach ($value as $id) {
   					$writeGateway = $this->getDbTable()->getWriteGateway();
   					$rowsAffected = $writeGateway->update(array(
   							'status' => 2
   					), array(
   							'id' => $id
   					));
   				}
   			}
   		}
   		return count($allInvitation);
   	}
        
        public function delete(){
          
           $writeGateway = $this->getDbTable ()->getWriteGateway ();
	   $rowsAffected = $writeGateway->delete ( array (
		'ref_id' => $this->ref_id,
                'user_id'=>$this->user_id,
                'point_source'=>$this->point_source
                
            ) );
            return $rowsAffected;
	}
    
    public function countUserPoints($user_id)
    {
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array(
            'points'=>new Expression('sum(points)'),
            'redeemed_points'=> new Expression('sum(redeemPoint)'),
        ));
        $where = new Where();
        $where->equalTo('user_id', $user_id);
        $where->equalTo('status', 1);
        $select->where($where);
        //pr($select->getSqlString($this->getPlatform('READ')),true);
        $userPoints = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select);
        $uPointS=$userPoints->toArray();
        $uPointS[0]['points']=$uPointS[0]['points']>0?$uPointS[0]['points']:"000";
        return $uPointS;
    }
    public function updatePointDetail($data)
    {
        $writeGateway = $this->getDbTable()->getWriteGateway();
    	$rowsAffected = $writeGateway->update($data, array(
    			'ref_id' => $this->ref_id
    	));
    	return true;
    }
}