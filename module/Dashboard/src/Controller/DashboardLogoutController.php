<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;

class DashboardLogoutController extends AbstractRestfulController {
    public function getList() { 
        $dashboardFunctions = new \Dashboard\DashboardFunctions();
        if($dashboardFunctions->token){         
            if($dashboardFunctions->logoutDashboard()){
                //$this->createToken();
                return array("message"=>true);
            }else{
               throw new \Exception("Invalid token", 400);
            }
        }else{
            throw new \Exception("Invalid token", 400);
        }
    }
    
    public function createToken(){
        $tokenTime = microtime();
        $salt = "Munc!";
        $token = md5($salt . $tokenTime);
        // Save database entry
        $tokenModel = new \Dashboard\Model\Token();
        $tokenModel->token = $token;
        $tokenModel->ttl = $this->getDefaultTtl();
        $tokenModel->created_at = StaticOptions::getDateTime()->format(StaticOptions::MYSQL_DATE_FORMAT);
        $tokenModel->last_update_timestamp = StaticOptions::getDateTime()->getTimestamp();
        $tokenModel->save();           
        $this->response->setHeaders(StaticOptions::getExpiryHeaders());
    }
     protected function getDefaultTtl() {
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $apiStandards = isset($config['api_standards']) ? $config['api_standards'] : array();
        $defaultTtl = isset($apiStandards['default_ttl']) ? $apiStandards['default_ttl'] : 315360000;
        return $defaultTtl;
    }
}
