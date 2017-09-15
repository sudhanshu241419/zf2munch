<?php

/*
 * To assign $5 off to existing user 
 */

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class AssignDollarFiveController extends AbstractRestfulController {

    public function create($data) {
        if (isset($data['userid']) && !empty($data['userid']) && ASSIGNDOLLAR5PROMO) { //it should be comma sepreated
            $userIds = explode(",", $data['userid']);
            $userFunction = new \User\UserFunctions();
            $locationData['state_code'] = 'NY';
            $currentDate = $userFunction->userCityTimeZone($locationData);
            $userPromocode = new \Restaurant\Model\UserPromocodes();
            $promocodes = new \Restaurant\Model\Promocodes();
            foreach ($userIds as $key => $userId) {
                #####################
                if(!empty($userId) && $userPromocode->getPromocodeOfUser($userId)){
                
                $pDetails['start_on'] = $currentDate;
                $currentDate1 = new \DateTime($currentDate);
                $currentDate1->add(new \DateInterval(PROMOCODE_ENDDATE));
                $endDate = $currentDate1->format('Y-m-d H:i:s');
                $pDetails['end_date'] = $endDate;
                $pDetails['promocodeType'] = 2;
                $pDetails['discount'] = PROMOCODE_FIRST_REGISTRATION;
                $pDetails['discount_type'] = 'flat';
                $pDetails['status'] = 1;
                $pDetails['deal_for'] = 'delivery/takeout/dine-in';
                $pDetails['title'] = '$' . PROMOCODE_FIRST_REGISTRATION . ' for You';
                $pDetails['description'] = 'Enjoy free order up to $' . PROMOCODE_FIRST_REGISTRATION . ' on pre-paid reservation or delivery or take-out orders';
                $addPromocode = $promocodes->insert($pDetails);
                if ($addPromocode) {
                    $upDetail['promo_id'] = $promocodes->id;
                    $upDetail['user_id'] = $userId;
                    $upDetail['reedemed'] = 0;
                    $upDetail['order_id'] = 0;
                    $userPromocode = new \Restaurant\Model\UserPromocodes();
                    $userPromocode->insert($upDetail);
                }
            }
            }
            return array('success' => true);
        }
        return array('success' => false);
    }

    public function getList() {
        $user = new \User\Model\User();
        $userPromocode = new \Restaurant\Model\UserPromocodes();
        $apus = $userPromocode->getUserAssignPromo(); //assgin promocode user
        $apu = array();
        //pr($apus,1);       
        foreach ($apus as $key => $apuv) {
            $apu[] = $apuv['user_id'];
        }
        //pr($apu,1);
        $type = $this->getQueryParams('type');
//        $request  = $this->getRequest()->getHeaders()->toArray();
//        pr($request,1);//Get User Agent
        if ($type == 'no') {
            $uo = $user->getUserNotOrder(); //uo:user and order
            $uno = array();
            $i = 1;
            $csvFile = BASE_DIR.'/public/assets/user.csv';
            //$str = "First name, Email \n";
            foreach ($uo as $key => $val) {
                if ($val['order_id'] == null) {
                    if (!in_array($val['id'], $apu)) {
                        $uno[$i] = $val;
                        $str = $val['first_name'].", ".$val['email']."\n";
                        $file_handle = fopen($csvFile, 'a+');
                        fwrite($file_handle, $str);                        
                        $i++;
                        //$str = '';
                    }
                }
            }
            fclose($file_handle);
            return $uno;
        }
        
        
        
        $options = array('columns' => array('id', 'first_name', 'email', 'created_at', 'city_id'));
        $userDetails = $user->getAUser($options);
        $totalUser = count($userDetails);
        return array('user_detials' => $userDetails, 'total_user' => $totalUser);
//        switch ($type){
//            case "user":
//            $options = array('columns'=>array('id','first_name','email','created_at'));
//            return $user->getAUser($options); 
//            break;
//            case "order":
//                
//                break;
//            case "promo":
//                break;
//            default:
//                $options = array('columns'=>array('id','first_name','email','created_at'));
//                return $user->getAUser($options); 
//              
//        }
    }

}
