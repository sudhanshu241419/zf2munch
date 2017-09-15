<?php
namespace Restaurant\Controller;
use MCommons\Controller\AbstractRestfulController;
class WebUserPromocodesController extends AbstractRestfulController {

    public function getList() {        
        $userId = $this->getUserSession()->getUserId();        
        $selectedLocation = $this->getUserSession ()->getUserDetail ( 'selected_location', array () );
        $cityId = $selectedLocation ['city_id'];
        $cityModel = new \Home\Model\City();
        $cityDetails = $cityModel->cityDetails($cityId);
        $currentCityDateTime = \MCommons\StaticOptions::getRelativeCityDateTime(array(
             'state_code' => $cityDetails [0] ['state_code']
        ))->format('Y-m-d H:i:s');
        
        if (!$userId) {
            throw new \Exception('User id not found');
        }
        
        $userFunctions = new \User\UserFunctions();
        $userFunctions->userId = $userId;
        $userFunctions->getUserPromocodeDetails();
        $userFunctions->currentDateTimeUnixTimeStamp = strtotime($currentCityDateTime);
        if($userFunctions->userPromocodes){
            if($userFunctions->getNewUserPromotion()){ 
                $userFunctions->userPromocodes[$userFunctions->promocodeId]['promocodeType']=(int)1;
                $userFunctions->userPromocodes[$userFunctions->promocodeId]['cityDateTime']=$currentCityDateTime;
               return $userFunctions->userPromocodes[$userFunctions->promocodeId];                
            }elseif($userFunctions->getUserPromocode()){
                $userFunctions->userPromocodes[$userFunctions->promocodeId]['promocodeType']=(int)0;
                $userFunctions->userPromocodes[$userFunctions->promocodeId]['cityDateTime']=$currentCityDateTime;
               return $userFunctions->userPromocodes[$userFunctions->promocodeId];               
            }
        }    
        return array();
    }

}