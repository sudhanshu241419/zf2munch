<?php
namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class WebRegistrationWithSmsController extends AbstractRestfulController {

    public function getList() {
      $str= $this->getQueryParams('q', false);
      $userFunction = new \User\UserFunctions();
      if($userFunction->parseLoyaltyRegistrationSms($str)){          
          if($userFunction->userRegistrationWithSmsWeb()){             
                $userFunction->registerRestaurantServer();
                return array("success"=>true);
          }
          
      }
      return array("success"=>false);
    }

}