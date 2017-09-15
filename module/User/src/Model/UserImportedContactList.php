<?php
namespace User\Model;

use MCommons\Model\AbstractModel;

class UserImportedContactList extends AbstractModel
{

    public $id;

    public $user_id;

    public $contact_source;

    public $contact_list;

    public $create_at;

    protected $_db_table_name = 'User\Model\DbTable\UserImportedContactListTable';

    protected $_primary_key = 'id';

    public function getUser(array $options = array())
    {
         $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
         return $this->find($options)->current();
    }
    /**
     * Update User Contact List From Gmail Mail/Hotmail/Yahoo Maill
     * @param unknown $userId
     * @param unknown $list
     * @param unknown $source
     * @param unknown $currentDate
     * @throws \Exception
     * @return True
     */
    public function updateUserContactList($userId, $list, $source, $currentDate)
    {  
        $data = array(
            'contact_list' => $list,
            'create_at' => $currentDate
        );
        
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $dataUpdated = $writeGateway->update($data, array(
            'user_id' => $userId,
            'contact_source' => $source
        ));
        if ($dataUpdated == 0) {
            throw new \Exception("Invalid User ID provided", 500);
        } else {
            return array(
                'success' => 'true'
            );
        }
    }
   /**
    * Add User Contact List From Gmail Mail/Hotmail/Yahoo Maill
    * @param unknown $userId
    * @param unknown $list
    * @param unknown $source
    * @param unknown $currentDate
    * @throws \Exception
    * @return multitype:string
    */
    public function addUserContactList($userId, $list, $source, $currentDate)
    {
        $data = array(
            'user_id' => $userId,
            'contact_source' => $source,
            'contact_list' => $list,
            'create_at' => $currentDate
        );
        
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $dataUpdated = $writeGateway->insert($data);
        if ($dataUpdated == 0) {
            throw new \Exception("Invalid Data provided", 500);
        } else {
            return array(
                'success' => 'true'
            );
        }
    }
}