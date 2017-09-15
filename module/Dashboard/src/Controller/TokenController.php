<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\Model\Token;
use MCommons\StaticOptions;

class TokenController extends AbstractRestfulController {

    public function get($token) {    
        try {
            $tokenModel = new Token();
            $tokenModel->getDbTable()->setArrayObjectPrototype('Dashboard\Model\Token');
            //pr($token,1);
            $authDetails = $tokenModel->findToken($token);
            //pr($authDetails,1);
            if ($authDetails && isset($authDetails['token'])) {
                $this->response->setHeaders(StaticOptions::getExpiryHeaders());
                return array('token'=>$authDetails['token']);
            }else{
                throw new \Exception("Invalid token",400);
            }
            
        } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On Token Api');
            throw new \Exception($e->getMessage(),400);
        }
    }

    public function create($data) {
        try{
        $tokenTime = microtime();
        $salt = "Munc!";
        $token = md5($salt . $tokenTime);
        // Save database entry
        $tokenModel = new Token();
        $tokenModel->token = $token;
        $tokenModel->ttl = $this->getDefaultTtl();
        $tokenModel->created_at = StaticOptions::getDateTime()->format(StaticOptions::MYSQL_DATE_FORMAT);
        $tokenModel->last_update_timestamp = StaticOptions::getDateTime()->getTimestamp();
        if (!$tokenModel->save()) {
            return $this->sendError(array('error' => 'Unable to save data'), 500);
        }
        $this->response->setHeaders(StaticOptions::getExpiryHeaders());
        // db entry
        return array("token" => $token);
        }  catch (\Exception $e){
            \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On Token Api');
           throw new \Exception($e->getMessage(),400);
        }
    }

    public function delete($token) {
        $tokenModel = new Token();
        $tokenModel->token = $token;
        $deleted = $tokenModel->delete();
        $this->response->setHeaders(StaticOptions::getExpiryHeaders());
        return array(
            "deleted" => (bool) $deleted
        );
    }

    protected function getDefaultTtl() {
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $apiStandards = isset($config['api_standards']) ? $config['api_standards'] : array();
        $defaultTtl = isset($apiStandards['default_ttl']) ? $apiStandards['default_ttl'] : 315360000;
        return $defaultTtl;
    }

}