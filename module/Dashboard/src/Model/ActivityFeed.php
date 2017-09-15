<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class ActivityFeed extends AbstractModel {

    public $id;
    public $feed_type_id;
    public $user_id;
    public $feed;
    public $feed_for_others;
    public $event_date_time;
    public $added_date_time;
    public $status;
    public $privacy_status = 0;
    protected $_db_table_name = 'Dashboard\Model\DbTable\ActivityFeedTable';
    protected $_primary_key = 'id';

    public function getRestaurantUserActivity($userId, $restId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'feed',
        ));
        $where = new Where ();
        $where->equalTo('user_id', $userId);
        $where->equalTo('status', '1');
        $select->where($where);
        $select->order('event_date_time DESC');
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        if (empty($data)) {
            return '';
        } else {
            return $data['event_date_time'];
        }
    }
    public function getGuestFeeds($userId, $restId) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array('id', 'feed', 'event_date_time'));
        $select->join(array(
            'aft' => 'activity_feed_type'
                ), 'activity_feed.feed_type_id = aft.id', array(
            'feed_type',
            'feed_message',
                ), $select::JOIN_LEFT);
        $where = new Where ();
        $where->equalTo('activity_feed.user_id', $userId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $feeds = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        if (!empty($feeds)) {
            foreach ($feeds as $key => $val) {
                if (!isset(json_decode($val['feed'])->restaurant_id)) {
                    unset($feeds[$key]);
                }
            }
            foreach ($feeds as $key => $value) {
                if (json_decode($value['feed'])->restaurant_id != $restId) {
                    unset($feeds[$key]);
                } else {
                    $feeds[$key]['restaurant_id'] = json_decode($value['feed'])->restaurant_id;
                    $feeds[$key]['restaurant_name'] = json_decode($value['feed'])->restaurant_name;
                    $feeds[$key]['feed_type'] = $value['feed_type'];
                    $feeds[$key]['feed_text'] = preg_replace("/[^a-zA-Z0-9`_.,;@#%~'\"\+\*\?\[\^\]\$\(\)\{\}\=\!\<\>\|\:\-\s\\\\]+/", "'", json_decode($value['feed'])->text);
                    $feeds[$key]['feed'] = json_decode($value['feed']);
                    
                }
            }
            return array_merge($feeds);
        } else {
            return [];
        }
    }

}
