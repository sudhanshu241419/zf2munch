<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\DashboardOrder;

class DashboardController extends AbstractRestfulController {
    public $restaurantId;
    public function getList() { 
        $dashboardFunctions = new DashboardFunctions();            
        $dashboardFunctions->getDashboardDetail();//restaurant Details
        $currentDate = $dashboardFunctions->CityTimeZone();       
        $this->restaurantId = $dashboardFunctions->restaurantId;
        $staticsData = [];
        
        $reservationList = [];
        if($dashboardFunctions->restaurantId){
            $dashboardOrder = new DashboardOrder();
            $dashboardReservation = new \Dashboard\Model\DashboardReservation();  
            $dealsCoupons = new \Dashboard\Model\DealsCoupons();
            $userReview = new \Dashboard\Model\UserReview();
            $restaurantReview = new \Dashboard\Model\RestaurantReview();
            $sl = $this->getServiceLocator();
            $config = $sl->get('Config');
            $orderStatus = isset($config['constants']['order_status']) ? $config['constants']['order_status'] : array();
            
            error_reporting(~E_NOTICE);

            $mongoClient = new \MongoClient($config['mongo']['host']);
            $mongoDb = new \MongoDB($mongoClient, $config['mongo']['database']);
            $collectionName = 'reports';
            if (!$mongoDb->$collectionName) {
                $collection = $mongoDb->createCollection($collectionName);
            }
            $collection = $mongoDb->createCollection($collectionName);
            $condition = array('restaurant_id' => (int) $this->restaurantId);
            $currentDateData = $collection->find($condition)->sort(array('date' =>-1));
            $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
            $todayReport = $reportCurrentTodayDate[0]['reports'];
            $newc=$todayReport['munchado']['orders']['order_alltime']['new_customers'];
            $repeatc= $todayReport['munchado']['orders']['order_alltime']['returning_customers'];
            $total_visits=$todayReport['social']['ga']['visit'];
            
            $status[] = $orderStatus[2];
            $options = array(
                'restaurant_id' => $this->restaurantId,
                'offset' => 0,
                'orderby' => 'date',
                'orderStatus' => $status,
                'currentDate' => $dashboardFunctions->CityTimeZone(),
                'limit' => 3,
                'type' => 'live'
            );
            $orderList = $dashboardOrder->getLiveOrder($options);
            $activeOrderTotal = $dashboardOrder->getTotalRestaurantOrder($this->restaurantId,false);
            $archiveOrderStatus = array($orderStatus[3],$orderStatus[6],$orderStatus[9],$orderStatus[5]);
            $totalArchiveOrder = $dashboardOrder->getArchiveOrderCount($this->restaurantId,$archiveOrderStatus,$currentDate);
            
            $currentReservation = $dashboardReservation->getReservation($this->restaurantId,'current');
            $totalActiveReservation = $dashboardReservation->getTotalActiveReservations($this->restaurantId);
            $revenue = $dashboardOrder->getRevenue($this->restaurantId); 
            
            $totalReservation = $dashboardReservation->getTotalRestaurantReservations($this->restaurantId)[0]['total_reservation'];
            $staticsData['total_order_count'] = (int)$dashboardOrder->getTotalRestaurantOrder($this->restaurantId, true)[0]['total_order'];
            $staticsData['total_reservation_count'] = (int)$totalReservation;
            $staticsData['total_revenue'] =number_format($revenue[0]['revenue'],2);
            $staticsData['total_deals_coupons'] = (int)$dealsCoupons->getDealsCount($this->restaurantId)[0]['total_deal'];
            $staticsData['live_deals_count'] = (int)$dealsCoupons->liveDealsCount($this->restaurantId)[0]['total_deal'];
            //$staticsData['unread_review_count'] = RestaurantDealCoupon::live_deals_count($restaurant_id); 
            $staticsData['review_count'] = (int)$userReview->dashboardTotalUserReviews($this->restaurantId)[0]['total_review'];
            $staticsData['other_review_count'] = (int)$restaurantReview->getRestaurantReviewCount($this->restaurantId)['total_count'];
            $staticsData['total_review_count'] = (int)$staticsData['review_count'];
            $staticsData['newc'] = (int)$newc;
            $staticsData['repeatc'] = (int)$repeatc;
            $staticsData['total_visits'] = (int)$total_visits;
            
            //$this->getCustomer($staticsData);
            
            $currentVersion = $this->getQueryParams("current_version",false);
            $fourceUp=\MCommons\StaticOptions::fourceUpdate($currentVersion);
            return array(
                'order_detail'=>  array_slice($orderList, 0,3),
                'total_order'=>(int)$activeOrderTotal[0]['total_order'],
                'current_reservation'=>$currentReservation,
                'total_reservation'=>(int)$totalActiveReservation[0]['total_reservations'],                
                'statistics'=>$staticsData,
                'total_archive_order'=>(int)$totalArchiveOrder[0]['total_order'],
                'cDate'=> date("M d,Y",strtotime($currentDate)),
                'fource_update'=>$fourceUp
        
            );                   
        }
        
        return array();       
    }
    
    public function getCustomer(&$staticsData) {
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $mongoClient = new \MongoClient($config['mongo']['host']);
        $mongoDb = new \MongoDB($mongoClient, $config['mongo']['database']);
        $collectionName = 'reports';
        if (!$mongoDb->$collectionName) {
            $collection = $mongoDb->createCollection($collectionName);
        }
        $collection = $mongoDb->createCollection($collectionName);
        $condition = array('restaurant_id' => $this->restaurantId);
        $currentDateData = $collection->find($condition)->sort(array('date' => -1))->limit(1);
        $reportCurrentTodayDate = iterator_to_array($currentDateData, FALSE);
        $todayReport = isset($reportCurrentTodayDate[0]['reports'])?$reportCurrentTodayDate[0]['reports']:array();
        $ga_users = isset($todayReport['social']['ga']['users'])?(int) $todayReport['social']['ga']['users']:0;
       
        $order_new_users = isset($todayReport['munchado']['orders']['order_alltime']['new_customers'])?(int) $todayReport['munchado']['orders']['order_alltime']['new_customers']:0;
        $order_repeat_users = isset($todayReport['munchado']['orders']['order_alltime']['returning_customers'])?(int) $todayReport['munchado']['orders']['order_alltime']['returning_customers']:0;

//        $ga_new_users = $order_new_users;
//        $ga_new_users = ($ga_new_users > 0) ? $ga_new_users : 0;
//        $ga_repeat_users = $order_repeat_users;
//        $ga_repeat_users = ($ga_repeat_users > 0) ? $ga_repeat_users : 0;
        
        $staticsData['total_visits'] = $ga_users;
        $staticsData['newc'] = $order_new_users;
        $staticsData['repeatc'] = $order_repeat_users;
    }
    
    public function latestThreeSnagSpotTable(){
        $reservationModel = new \Restaurantdinein\Model\Restaurantdinein();
        $dashboardFunctions = new DashboardFunctions();
        $restId = $dashboardFunctions->getRestaurantId();
        $upcomingCondition = array(
            'restaurantid' => $restId,            
            'archive' => 0,
            'orderBy' => 'created_at DESC'
        );
        $upcomingReservationResponse = $reservationModel->latestThreeSnagSpotTable($upcomingCondition);
        $this->snagSportCount = $reservationModel->countSnagSportTable($restId);
    }

}

