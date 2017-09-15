<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\DashboardReservation;
use Dashboard\Model\DashboardOrder;
use Dashboard\Model\UserPoint;

class DashboardReservationController extends AbstractRestfulController {

    public $restaurantId;
    public $currentDate;
    public static $statusArray = [
        '0' => 'Archived',
        '1' => 'Upcoming',
        '2' => 'Canceled',
        '3' => 'Rejected',
        '4' => 'Confirmed',
    ];

    public function getList() {
        $reservationModel = new DashboardReservation();
        $dashboardFunctions = new DashboardFunctions();
        $reservationDate = $this->params('date');
        $restId = $dashboardFunctions->getRestaurantId();
        $type= $this->getQueryParams("type",false);
        
        $data = [];
        $archiveStatus = 0;
        $reservationModel->status=1;
        if ($reservationDate == 'today') {
            $reservationDate = date('Y-m-d');
        }
        if (($reservationDate == 'today') || $reservationDate == date('Y-m-d')) {
            $todayObject = new \DateTime(date('Y-m-d') . ' 23:59:59');
            $nextday = date_format($todayObject, 'Y-m-d H:i:s');
        } else {            
            if($type == 'archive'){
                $archiveStatus = 1;
            }
                
            $todayObject = new \DateTime($reservationDate . ' 23:59:59');
            $nextday = date_format($todayObject, 'Y-m-d H:i:s');
            
        }
        $data['today_reservations'] = $reservationModel->getTotalReservationsAndSeats($restId, $reservationDate);
        $data['today_reservations']['total_upcoming_reservations'] = $reservationModel->getTotalUpcomingReservations($restId, $reservationDate);
        
        $data['archive_reservation'] = array();
        $data['incoming_reservations'] = array();
        $data['view_today_reservation'] = array();
        if($archiveStatus==1){            
            $page = $this->getQueryParams('page', false);           
            $limit = 20;  
            ($page) ? $page : 1; 
            $start = ($page-1) * $limit;
            $data['archive_reservation'] = $reservationModel->getAllReservations($restId, $reservationDate,$limit,$start,$archiveStatus);
        }else{
            $data['incoming_reservations'] = $reservationModel->getAllUpcomingReservations($restId, $reservationDate);
            $data['view_today_reservation'] = $reservationModel->getAllReservations($restId, $reservationDate);
        }
        $data['summary_title'] = 'Today’s Reservations';
        if ($reservationDate != date('Y-m-d')) {
            $data['summary_title'] = 'Reservations for ' . date('M d', strtotime($reservationDate));
        }
        $data['time_zone_date'] = date('Y-m-d');
        $data['date_text'] = date('M d, Y', strtotime($reservationDate));
        $data['reservation_date'] = date('Y-m-d', strtotime($reservationDate));
        return $data;
    }

    public function get($id) {
        $reservationModel = new DashboardReservation();
        $dashboardFunctions = new DashboardFunctions();
        $restId = $dashboardFunctions->getRestaurantId();
        $data = [];
        $data = $reservationModel->getReservationDetailsById($restId, $id);
        $data['status'] = self::$statusArray[$data['status']];
        $data['status'] = ($data['status'] == 'Upcoming') ? "Waiting for confirmation" : $data['status'];
        return $data;
    }

