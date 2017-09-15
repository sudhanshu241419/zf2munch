<?php

namespace Search\Model;

use MCommons\Model\AbstractModel;

class LogSearch extends AbstractModel {

    public $user_id;
    public $search_key;
    public $searched_on;
    public $ip;
    public $user_agent;
    public $filters;
    public $where;
    
    protected $_db_table_name = 'Search\Model\DbTable\LogSearchTable';
    protected $_primary_key = 'id';
    
    /**
     * 
     * @param array $data containing q and user_id
     * @return boolean
     */
    public function saveSearchLogWeb($data) {
        $q = trim($data['sq']);
        if ($q != "") {
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR']: $_SERVER['REMOTE_ADDR'];
            $dataArray = array(
                'user_id' => 0,
                'search_key' => $q,
                'searched_on' => date('Y-m-d H:i:s'),
                'ip' => ip2long($ip),
                'user_agent' => $this->getBrowserName($_SERVER['HTTP_USER_AGENT']),
                'device' =>self::user_agent($_SERVER['HTTP_USER_AGENT']),
                'filters' => isset($data['fq'])?$data['fq']:'',
                'where' => isset($data['av'])?$data['av']: ''
            );
            $this->getDbTable()->getWriteGateway()->insert($dataArray);
            \MCommons\StaticOptions::resquePush($dataArray, "activityLog");
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 
     * @param array $data containing q and user_id
     * @return boolean
     */
    public function saveSearchLogMob($data) {
        $q = trim($data['q']);
        $data['cuisines'] = isset($data['cuisines']) ? $data['cuisines'] : '';
        if ($q != "") {
            $ip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR']: $_SERVER['REMOTE_ADDR'];
            $dataArray = array(
                'user_id' => 0,
                'search_key' => $q,
                'searched_on' => date('Y-m-d H:i:s'),
                'ip' => ip2long($ip),
                'user_agent' => 'App',
                'device' =>self::user_agent($_SERVER['HTTP_USER_AGENT']),
                'filters' => $data['cuisines'],
                'where' => $data['av'],
            );
            $this->getDbTable()->getWriteGateway()->insert($dataArray);
            return true;
        } else {
            return false;
        }
    }

    private function getBrowserName($useragent) {
        $browsers = Array('msie', 'chrome', 'safari', 'firefox', 'opera');
        preg_match("/(?:version\/|(?:msie|chrome|safari|firefox|opera) )([\d.]+)/i", $useragent);
        $browser = "";
        foreach ($browsers as $b) {
            if (stripos($useragent, $b) !== false) {
                $browser = $b;
                break;
            }
        }
        return $browser;
    }
    
    private function user_agent($useragent){
    $iPod = strpos($useragent,"iPod");
    $iPhone = strpos($useragent,"iPhone");
    $iPad = strpos($useragent,"iPad");
    $android = strpos($useragent,"Android");
    $webOS   = stripos($useragent,"webOS");
    //file_put_contents('./public/upload/install_log/agent',$useragent);
    if($iPad||$iPhone||$iPod){
        return 'ios';
    }else if($android){
        return 'android';
    }else{
        return 'desktop';
    }
}

}
