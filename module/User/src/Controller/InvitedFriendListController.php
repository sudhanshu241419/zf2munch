<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Restaurant\Model\Restaurant;
use Restaurant\Model\RestaurantDetail;
use Home\Model\City;
use User\Model\User;
use User\Model\UserReservation;
use User\Model\UserNotification;
use User\UserFunctions;

class InvitedFriendListController extends AbstractRestfulController {

    public function getList() {
        $userReservationModel = new UserReservation ();
        $userModel = new User ();
        $userfunctions = new UserFunctions();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();

        $userReservationModel->email = $this->getQueryParams('email', '');


        if ($isLoggedIn) {
            $userReservationModel->user_id = $session->getUserId();
            $where = 'userid';
            $reservation = $userReservationModel->getFriendListOfMyReservation($where, $userReservationModel->user_id);
        } else {

            if (empty($userReservationModel->email)) {
                throw new \Exception("Email id is required", 405);
            }

            $column['where'] = array('email' => $userReservationModel->email);
            $where = 'email';
            $reservation = $userReservationModel->getFriendListOfMyReservation($where, $userReservationModel->email);
        }

        if (!$reservation) {
            throw new \Exception('Reservation details not found', 404);
        }

        $reservatioId = array_unique(array_map(function ($i) {
                    return $i['reservation_id'];
                }, $reservation));

        $response['invited_friend_list'] = $this->refineOrder($reservation, $reservatioId);
        return $response;
    }

    private function refineOrder($reservation, $reservatioId) {
        $response = array();
        $orders = array();
        $items = array();
        $orderData = array();
        $index = 0;
        $orderindex = 0;
        $i = 0;

        foreach ($reservatioId as $k => $v) {
            $description = '';
            foreach ($reservation as $key => $value) {
                if ($v == $value['reservation_id']) {

                    $i++;
                    $friendList[$value['reservation_id']]['reservation_detail']['reservation_id'] = $value['reservation_id'];
                    $friendList[$value['reservation_id']]['reservation_detail']['reserved_on'] = $value['reserved_on'];
                    $friendList[$value['reservation_id']]['reservation_detail']['time_slot'] = $value['time_slot'];
                    $friendList[$value['reservation_id']]['reservation_detail']['reserved_seats'] = $value['reserved_seats'];
                    $friendList[$value['reservation_id']]['reservation_detail']['party_size'] = $value['party_size'];
                    $friendList[$value['reservation_id']]['reservation_detail']['status'] = $value['status'];
                    $friendList[$value['reservation_id']]['reservation_detail']['user_instruction'] = $value['user_instruction'];

                    $friendList[$value['reservation_id']]['restaurant_detail']['restaurant_id'] = $value['restaurant_id'];
                    $friendList[$value['reservation_id']]['restaurant_detail']['restaurant_name'] = $value['restaurant_name'];

                    $friendList[$value['reservation_id']]['friend_detail'][$i]['invitation_id'] = $value['invitaion_id'];
                    $friendList[$value['reservation_id']]['friend_detail'][$i]['friend_id'] = $value['friend_id'];
                    $friendList[$value['reservation_id']]['friend_detail'][$i]['friend_email'] = $value['friend_email'];
                }
            }
            $i = 0;
        }

        $key_index = 0;
        foreach ($friendList as $key => $fl) {
            $friendListResponse[$key_index] = $fl;
            $key_index ++;
        }

        return $friendListResponse;
    }

}
