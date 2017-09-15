<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReservation;
use User\UserFunctions;
use User\Model\UserInvitation;

class WebUserHomeReservationController extends AbstractRestfulController {

    public function getList() {
        $response = array();
        $liveRes = "";
        $reject = "";
        // Get reservation data
        $userReservationModel = new UserReservation ();
        $userReservationInviteModel = new UserInvitation ();
        $userFunction = new UserFunctions ();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunction->userCityTimeZone($locationData);
        $type = $this->getQueryParams('type');
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $reservationStatus = isset($config ['constants'] ['reservation_status']) ? $config ['constants'] ['reservation_status'] : array();
        $statusArray = array(
            $reservationStatus ['upcoming']
        );
        /**
         * Get User Reservation count
         */
        if (!empty($type)) {
            if ($type === 'count') {
                $conditions = array(
                    'userId' => $userId,
                    'status' => array(
                        $reservationStatus ['upcoming'],
                        $reservationStatus ['archived'],
                        $reservationStatus ['confirmed'],
                        $reservationStatus ['rejected'],
                        $reservationStatus ['canceled']
                    )
                );
                $totalReservation = $userReservationModel->getTotalReservation($conditions);
                $accepted_invitation = $userFunction->getReservationDetailInvitationAccepted($userId, UserInvitation::ACCEPTED, $currentDate, $reservationStatus);
                if (empty($accepted_invitation)) {
                    return $totalReservation;
                }
                $totalReservation = $totalReservation ['total_reservation'] + count($accepted_invitation);
                return array(
                    'total_reservation' => $totalReservation
                );
            } else {
                throw new \Exception('Type not found', 404);
            }
        }
        $hostRecordCondition = array(
            'userId' => $userId,
            'currentDate' => $currentDate,
            'status' => array(
                $reservationStatus ['upcoming']
            ),
            'orderBy' => 'time_slot ASC'
        );
        /**
         * Get User Current Reservation
         */
        $hostRecord = $userReservationModel->getCurrentReservation($hostRecordCondition);

        if ($hostRecord) {
            $reservationId = $hostRecord ['id'];
            $userIdHostRecord = $hostRecord ['user_id'];

            $invitedRecord = $userReservationInviteModel->getAllUserInvitation(array(
                'where' => array(
                    'user_id' => $userIdHostRecord,
                    'reservation_id' => $reservationId
                )
                    ));
        }

        $recordsCondition = array(
            'userId' => $userId,
            'currentDate' => $currentDate,
            'status' => array(
                $reservationStatus ['upcoming'],
                $reservationStatus['confirmed']
            ),
            'orderBy' => 'time_slot ASC'
        );
        $records = $userReservationModel->getCurrentReservation($recordsCondition);
        /**
         * Get User Current Rejected Reservation
         */
        $rejectedRecordsCondition = array(
            'userId' => $userId,
            'currentDate' => $currentDate,
            'status' => array(
                $reservationStatus ['rejected']
            ),
            'orderBy' => 'time_slot ASC'
        );
        $rejectedRecords = $userReservationModel->getCurrentReservation($rejectedRecordsCondition);
        /**
         * Get User Current Invitation Reservation Accpted/Rejected
         */
        $invitationAdmittedCondtions = array(
            'userId' => $userId,
            'currentDate' => $currentDate,
            'status' => $reservationStatus,
            'msg_status' => array(
                1
            )
        );
        
        $invitationAdmitted = $userFunction->getReservationInvitationAdmitted($invitationAdmittedCondtions);
        $invitationAdmittedFlag = $userFunction->getReservationInvitationAdmitted($invitationAdmittedCondtions, 'flag');

        $invited_activity = false;
        $live_data = false;
        $invited_activity_flag = false;

        if (!empty($invitationAdmitted) && !empty($records)) {

            $invitedTime = $invitationAdmitted ['time_slot'];
            $recordTime = $records ['time_slot'];
            if ($invitedTime < $recordTime) {
                $invited_activity = true;
            } else {
                $liveRes = true;
            }
        } elseif (!empty($records)) {
            $liveRes = true;
        } elseif (!empty($invitationAdmitted)) {
            if ($invitationAdmitted ['status'] != 3)
                $invited_activity = true;
        } elseif (!empty($invitationAdmittedFlag) && !empty($rejectedRecords)) {
            if ($invitationAdmittedFlag ['status'] == 3 && !empty($rejectedRecords)) {
                $rejectedTime = date('Y-m-d H:i:s', strtotime($rejectedRecords ['time_slot']));
                $inviteTime = date('Y-m-d H:i:s', strtotime($invitationAdmittedFlag ['time_slot']));
                if (strtotime($rejectedTime) < strtotime($inviteTime)) {
                    $reject = true;
                } else {
                    $invited_activity_flag = true;
                }
            }
        } elseif (!empty($invitationAdmittedFlag)) {
            if ($invitationAdmittedFlag ['status'] == 3)
                $invited_activity_flag = true;
        } elseif (!empty($rejectedRecords)) {
            $reject = true;
        }
        /**
         * Get User Current Invitation Reservation Requested
         */
        $inviteeResultCondtions = array(
            'userId' => $userId,
            'currentDate' => $currentDate,
            'status' => $reservationStatus,
            'msg_status' => array(
                '0'
            )
        );
        $inviteeResult = $userFunction->getReservationInvitationAdmitted($inviteeResultCondtions);
       
        if (!empty($inviteeResult)) {
            $myReservation = array();
            $myReservation = $userFunction->getHomeReservationDetail($inviteeResult);
            $myReservation ['myreservation_status'] = 'invitation-request';
            return array(
                $myReservation
            );
        } elseif (!empty($invitedRecord)) {
            $myReservation = array();
            $invitaionToFriend = $userFunction->InvitationFriendList($invitedRecord);
            $myReservation = $userFunction->getHomeReservationDetail($hostRecord);
            $myReservation ['invite_status'] = $invitaionToFriend;
            $myReservation ['myreservation_status'] = 'invitation-host';
            return array(
                $myReservation
            );
        }
        if ($invited_activity) {
            $myReservation = array();
            $myReservation = $userFunction->getHomeReservationDetail($invitationAdmitted);
            $myReservation ['myreservation_status'] = 'invitation-status';
            return array(
                $myReservation
            );
        } elseif ($invited_activity_flag) {
            $myReservation = array();
            $myReservation = $userFunction->getHomeReservationDetail($invitationAdmittedFlag);
            $myReservation ['myreservation_status'] = 'invitation-status';
            return array(
                $myReservation
            );
        } elseif ($liveRes) {
            $myReservation = array();
            $myReservation = $userFunction->getHomeReservationDetail($records);
            $myReservation ['myreservation_status'] = 'upcoming';
            return array(
                $myReservation
            );
        } elseif ($reject) {
            $myReservation = array();
            $myReservation = $userFunction->getHomeReservationDetail($rejectedRecords);
            $myReservation ['myreservation_status'] = 'rejected';
            return array(
                $myReservation
            );
        } else {
            $myReservation = array();
            $archieveCondition = array(
                'userId' => $userId,
                'offset' => 0,
                'limit' => 3,
                'currentDate' => $currentDate,
                'orderBy' => 'user_reservations.time_slot desc',
                'status' => array(
                    $reservationStatus ['upcoming'],
                    $reservationStatus ['archived'],
                    $reservationStatus ['rejected']
                )
            );
            $myReservation = $userReservationModel->getReservationDetails($archieveCondition);
            $reservationArchiveData = array();
            foreach ($myReservation as $key => $value) {
                $value ['time_slot'] = date('M d, Y', strtotime($value ['time_slot']));
                if ($value['is_reviewed'] == 0) {
                    $value['is_reviewed'] = "";
                }
                $reservationArchiveData [] = $value;
            }
            return $reservationArchiveData;
        }
    }

}
