<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;

class DashboardAppForceUpdateController extends AbstractRestfulController {

    public function getList() {
        $currentVersion = $this->getQueryParams("current_version",false);
        
        //$userAgent = \MCommons\StaticOptions::getUserAgent();        
        $hardVersion = DASHBOARD_HARD_VERSION_ANDROID;
        $softVersion = DASHBOARD_SOFT_VERSION_ANDROID;
        
        if($currentVersion < $hardVersion){	
            $updateType = "hard";		
        }elseif($currentVersion < $softVersion){
            $updateType = "soft";
        }else{
            $updateType = "no";
        }
        
        $appUpdate = array(
            "upgrade_type"=>$updateType,
            "counter"=>COUNTER,
            "message"=>FOURCE_UPDATE_MESSAGE,
            "clear_data"=>CLEAR_DATA,
            "apk_link"=>APK_FILE_PATH
            );
        return $appUpdate;
    }
}

