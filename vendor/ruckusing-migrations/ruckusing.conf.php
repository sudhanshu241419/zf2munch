<?php
if (! defined ( 'DS' )) {
    define ( 'DS', DIRECTORY_SEPARATOR );
}
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'local'));

$dbConfig = file_get_contents(dirname(__FILE__) . '/../../config/autoload/'.APPLICATION_ENV.'/masters.db.php');

if(!empty($dbConfig)){
   $configArr= explode(',',$dbConfig);
   $userConfig=$configArr[1];
   $userConfig=explode('=>',$userConfig);

   $passConfig=$configArr[2];
   $passConfig=explode('=>',$passConfig);

   $hostConfig=$configArr[3];
   $hostConfig=explode('=>',$hostConfig);

   $hostInfo=$hostConfig[1];
   $hostInfo=explode(';',$hostInfo);
   $dbName=$hostInfo[0];
   $dbName=explode('=',$dbName);
   $hostName=$hostInfo[1];
   $hostName=explode('=',$hostName);
}


/*if(is_array($config[APPLICATION_ENV]['database']['password'])) {
    $config[APPLICATION_ENV]['database']['password'] = '';
}*/

//print str_replace("'","",trim($passConfig[1])); exit;


return array(
        'db' => array(
                

         APPLICATION_ENV => array(
                        'type'      => 'mysql',
                        'host'      => str_replace("'","",trim($hostName[1])),
                        'port'      => 3306,
                        'database'  => str_replace("'","",trim($dbName[1])),
                        'user'      => str_replace("'","",trim($userConfig[1])),
                        'password'  => !empty($passConfig[1]) ? str_replace("'","",trim($passConfig[1])) : '',

                        //'directory' => 'custom_name',
                        //'socket' => '/var/run/mysqld/mysqld.sock'
                ),
         ),

        'migrations_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'migrations',

        'db_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'db',

        'log_dir' => RUCKUSING_WORKING_BASE . DIRECTORY_SEPARATOR . 'logs',

        'ruckusing_base' => dirname(__FILE__) . DIRECTORY_SEPARATOR 

);
