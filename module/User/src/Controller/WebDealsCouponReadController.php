<?php
namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class WebDealsCouponReadController extends AbstractRestfulController {

    public function getList() {
        $session = $this->getUserSession();
        if($session->isLoggedIn()){
            $userId = $session->getUserId();
            $userDeal = new \User\Model\UserDeals();
            $options = array('columns'=>array('deal_id'),'where'=>array('user_id'=>$userId));
            $userDealId = $userDeal->getUserDeals($options);
            if(!empty($userDealId)){
                $userDeal->readUserDeals($userId);
                $dealsCoupons = new \Restaurant\Model\DealsCoupons();
                $dealsCoupons->getDbTable()->setArrayObjectPrototype('ArrayObject');
                $data = array('read'=>1);
                $predicate = array('id'=>array_column($userDealId,'deal_id'));
                $dealsCoupons->abstractUpdate($data, $predicate);
                return array('success'=>true);
            }
        }
        return array('success'=>false);
    }

}

