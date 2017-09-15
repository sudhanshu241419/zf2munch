<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\DashboardOrder;
use Dashboard\Model\DashboardReservation;
use Dashboard\Model\User;
use Dashboard\Model\ActivityFeed;
use Dashboard\Model\MenuBookmark;
use Dashboard\Model\UserPoint;
use Dashboard\Model\UserReferrals;

class UserController extends AbstractRestfulController {

    public $msg;
    public $status;
    public $restaurantId;

    public function getList() {
        $userIds = [];
        $dashboardFunction = new DashboardFunctions();
        $restaurantServer = new \Dashboard\Model\RestaurantServer();
        $filter = $this->getQueryParams('filter', false);
        $filter_val = $this->getQueryParams('value', false);
        $type = $this->getQueryParams('type', false);
        $restId = $dashboardFunction->getRestaurantId();
        if ($filter == 'default') {
            $flag = 'default';
        } else {
            $flag = 'filter';
        }
        $users = $restaurantServer->getGuestbook($restId, [0], 'default');
        if (empty($users) && count($users) == 0) {
            $data['guestbook_count'] = 0;
            $data['guestbook_list'] = [];
            return $data;
        }
        foreach ($users as $key => $val) {
            $userIds[] = $val['id'];
        }
        $ids = $this->filterConditons($userIds, $restId, $filter, $filter_val);
        $guestBook = array();
        $data['guestbook_count'] = count($restaurantServer->getGuestbook($restId, $ids, $flag));
        $data['guestbook_list'] = $restaurantServer->getGuestbook($restId, $ids, $flag);
        $userPointsModel = new UserPoint();
        $orderModel = new DashboardOrder();
        $reservationModel = new DashboardReservation();
        $serverModel = new \Dashboard\Model\Servers();
        foreach ($data['guestbook_list'] as $key => $value) { 
            $guestPoint = $userPointsModel->getGuestPoints($value['id'], $restId);
            $dateTimeGuest = strtotime(date('Y-m-d h:i:s', strtotime($value['created_at']))) - 20000;
            $guestBook[$key]['fullname'] = ucfirst($value['first_name']) . " " . ucfirst($value['last_name']);
            
            $guestBook[$key]['email'] = $value['email'];
            $orders = $orderModel->getGuestTotalOrdersAndRevenue($value['user_id'], $value['restaurant_id'], $dateTimeGuest);
           
            $reservations = $reservationModel->getGuestTotalReservations($value['user_id'], $value['restaurant_id'], $dateTimeGuest);
            $guestBook[$key]['total_orders'] = $orders['total_order'];
            $guestBook[$key]['total_reservations'] = ($reservations) ? $reservations : 0;
            $guestBook[$key]['server'] = $serverModel->getServerName($value['code'], $value['restaurant_id']);
            $guestBook[$key]['points'] = (isset($guestPoint['total_points']) && $guestPoint['total_points'] > 0) ? $guestPoint['total_points'] : 0;
            $guestBook[$key]['join_date'] = self::time_format(strtotime($value['created_at']));
            $guestBook[$key]['id'] = $value['id'];
        }
        if (empty($guestBook)) {
            $data['guestbook_count'] = 0;
        }
        $data['guestbook_list'] = $guestBook;
        if ($type == 'download') {
            $this->downloadGuestCsv($data);
            return ['status' => 'success'];
        } else {
            return $data;
        }
    }

    public function get($id) {
        $guestDetail = [];
        $dashboardFunction = new DashboardFunctions();
        $restId = $dashboardFunction->getRestaurantId();
        $userModel = new User();
        $feedModel = new ActivityFeed();
        $userOrderModel = new DashboardOrder();
        $userReservationModel = new DashboardReservation();
        $userReviewModel = new \Dashboard\Model\UserReview();
        $bookmarkModel = new MenuBookmark();
        $orders = $userOrderModel->getGuestTotalOrdersAndRevenue($id, $restId);
        $guestDetail['overview'] = $userModel->getGuestDetail($id, $restId);
        $guestDetail['overview']['total_orders'] = $orders['total_order'];
        $guestDetail['overview']['total_reservations'] = ($userReservationModel->getGuestTotalReservations($id, $restId)) ? $userReservationModel->getGuestTotalReservations($id, $restId) : 0;
        $guestDetail['overview']['frequency'] = substr($orders['total_order'] / 12, 0, 4);
        $guestDetail['overview']['total_spend'] = ($orders['total_spend']) ? $orders['total_spend'] : 0 ;
        $guestDetail['overview']['average_spend'] = ceil($orders['average_spend']);
        $guestDetail['overview']['first_order'] = $userOrderModel->getGuestFirstOrder($id, $restId);
        $guestDetail['overview']['last_transaction'] = $userOrderModel->getGuestLastTransaction($id, $restId);
        $guestDetail['overview']['favourites'] = $bookmarkModel->getGuestFavouriteItems($id, $restId);
        $guestDetail['overview']['review_tips'] = $userReviewModel->getGuestReviewsAndTips($id, $restId);
        $guestDetail['overview']['offers'] = $userModel->getGuestOffers($id, $restId);
        $guestDetail['overview']['created_at'] = gmdate('D, M d Y', strtotime($guestDetail['overview']['created_at']));
        $guestDetail['activities'] = $feedModel->getGuestFeeds($id, $restId);
        return $guestDetail;
    }

    public static function time_format($timestamp) {
        $diff = abs(strtotime(date('Y-m-d H:i:s')) - (int) $timestamp);
        $value = floor($diff / 31556926);
        return ($value > 0) ? $value . ' year+' : date('m/d/Y', $timestamp);
    }

    public function downloadGuestCsv($data) {
        $headers = array('Fullname', 'Email', 'Total Orders', 'Total Reservations', 'Server', 'Points', 'Join Date');
        $fp = fopen('php://output', 'w');
        if ($fp) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="guestbook.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            fputcsv($fp, $headers);
            foreach ($data['guestbook_list'] as $value) {
                unset($value['id']);
                fputcsv($fp, array_values($value));
            }
            die;
        }
    }

    public function filterConditons($userIds, $restId, $filter, $filter_val) {
        $orderModel = new DashboardOrder();
        $userPointsModel = new UserPoint();
        $referralModels = new UserReferrals();
        if ($filter == 'days') {
            $date = new \DateTime();
            $endDate = $date->format('Y-m-d') . " 11:59:59";
            $filter_val = $filter_val - 1;
            $date->sub(new \DateInterval('P' . $filter_val . 'D'));
            $startDate = $date->format('Y-m-d') . " 00:00:00";
            $ids = $orderModel->getGuestNotOrders($userIds, $restId, $startDate, $endDate);
            if (empty($ids)) {
                $ids = [0];
            }
        }
        if ($filter == 'month') {
            $ids = $orderModel->getMonthWiseOrders($userIds, $restId, $filter_val);
            if (empty($ids)) {
                $ids = [0];
            }
        }
        if ($filter == 'default') {
            $ids = [0];
        }
        if ($filter == 'points') {
            $ids = $userPointsModel->getUserPointsData($userIds, $restId, $filter_val);
            if (empty($ids)) {
                $ids = [0];
            }
        }
        if ($filter == 'friend') {
            $ids = $referralModels->getGuestInviters($userIds, $restId);
            if (empty($ids)) {
                $ids = [0];
            }
        }
        return $ids;
    }
}
