<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;


class MerchantAgreementController extends AbstractRestfulController {
   
    
    public function getList() {
      $merchant = new \Dashboard\Model\MerchantRegistration();  
      $offset =$this->getQueryParams('s',false); 
      $limit =$this->getQueryParams('l',false); 
      $sl = $this->getServiceLocator();
      $config = $sl->get('Config');
      $dataCount=count($merchant->countRestaurantAgreements());
      $data=$merchant->getRestaurantAgreements($offset,$limit);
      if($dataCount > 0){
      return array("count"=>$dataCount,'data'=>$data);
      }
      return array("count"=>0,'data'=>array());
    }
    

}