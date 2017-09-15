<?php

namespace Servers\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\RestaurantServer;
use User\Model\UserOrder;

class WebServerCustomersController extends AbstractRestfulController {

    public function getList() {
        $month = $this->getQueryParams('month', false);
        $page = $this->getQueryParams('page', false);
        if($month == 8){
            $startDate = '2016-08-01 00:00:00';
            $endDate = '2016-08-31 11:59:59';
        }else if($month == 9){
            $startDate = '2016-09-01 00:00:00';
            $endDate = '2016-09-30 11:59:59';
        }else if($month == 10){
            $startDate = '2016-10-01 00:00:00';
            $endDate = '2016-10-31 11:59:59';
        }
        else if($month == 11){
            $startDate = '2016-11-01 00:00:00';
            $endDate = '2016-11-30 11:59:59';
        }
        else if($month == 12){
            $startDate = '2016-12-01 00:00:00';
            $endDate = '2016-09-31 11:59:59';
        }else{
            $startDate = '2016-09-01 00:00:00';
            $endDate = date("Y-m-d") ." 11:59:59";
        }
        $limit = 5;  
        ($page) ? $page : 1; 
        $start = ($page-1) * $limit;
        $session = $this->getUserSession();
        if ($session->isLoggedIn()) {
            $customers = new RestaurantServer();
            $order = new UserOrder();
            $userData = $session->getUserDetail('server_user_detail'); 
            $customersList = $customers->getServerCustomersList($userData['server_code'],$startDate,$endDate,$start,$limit);
            $totalRecords = $customers->getServerCustomersList($userData['server_code'],$startDate,$endDate,0,1000);
            $totaPages = ceil(count($totalRecords) / $limit);
            $totalTransactions = 0;
            foreach ($customersList as $key => $value) {
                $customersList[$key]['s_no'] = $key + 1;
                $customersList[$key]['customer'] = $value['first_name'] . ' ' . $value['last_name'];
                $date=date_create(substr($value['date'], 0, 10));
                $customersList[$key]['join_date'] = date_format($date,"m/d/Y");
                $firstTrans = $order->getUserfirstOrder($value['user_id'], $value['restaurant_id'],$startDate,$endDate);
                //$referals = $order->getReferals($value['user_id'], $value['restaurant_id']);
                $customersList[$key]['first_transaction'] = $firstTrans;
                $customersList[$key]['referals'] = 0;
            }
            foreach ($totalRecords as $key => $value) {
                $firstTrans = $order->getUserfirstOrder($value['user_id'], $value['restaurant_id'],$startDate,$endDate);
                if (!empty($firstTrans)) {
                    $totalTransactions ++;
                }
            }
            return array('total_pages' => $totaPages,'total_customers' => count($totalRecords), 'total_transaction' => $totalTransactions, 'total_referals' => 0, 'data' => $customersList);
        } else {
            return array('total_pages' => 0,'total_customers' => 0, 'total_transaction' => 0, 'total_referals' => 0, 'data' => 0);
        }
    }
}