    public function update($id, $data) {
        $reservationModel = new DashboardReservation();
        $dashboardFunctions = new DashboardFunctions();
        $userPoint = new UserPoint();
        $userOrderModel = new DashboardOrder();
        $restId = $dashboardFunctions->getRestaurantId();        
        $this->currentDate = $dashboardFunctions->CityTimeZone();
        $reservationDetails = $reservationModel->getReservationDetailsById($restId, $id);        
        $loyaltyPoints = 0;
        $status = (isset($data['status']) && $data['status'] == 'confirmed') ? 4 : 3;
        if (isset($data['status']) && $data['status'] == 'confirmed') {
            $loyaltyPoints = $reservationModel->updateUserPointsConfirmation($restId, $reservationDetails);
            $updateData['id'] = $id;
            $updateData['status'] = $status;
        } else if (isset($data['status']) && $data['status'] == 'rejected') {
            $loyaltyPoints = 0;
            if (!empty($reservationDetails['user_id'])) {
                $userPoint->updateUserPointStatus($reservationDetails['user_id'], $id);
            }
            if (!empty($data['restaurant_comment'])) {
                $updateData['restaurant_comment'] = $data['restaurant_comment'];
                $restComments = $data['restaurant_comment'];
            } else {
                $restComments = '';
            }
            $updateData['id'] = $id;
            $updateData['status'] = $status;
        } else {
            if (!empty($data['first_name'])) {
                $updateData['first_name'] = $data['first_name'];
            }
            if (!empty($data['last_name'])) {
                $updateData['last_name'] = $data['last_name'];
            }
            if (!empty($data['phone'])) {
                $updateData['phone'] = $data['phone'];
            }
            if (!empty($data['email'])) {
                $updateData['email'] = $data['email'];
            }
            if (!empty($data['requested_seats'])) {
                $updateData['party_size'] = $data['requested_seats'];
                $updateData['reserved_seats'] = $data['requested_seats'];
            }
            if (!empty($data['user_instruction'])) {
                $updateData['user_instruction'] = $data['user_instruction'];
            }
            $updateData['reserved_on'] = $this->currentDate;
            if (!empty($data['date']) && !empty($data['time'])) {
                $updateData['time_slot'] = $data['date'] . " " . $data['time'];
            }
        }
        if ($reservationModel->updateReservation($id, $updateData)) {
            $reservationResponce = $reservationModel->getReservationDetailsById($restId, $id);
            if (!isset($data['status'])) {
                if ($reservationDetails['order_id'] != '') {
                    $updatestatus = $userOrderModel->updateOrderDeliveryitme($reservationDetails['order_id'], $updateData['time_slot']);
                }
                //unset($reservationResponce['pastActivity']);
                $dateObject = new \DateTime($reservationResponce['date_time']);
                $reservationResponce['time_slot'] = $reservationResponce['date_time'];
                $reservationResponce['date'] = $dateObject->format("Y-m-d");
                $reservationResponce['display_date'] = $dateObject->format("m/d/Y");
                $reservationResponce['display_time'] = $dateObject->format("H:i A");
                $reservationResponce['display_date_time'] = $dateObject->format("D, M d, Y");
                $reservationModel->sendReservationModificationMail($reservationResponce, $loyaltyPoints);
                return $reservationResponce;
            }
            //unset($reservationResponce['pastActivity']);
            $dateObject = new \DateTime($reservationResponce['date_time']);
            $reservationResponce['time_slot'] = $reservationResponce['date_time'];
            $reservationResponce['date'] = $dateObject->format("Y-m-d");
            $reservationResponce['display_date'] = $dateObject->format("m/d/Y");
            $reservationResponce['display_time'] = $dateObject->format("H:i A");
            $reservationResponce['display_date_time'] = $dateObject->format("D, M d, Y");
            if ($reservationDetails['user_id']) {
                if (\MCommons\StaticOptions::getPermissionToEmails($reservationDetails['user_id'])) {
                    if ($data['status'] == 'confirmed') {
                        $mailData = $reservationModel->sendReservationConfirmationMail($reservationResponce, $loyaltyPoints);
                    } else {
                        $mailData = $reservationModel->sendReservationCancelMail($reservationResponce, $loyaltyPoints, $restComments);
                    }
                }
            } else {
                if ($data['status'] == 'confirmed') {
                    $mailData = $reservationModel->sendReservationConfirmationMail($reservationResponce, $loyaltyPoints);
                } else {
                    $mailData = $reservationModel->sendReservationCancelMail($reservationResponce, $loyaltyPoints, $restComments);
                }
            }
            $reservationModel->userReservationNotification($reservationResponce, $data['status']);
            return $reservationResponce;
        }
        return ['status' => 'success'];
    }
}
