<?php
namespace Restaurant\Controller;
use MCommons\Controller\AbstractRestfulController;
class UserPromocodesController extends AbstractRestfulController {
    
    public function getList() {
        //return  array();  //used to desable mobile promocode untill new build
        try {
        $userId = $this->getUserSession()->getUserId();
        if (!$userId) { 
           return  array();
        }
        $selectedLocation = $this->getUserSession ()->getUserDetail ( 'selected_location', array () );
        $cityId = isset($selectedLocation ['city_id'])?$selectedLocation ['city_id']:false;//18848;
        if(!$cityId){
            throw new \Exception('City id not found.');
        }
        $cityModel = new \Home\Model\City();
        $cityDetails = $cityModel->cityDetails($cityId);
        $currentCityDateTime = \MCommons\StaticOptions::getRelativeCityDateTime(array('state_code' => $cityDetails [0] ['state_code']))->format('Y-m-d H:i:s');
        $userFunctions = new \User\UserFunctions();
        $userFunctions->userId = $userId;
        $userFunctions->getUserPromocodeDetails();
       // pr($userPromocodes);
        $userFunctions->currentDateTimeUnixTimeStamp = strtotime($currentCityDateTime);
        
        if($userFunctions->userPromocodes){
            if($userFunctions->getNewUserPromotion()){
                $userFunctions->userPromocodes[$userFunctions->promocodeId]['promocodeType']=(int)1;
                $promocodes[] = $userFunctions->userPromocodes[$userFunctions->promocodeId];
                return $promocodes;
            }elseif($userFunctions->getUserPromocode()){
                $userFunctions->userPromocodes[$userFunctions->promocodeId]['promocodeType']=(int)0;
                $promocodes[] = $userFunctions->userPromocodes[$userFunctions->promocodeId];
                return $promocodes;
            }
        }        
        return  array();
        } catch (\Exception $e) {
           \MUtility\MunchLogger::writeLog($e, 1,'Something Went Wrong On Promocode Api');
           throw new \Exception($e->getMessage(),400);
        }        
    }
    
    
    
   
    
    
}