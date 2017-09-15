<?php
namespace Dashboard\Model;

use MCommons\Model\AbstractModel;

class UserActionSettings extends AbstractModel
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

  protected $_db_table_name = 'Dashboard\Model\DbTable\UserActionSettingTable';

  protected $_primary_key = 'id';
  public function select(array $options=array()){
    $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
    return $this->find($options)->toArray();
  }
  public function getPermissionToSendMail($userId = 0) {
        if ($userId > 0) {
            $select = new Select();
            $select->from($this->getDbTable()
                            ->getTableName());
            $select->columns(array('email_sent'));
            $where = new Where ();
            $where->equalTo('user_id', $userId);
            $select->where($where);
            //var_dump($select->getSqlString($this->getPlatform('READ')));die;
            $settings = $this->getDbTable()
                            ->setArrayObjectPrototype('ArrayObject')
                            ->getReadGateway()
                            ->selectWith($select)->current();
            //$settings = UserActionSettings::get_user_actionsettings($userId);
            if ($settings) {
                if ($settings->email_sent == 1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }

}
