<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate\Expression;

class UserReferrals extends AbstractModel {

    public $user_id;
    public $inviter_id;
    public $order_placed;
    public $updated_on;
    public $mail_status;
    public $restaurant_id = 0;
    protected $_db_table_name = 'Dashboard\Model\DbTable\UserReferralsTable';

    public function __construct() {
        $this->updated_on = date("Y-m-d H:i:s");
    }

    public function getReferralData($user_id) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            //'updated_on' => new Expression("DATE_FORMAT(`user_referrals`.`updated_on`, '%Y-%m-%d')"),
            'order_placed' => 'order_placed',
                )
        );
        $select->join(
                'users', 'users.id = user_referrals.user_id', array(
            'name' => new Expression('CONCAT(`first_name`," ",`last_name`)'),
            'id',
            'email',
            'display_pic_url',
            'created_at' => new Expression("DATE_FORMAT(`users`.`created_at`, '%d/%m/%Y')")
                ), 'inner');

        $where = new Where ();
        $where->equalTo('user_referrals.inviter_id', $user_id);
        $select->where($where);
        $refDetails = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $refDetails->toArray();
    }

    public function getGuestInviters($userIds, $restId) {
        $ids = [];
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
             'inviter_id' => new Expression('DISTINCT(inviter_id)'),
        ));
        $where = new Where();
        $where->equalTo('restaurant_id', $restId);
        $where->in('inviter_id', $userIds);
        $select->where($where);
        $inviters = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (count($inviters) > 0) {
            $invits = array();
            foreach ($inviters as $key => $v) {
                $invits[] = $v['inviter_id'];
            }
            foreach ($userIds as $key => $value) {
                if (!in_array($value, $invits)) {
                    $ids[] = $value;
                }
            }
            return $ids;
        } else {
            return $userIds;
        }
    }
}
