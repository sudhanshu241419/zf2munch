<?php

namespace Search\Model;

use MCommons\Model\AbstractModel;

class LogError extends AbstractModel {

    public $level;
    public $message;
    public $origin;
    public $happened_at;
    
    protected $_db_table_name = 'Search\Model\DbTable\LogErrorTable';
    protected $_primary_key = 'id';

    /**
     * Writes log
     * @param array $data with keys 'level', 'message' and 'origin'
     * @return boolean
     */
    public function saveErrorLog($data) {
        try {
            $dataArray = array(
                'level' => $data['level'],
                'message' => $data['message'],
                'origin' => $data['origin'],
                'happened_at' => date('Y-m-d H:i:s'),
            );
            $this->getDbTable()->getWriteGateway()->insert($dataArray);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

}
