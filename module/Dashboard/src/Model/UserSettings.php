<?php
namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class UserSettings extends AbstractModel
{

  public $id;
  public $user_id;
  public $order;
  public $reservation;
  public $bookmarks;
  public $checkin;
  public $muncher_unlocked;
  public $upload_photo;
  public $reviews;
  public $tips;
  public $email_sent;
  public $notification_sent;
  public $sms_sent;
  public $created_at;
  public $updated_at;

  protected $_db_table_name = 'Dashboard\Model\DbTable\UserSettingsTable';

  protected $_primary_key = 'id';
  public function select(array $options=array()){
    $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
    return $this->find($options)->toArray();
  }
  public function getUserSettings($userId = 0) {
        if ($userId > 0) {
            $select = new Select();
            $select->from($this->getDbTable()
                            ->getTableName());
            $select->columns(array('reservation_confirmation'));
            $where = new Where ();
            $where->equalTo('user_id', $userId);
            $select->where($where);
            //var_dump($select->getSqlString($this->getPlatform('READ')));die;
            $settings = $this->getDbTable()
                            ->setArrayObjectPrototype('ArrayObject')
                            ->getReadGateway()
                            ->selectWith($select)->current();
            return $settings;
        }
    }

}
