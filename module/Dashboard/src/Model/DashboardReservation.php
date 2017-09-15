<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use \MCommons\StaticOptions;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Where;
use Dashboard\DashboardFunctions;

class DashboardReservation extends AbstractModel {

    public $id;
    public $user_id;
    public $restaurant_id;
    public $seat_type_id;
    public $party_size;
    public $reserved_on;
    public $user_instruction;
    public $restaurant_comment;
    public $time_slot;
    public $meal_slot;
    public $status;
    public $restaurant_name;
    public $first_name;
    public $last_name;
    public $phone;
    public $email;
    public $reserved_seats;
    public $receipt_no;
    public $is_reviewed = 0;
    public $host_name;
    public $user_ip;
    protected $_db_table_name = 'Dashboard\Model\DbTable\DashboardReservationTable';
    protected $_primary_key = 'id';
    public $order_id;
    public $city_id = NULL;
    public $is_modify = 0;

    const UPCOMING = 1;
    const ARCHIVED = 0;
    const CANCELED = 2;
    const REJECTED = 3;
    const CONFIRMED = 4;

    static $reserveStatus = [self::UPCOMING, self::REJECTED, self::CONFIRMED, self::ARCHIVED];

//    public function updateReservation() {
//        $data = array(
//            'first_name' => $this->first_name,
//            'last_name' => $this->last_name,
//            'email' => $this->email,
//            'phone' => $this->phone,
//            'party_size' => $this->party_size,
//            'reserved_seats' => $this->reserved_seats,
//            'reserved_on' => $this->reserved_on,
//            'time_slot' => $this->time_slot
//        );
//
//        $writeGateway = $this->getDbTable()->getWriteGateway();
//        $dataUpdated = array();
//        if ($this->id == 0) {
//            throw new \Exception("Invalid reservation ID provided", 500);
//        } else {
//            $dataUpdated = $writeGateway->update($data, array(
//                'id' => $this->id
//            ));
//        }
//
//        if (!$dataUpdated) {
//            throw new \Exception("Data Not Updated", 424);
//        }
//
//        return $this->toArray();
//    }

    public function getReservation($restaurantId, $type, $orderby = false) {
        $output = array();
        // print_r($status);die;
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->where->equalTo('restaurant_id', $restaurantId);
        //$select->where->greaterThan ( 'time_slot', $options ['currentDate'] );        
        $select->where->in('status', array(1, 4));
        $select->order('time_slot ASC');

        if ($type === "current") {
            $select->limit(3);
        }

        $currentReservation = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();

        if (!empty($currentReservation)) {
            $this->prepairReservation($currentReservation);
            $output = $currentReservation;
        }
        return $output;
    }

    public function updateReservation($id, $data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $dataUpdated = array();
        if ($id == 0) {
            throw new \Exception("Invalid reservation ID provided", 500);
        } else {
            $dataUpdated = $writeGateway->update($data, array(
                'id' => $id
            ));
        }
        if ($dataUpdated) {
            return true;
        } else {
            return false;
        }
    }

    public function update($data) {
        $this->getDbTable()->getWriteGateway()->update($data, array(
            'id' => $this->id
        ));
        return true;
    }

    public function getUpcomingReservations($restId, $date) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'slot' => new Expression('DATE(time_slot)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('status', '1');
        $where->greaterThan($left, $right);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $reservations = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $reservations;
    }

    public function getTotalReservationsAndSeats($restId, $date) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_reservations' => new Expression('COUNT(id)'),
            'guests' => new Expression('SUM(reserved_seats)')
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->in('status', self::$reserveStatus);
        $where->like('time_slot', '%' . $date . '%');
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservations = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($reservations)) {
            return $reservations[0];
        } else {
            $reservations['total_reservations'] = 0;
            $reservations['guests'] = 0;
            return $reservations;
        }
    }

    public function getTotalUpcomingReservations($restId, $date) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_reservations' => new Expression('COUNT(id)')
        ));
        $select->where->equalTo('restaurant_id', $restId);
        $select->where(new \Zend\Db\Sql\Predicate\Expression('DATE(time_slot) > ?', $date));
        $select->where->in('status', self::$reserveStatus);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservations = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($reservations)) {
            return $reservations[0]['total_reservations'];
        } else {
            $reservations['total_reservations'] = 0;
            return $reservations;
        }
    }

    public function getTotalActiveReservations($restaurantId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_reservations' => new Expression('COUNT(id)')
        ));

        $select->where(array(
            'restaurant_id' => $restaurantId
        ));
        $select->where->in('status', array(1, 4));

        //var_dump($select->getSqlString($this->getPlatform('READ')));

        $totalOrder = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $totalOrder->toArray();
    }

    public function getAllReservations($restId, $date, $limit = false, $start = false, $archive = false) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        if ($archive == 1) {
            $where->equalTo('status', 0);
            $select->order('time_slot desc');
            $select->limit($limit)->offset($start);
        } else {
            $where->in('status', self::$reserveStatus);
            $where->like('time_slot', '%' . $date . '%');
        }
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservations = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($reservations)) {
            foreach ($reservations as $key => $value) {
                $date1 = date('Y-m-d', strtotime($value['time_slot']));
                $date2 = date('Y-m-d');
                $reservations[$key]['reserved_on'] = date('M d, Y', strtotime($value['reserved_on']));
                $reservations[$key]['time_slot'] = ($date1 != $date2) ? date('M d @ h:i A', strtotime($value['time_slot'])) : date('h:i A', strtotime($value['time_slot']));
                $reservations[$key]['today_time'] = date('M d, g:iA', strtotime($value['time_slot']));
                $reservations[$key]['res_flag'] = $this->getReservationFlag($restId, $value['id'], $value['time_slot'], $value['reserved_seats'], $value['status']);
                $reservations[$key]['time_slot_readable'] = ($date1 != $date2) ? date('M d @ h:i A', strtotime($value['time_slot'])) : date('h:i A', strtotime($value['time_slot']));
                $reservations[$key]['statusClass'] = $this->getStatus($value['status']);
                $reservations[$key]['newcustomer'] = ($value['user_id'] == null || $value['user_id'] == '') ? "New Customer" : $this->getNewVsReturningCustomer($restId, $value['user_id'], $value['email']);
                $reservations[$key]['user_instruction'] = $value['user_instruction'];
            }
            return $reservations;
        } else {
            return $reservations = [];
        }
    }

    public function getAllUpcomingReservations($restId, $date) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $select->where->equalTo('restaurant_id', $restId);
        $select->where(new \Zend\Db\Sql\Predicate\Expression('DATE(time_slot) > ?', $date));
        $select->where->in('status', self::$reserveStatus);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservations = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($reservations)) {
            foreach ($reservations as $key => $value) {
                $date1 = date('Y-m-d', strtotime($value['time_slot']));
                $date2 = date('Y-m-d');
                $reservations[$key]['reserved_on'] = date('M d, Y', strtotime($value['reserved_on']));
                $reservations[$key]['time_slot'] = ($date1 != $date2) ? date('M d @ h:i A', strtotime($value['time_slot'])) : date('h:i A', strtotime($value['time_slot']));
                $reservations[$key]['today_time'] = date('M d, g:iA', strtotime($value['time_slot']));
                $reservations[$key]['res_flag'] = $this->getReservationFlag($restId, $value['id'], $value['time_slot'], $value['reserved_seats'], $value['status']);
                $reservations[$key]['time_slot_readable'] = ($date1 != $date2) ? date('M d @ h:i A', strtotime($value['time_slot'])) : date('h:i A', strtotime($value['time_slot']));
                $reservations[$key]['statusClass'] = $this->getStatus($value['status']);
                $reservations[$key]['newcustomer'] = ($value['user_id'] == null || $value['user_id'] == '') ? "New Customer" : $this->getNewVsReturningCustomer($restId, $value['user_id'], $value['email']);
                $reservations[$key]['user_instruction'] = $value['user_instruction'];
            }
            return $reservations;
        } else {
            return $reservations = [];
        }
    }

    public function getReservationDetailsById($restId, $reservationId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            '*'
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('id', $reservationId);
        $where->in('status', self::$reserveStatus);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $reservations = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        if (!empty($reservations)) {
            $userModel = new User();
            foreach ($reservations as $key => $value) {
                $date1 = date('Y-m-d', strtotime($value['time_slot']));
                $date2 = date('Y-m-d');
                $reservations[$key]['readflag'] = ($value['is_read'] == 0) ? 1 : 0;
                $reservations[$key]['is_read'] = ($value['is_read'] == 0) ? 1 : $value['is_read'];
                $email = iconv('CP1252', 'UTF-8', $value['email']);
                $reservations[$key]['email'] = $email;
                $reservations[$key]['phone'] = $this->formatPhoneNumber($value['phone']);
                $reservations[$key]['edit_phone'] = $value['phone'];
                $reservations[$key]['reserved_on'] = date('M d, Y', strtotime($value['reserved_on']));
                $reservations[$key]['time_slot'] = ($date1 != $date2) ? date('M d @ h:i A', strtotime($value['time_slot'])) : date('h:i A', strtotime($value['time_slot']));
                $reservations[$key]['reservation_date_time'] = ($date1 != $date2) ? date('M d @ h:i A', strtotime($value['time_slot'])) : "Today " . date('h:i A', strtotime($value['time_slot']));
                $reservations[$key]['statusClass'] = ($value['status'] != 1) ? $this->getStatus($value['status']) : "Waiting for confirmation";
                $reservations[$key]['newcustomer'] = ($value['user_id'] == null || $value['user_id'] == '') ? "New Customer" : $this->getNewVsReturningCustomer($restId, $value['user_id'], $value['email']);
                $reservations[$key]['user_instruction'] = $value['user_instruction'];
                $reservations[$key]['time_zone'] = date('Y-m-d H:i:s');
                $reservations[$key]['date_time'] = $value['time_slot'];
                $reservations[$key]['display_time'] = date('h:i A', strtotime($value['time_slot']));
                $reservations[$key]['date'] = $date1;
                $reservations[$key]['restaurant_comment'] = ($value['restaurant_comment'] != '') ? trim(strip_tags(json_encode(utf8_encode($value['restaurant_comment']))), '"') : "";
                $reservations[$key]['userPicture'] = $userModel->getUserPicture($value['user_id']);
                $reservations[$key]['userDefaultPicture'] = $userModel->getUserDefaultPicture($value['user_id']);
                $reservations[$key]['pastActivity'] = $this->getUserPastActivities($value['user_id'], $restId, $email);
            }
            return $reservations[0];
        } else {
            return $reservations = [];
        }
    }

    public static function getStatus($status) {
        switch ($status) {
            case 0:
                $varStatusClass = "archived";
                break;
            case 1:
                $varStatusClass = "upcoming";
                break;
            case 2:
                $varStatusClass = "canceled";
                break;
            case 3:
                $varStatusClass = "rejected";
                break;
            case 4:
                $varStatusClass = "confirmed";
                break;
        }
        return $varStatusClass;
    }

    public function getSlotOrangeRed($restId, $date, $sTime, $eTime, $flag = null) {
        $timeS_object = new \DateTime($date . ' ' . $sTime);
        $timeS = date_format($timeS_object, 'Y-m-d H:i:s');
        $timeE_object = new \DateTime($date . ' ' . $eTime);
        $timeE = date_format($timeE_object, 'Y-m-d H:i:s');
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('reservation_id' => 'id'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('status', self::UPCOMING);
        $where->between('time_slot', $timeS, $timeE);
        $select->where($where);
        $select->order('reserved_on desc');
        $select->limit(1);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        //pr($records,1);
        if ($records) {
            return $records;
        } else {
            return array();
        }
    }

    public function getReservationFlag($restId, $id, $timeslot, $reservedSeats, $status) {
        $flag = 0;
        $tot = "";
        $tot1 = "";
        $tot2 = "";
        $tot3 = "";
        $tot4 = 0;
        $restModel = new Restaurant();
        $totalSeats = $restModel->getRestaurantTotalSeats($restId);
        $slot1 = date('H:i:s', strtotime($timeslot));
        $date = date('Y-m-d', strtotime($timeslot));
        $breakfastActive = $this->getSlotOrangeRed($restId, $date, '05:00:00', '12:00:00');
        $lunchActive = $this->getSlotOrangeRed($restId, $date, '12:00:01', '17:00:00');
        $dinnerActive = $this->getSlotOrangeRed($restId, $date, '05:00:00', '17:00:00', 'dinner');
        if ($slot1 >= '05:00:00' && $slot1 <= '12:00:00') {
            if ($reservedSeats) {
                if ($status == 1 || $status == 4)
                    $tot += $reservedSeats;
            }
            if ($breakfastActive) {
                if ($breakfastActive['reservation_id'] == $id) {
                    if ($tot >= $totalSeats)
                        $flag = 'redAlertIcon';
                    elseif ($tot >= ($totalSeats * 80 / 100))
                        $flag = 'orgAlertIcon';
                    else
                        $flag = 0;
                }
                else {
                    $flag = 0;
                }
            } else {
                $flag = 0;
            }
        } elseif ($slot1 >= '12:00:01' && $slot1 <= '17:00:00') {

            if ($reservedSeats) {
                if ($status == 1 || $status == 4)
                    $tot4 += $reservedSeats;
            }
            if ($lunchActive) {
                if ($lunchActive['reservation_id'] == $id) {
                    if ($tot4 >= $totalSeats)
                        $flag = 'redAlertIcon';
                    elseif ($tot4 >= ($totalSeats * 80 / 100))
                        $flag = 'orgAlertIcon';
                    else
                        $flag = 0;
                }
                else {
                    $flag = 0;
                }
            } else {
                $flag = 0;
            }
        }
        if (($slot1 >= '17:00:01' && $slot1 <= '23:59:59') || ($slot1 >= '00:00:00' && $slot1 <= '04:59:59' )) {
            if ($slot1 >= '17:00:01' && $slot1 <= '23:59:59') {
                if ($reservedSeats) {
                    if ($status == 1 || $status == 4)
                        $tot1 += $reservedSeats;
                }
            }
            elseif ($slot1 >= '00:00:00' && $slot1 <= '04:59:59') {
                if ($reservedSeats) {
                    if ($status == 1 || $status == 4)
                        $tot2 += $reservedSeats;
                }
            }
            if ($tot1 || $tot2) {
                $tot3 = $tot1 + $tot2;
                if ($dinnerActive) {
                    if ($dinnerActive['reservation_id'] == $id) {
                        if ($tot3 >= $totalSeats)
                            $flag = 'redAlertIcon';
                        elseif ($tot3 >= ($totalSeats * 80 / 100))
                            $flag = 'orgAlertIcon';
                        else
                            $flag = 0;
                    }
                    else {
                        $flag = 0;
                    }
                } else {
                    $flag = 0;
                }
            } else {
                $flag = 0;
            }
        }
        return $flag;
    }

    public function getNewVsReturningCustomer($restId, $userId, $email) {
        $reservations = $this->getTotalUserReservations($restId, $userId, $email);
        $orderModel = new DashboardOrder();
        $orders = $orderModel->getTotalUserOrder($userId, $restId, $email);
        $orders = ($orders) ? $orders[0]['total_order'] : 0;
        $total = $reservations + $orders;
        if ($total > 1) {
            return "Returning Customer";
        } else {
            return "New Customer";
        }
    }

    public function getTotalUserReservations($restId, $userId, $email) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_reservations' => new Expression('COUNT(id)'),
        ));
        $select->where->nest->equalTo('user_id', $userId)->or->equalTo('email', $email)->unnest->and->equalTo('restaurant_id', $restId);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        if (!empty($data)) {
            return $data['total_reservations'];
        } else {
            return 0;
        }
    }

    public function getTotalRestaurantReservations($restaurantId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_reservation' => new Expression('COUNT(id)')
        ));

        $config = StaticOptions::getServiceLocator()->get('config');
        $upcoming = $config['constants']['reservation_status']['upcoming'];
        $confirmed = $config['constants']['reservation_status']['confirmed'];
        $select->where(array(
            'restaurant_id' => $restaurantId, 'status' => array($upcoming, $confirmed)
        ));

        //var_dump($select->getSqlString($this->getPlatform('READ')));

        $totalOrder = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $totalOrder->toArray();
    }

    public function formatPhoneNumber($num) {
        if (!empty($num)) {
            $num = preg_replace('/[^0-9]/', '', $num);
            $len = strlen($num);
            if ($len == 7)
                $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
            elseif ($len == 10)
                $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);
            return $num;
        }else {
            return $num;
        }
    }

    public function getUserPastActivities($userId, $restId, $email) {
        $totalReservations = $this->getTotalUserReservations($restId, $userId, $email);
        $orderModel = new DashboardOrder();
        $reviewModel = new UserReview();
        $checkinModel = new UserCheckin();
        $orders = $orderModel->getTotalUserOrder($userId, $email, $restId);
        $totalOrders = ($orders) ? $orders[0]['total_order'] : 0;
        $checkins = $checkinModel->getTotalUsercheckin($userId, $restId);
        $totalCheckins = ($checkins) ? $checkins[0]['total_checkin'] : 0;
        $totalevReviews = $reviewModel->getTotalUserReviews($restId, $userId);
        $data['totalorder'] = $totalOrders;
        $data['totalreservation'] = $totalReservations;
        $data['totalcheckin'] = $totalCheckins;
        $data['totalreview'] = $totalevReviews;
        return $data;
    }

    public function updateUserPointsConfirmation($restId, $data) {
        $pointSourceModel = new PointSourceDetails();
        $restServerModel = new RestaurantServer();
        $userPoint = new UserPoint();
        $user = new User();
        $insertData = [];
        $pointSource = $pointSourceModel->getPoint(array(PointSourceDetails::RESERVE_A_TABLE));
        $reservationPoints = $pointSource[0]['points'];
        $firstReservationPoint = 0;
        if ($data['user_id'] != null && $data['user_id'] != '') {
            $userWithDineMore = $restServerModel->userRegisterWithDineAndMore($restId, $data['user_id']);
            //$userWithDineMore[0]['count'] = 1;
            if ($userWithDineMore > 0) {
                $firstReservation = $this->checkUserFirstReservation($data['user_id']);
                // $firstReservation = 1;
                if ($firstReservation == 1) {
                    $earlyBird = $restServerModel->earlyBirdSpecial($data['user_id'], $restId, $data['time_slot'], PointSourceDetails::EARLY_BIRD_SPECIAL_DAYS);
                    if ($earlyBird) {
                        $insertData['user_id'] = $data['user_id'];
                        $insertData['restaurant_id'] = $restId;
                        $insertData['point_source'] = PointSourceDetails::RESERVE_A_TABLE;
                        $insertData['points'] = PointSourceDetails::DINE_MORE_EARLY_BIRD_POINT;
                        $insertData['created_at'] = date("Y-m-d H:i:s");
                        $insertData['status'] = 1;
                        $insertData['points_descriptions'] = "Bonus points for you at " . $data['restaurant_name'] . " for making a reservation during your first 30 days with Dine & More";
                        $insertData['ref_id'] = $data['id'];
                        $userPoint->save(0, $insertData);
                        $firstReservationPoint += PointSourceDetails::DINE_MORE_EARLY_BIRD_POINT;
                    }
                }
                $insertData['user_id'] = $data['user_id'];
                $insertData['restaurant_id'] = $restId;
                $insertData['point_source'] = PointSourceDetails::RESERVE_A_TABLE;
                $insertData['points'] = PointSourceDetails::DINE_MORE_RESERVATION_POINT;
                $insertData['created_at'] = date("Y-m-d H:i:s");
                $insertData['status'] = 1;
                $insertData['points_descriptions'] = "You have upcoming plans! This calls for a celebration, here are " . PointSourceDetails::DINE_MORE_RESERVATION_POINT . " points!";
                $insertData['ref_id'] = $data['id'];
                $userPoint->save(0, $insertData);
                $firstReservationPoint += PointSourceDetails::DINE_MORE_RESERVATION_POINT;
            } else {
                $insertData['user_id'] = $data['user_id'];
                $insertData['restaurant_id'] = $restId;
                $insertData['point_source'] = PointSourceDetails::RESERVE_A_TABLE;
                $insertData['points'] = $reservationPoints;
                $insertData['created_at'] = date("Y-m-d H:i:s");
                $insertData['status'] = 1;
                $insertData['points_descriptions'] = "You have upcoming plans! This calls for a celebration, here are " . $reservationPoints . " points!";
                $insertData['ref_id'] = $data['id'];
                $userPoint->save(0, $insertData);
                $firstReservationPoint += $reservationPoints;
            }
            $user->updateUserPoints($data['user_id'], $data['id'], $firstReservationPoint, $reservationPoints);
        }
        return $firstReservationPoint;
    }

    public function checkUserFirstReservation($userId) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_reservations' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('user_id', $userId);
        $where->equalTo('status', self::CONFIRMED);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        if (!empty($data)) {
            return $data['total_reservations'];
        } else {
            return 0;
        }
    }

    public function prepairReservation(&$currentReservation) {
        foreach ($currentReservation as $key => $val) {
            $currentReservation[$key]['type'] = ($val['order_id']) ? "Prepaid" : "Normal";
            $currentReservation[$key]['seats'] = $val['reserved_seats'];
            $partyTime = date("H:i A", strtotime($val['time_slot'])) == "00:00 AM" ? "12:00 AM" : date('H:i A', strtotime($val['time_slot']));
            $currentReservation[$key]['party_time'] = $partyTime;
        }
    }

    public function userReservationNotification($reservationDetail, $status) {
        $responce = $this->userNotificationMsg($status, $reservationDetail);
        $userMessage = $responce['userMessage'];
        $dashboardMessage = $responce['dashboardMessage'];
        $pubnubInfo = array("user_id" => $reservationDetail['user_id'], "restaurant_id" => $reservationDetail['restaurant_id'], "restaurant_name" => $reservationDetail['restaurant_name']);
        $currDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $reservationDetail['restaurant_id']
        ));
        $userNotificationArray = array(
            "msg" => $userMessage,
            "channel" => "mymunchado_" . $reservationDetail['user_id'],
            "userId" => $reservationDetail['user_id'],
            "type" => 'reservation',
            "restaurantId" => $reservationDetail['restaurant_id'],
            'restaurantName' => $reservationDetail['restaurant_name'],
            'reservationStatus' => $reservationDetail['status'],
            'firstName' => $reservationDetail['first_name'],
            'isFriend' => 0,
            'curDate' => $currDateTime->format(StaticOptions::MYSQL_DATE_FORMAT),
        );
        $dashboardNotificationArray = array(
            "msg" => $dashboardMessage,
            "channel" => "dashboard_" . $reservationDetail['restaurant_id'],
            "userId" => $reservationDetail['user_id'],
            "type" => 'reservation',
            "restaurantId" => $reservationDetail['restaurant_id'],
            'restaurantName' => $reservationDetail['restaurant_name'],
            'reservationStatus' => $reservationDetail['status'],
            'firstName' => $reservationDetail['first_name'],
            'isFriend' => 0,
            'curDate' => $currDateTime->format(StaticOptions::MYSQL_DATE_FORMAT),
        );
        $userNotificationModel = new \Dashboard\Model\UserNotification();
        $userNotificationModel->createPubNubNotification($userNotificationArray, $pubnubInfo);
        \MCommons\StaticOptions::pubnubPushNotification($userNotificationArray);
        $userNotificationModel->createPubNubNotification($dashboardNotificationArray, $pubnubInfo);
        \MCommons\StaticOptions::pubnubPushNotification($dashboardNotificationArray);
        $this->sendFriendNotification($reservationDetail, $userNotificationArray);
        return 'success';
    }

    public function sendFriendNotification($reservationDetail, $userNotificationArray) {
        $userInvitationModel = new UserReservationInvitation();
        $userModel = new User();
        $userInvitees = $userInvitationModel->getUserInvitations($reservationDetail['id'], $flag = 1);
        if (!empty($userInvitees)) {
            foreach ($userInvitees as $key => $value) {
                $userId = $value['user_id'];
                $friendId = $value['to_id'];
                if (!empty($friendId)) {
                    $userName = $userModel->getUserName($userId);
                    $friendName = $userModel->getUserName($friendId);
                    $responce = $this->friendNotificationMsg($reservationDetail['status'], $userName, $friendName, $friendId, $reservationDetail);
                    $pubnubInfo = $responce['pubnubInfo'];
                    $userNotificationArray ['user_id'] = $friendId;
                    $userNotificationArray ['channel'] = "mymunchado_" . $friendId;
                    $userNotificationArray ['first_name'] = $userName;
                    $userNotificationArray['msg'] = $responce['msg'];
                }
                $userNotificationModel = new \Dashboard\Model\UserNotification();
                $userNotificationModel->createPubNubNotification($userNotificationArray, $pubnubInfo);
                \MCommons\StaticOptions::pubnubPushNotification($userNotificationArray);
            }
        }
    }

    public function userNotificationMsg($status, $reservationDetail) {
        $userMessage = '';
        $dashboardMessage = '';
        switch ($status) {
            case 'upcoming':
                $userMessage = ucwords($reservationDetail['restaurant_name']) . " had to modify your reservation. Does it still work for you?";
                $dashboardMessage = "Your customer needs to update their reservation No " . $reservationDetail['receipt_no'];
                if ($reservationDetail['order_id'] != '') {
                    $userMessage = ucwords($reservationDetail['restaurant_name']) . " made a change to your pre-paid reservation. Check it out.";
                }
                break;
            case 'confirmed':
                $userMessage = "Reservation: officially reserved, at " . $reservationDetail['restaurant_name'] . " Good get";
                $dashboardMessage = "You've confirmed reservation number: " . $reservationDetail['receipt_no'];
                if ($reservationDetail['is_modify'] == 1) {
                    $userMessage = $reservationDetail['restaurant_name'] . " approved the changes to your reservation. Congrats!";
                    $dashboardMessage = "You've approved a modification to reservation No. " . $reservationDetail['receipt_no'];
                }
                if ($reservationDetail['order_id'] != '') {
                    $userMessage = "Your pre-paid reservation was successfully reserved at " . $reservationDetail['restaurant_name'] . ". Way to go!";
                    $dashboardMessage = "You've confirmed pre-paid reservation number: " . $reservationDetail['receipt_no'];
                }
                break;
            case 'rejected':
                $userMessage = "We're sorry, " . $reservationDetail['restaurant_name'] . " canceled your reservation. Don&#8217;t worry, there are plenty of other places around town waiting for you to warm their seats.";
                $dashboardMessage = "You'successfully rejected reservation No. " . $reservationDetail['receipt_no'];
                if ($reservationDetail['order_id'] != '') {
                    $userMessage = "We're sorry, " . $reservationDetail['restaurant_name'] . " canceled your pre-paid reservation. Don't worry, there are plenty of other places around town waiting for you to warm their seats.";
                    $dashboardMessage = "You've successfully rejected pre-paid reservation No. " . $reservationDetail['receipt_no'];
                }
                break;
        }
        return ['userMessage' => $userMessage, 'dashboardMessage' => $dashboardMessage];
    }

    public function friendNotificationMsg($status, $userName, $friendName, $friendId, $reservationDetail) {
        $friendMessage = '';
        $pubnubInfo = [];
        switch ($status) {
            case 'upcoming':
                $friendMessage = "Hey " . ucwords($friendName) . "! The reservation details at " . ucwords($reservationDetail['restaurant_name']) . " have changed. Reconfirm if you&#8217;re going or let them know if you're bailing.";
                if ($reservationDetail['order_id'] != '') {
                    $friendMessage = ucwords($userName) . " changed your pre-paid reservation at " . ucwords($reservationDetail['restaurant_name']) . ". Check it.";
                }
                $pubnubInfo = json_encode(array("user_id" => $friendId, "restaurant_id" => $reservationDetail['restaurant_id'], "restaurant_name" => $reservationDetail['restaurant_name'], "reservation_id" => $reservationDetail['id']));
                break;
            case 'confirmed':
                $friendMessage = ucwords($reservationDetail['restaurant_name']) . " approved the changes to your reservation. Congrats!";
                if ($reservationDetail['order_id'] != '') {
                    $friendMessage = ucwords($userName) . " changed your pre-paid reservation at " . ucwords($reservationDetail['restaurant_name']) . ". Check it.";
                }
                $pubnubInfo = json_encode(array("user_id" => $friendId, "restaurant_id" => $reservationDetail['restaurant_id'], "restaurant_name" => $reservationDetail['restaurant_name'], "reservation_id" => $reservationDetail['id']));
            case 'rejected':
                $friendMessage = "How Rood! " . ucwords($reservationDetail['restaurant_name']) . " canceled ." . ucwords($userName) . "'s reservation. Maybe you guys can try a different spot.";
                $pubnubInfo = json_encode(array("user_id" => $friendId, "userName" => $userName, "restaurant_id" => $reservationDetail['restaurant_id'], "restaurant_name" => $reservationDetail['restaurant_name'], "reservation_id" => $reservationDetail['id']));
                break;
        }
        return ['msg' => $msg, 'pubnubInfo' => $pubnubInfo];
    }

    public function sendStagTableConfirmationMail($reservationDetails, $loyaltyPoints = 0) {
        $dashboardFunctions = new DashboardFunctions();
        $dineinFunctions = new \Restaurantdinein\RestaurantdineinFunctions();
        $sl = \MCommons\StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $dateTimeObject = new \DateTime($reservationDetails['reservation_date']);
        $restModel = new Restaurant();
        $options = array(
            'id' => $reservationDetails['restaurant_id'],
        );
        $restDetail = $restModel->findByRestaurantId($options);
        $username = $this->getUsername($reservationDetails);
        //$restAddress =  $restDetail['address'] . ", " . $restDetail['city_name'] . ", " . $restDetail['state_code'] . ", " . $restDetail['zipcode'];
        $restAddress = '<tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_westvillage.jpg" alt="Aria hell\'s kitchen 369 W 51st St, New York, NY 10019 b/t 9th Ave & 8th Ave" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr><tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_hellkitchen.jpg" alt="Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr>';
        $layout = "email-layout/default_new";
        $template = CONFIRM_RESERVATION;
        $subject = sprintf(SUBJECT_CONFIRM_RESERVATION, $reservationDetails['restaurant_name']);
        //$timeToReservation = $dineinFunctions->holdTableDateTime($reservationDetails['hold_time'], $reservationDetails['reservation_date']);
        $dateTimeObjectTable = new \DateTime($reservationDetails['hold_table_time']);
        if (!empty($reservationDetails)) {
            $variables = array(
                'restaurant_name' => iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']),
                'number_of_people' => $reservationDetails['seats'],
                'reserved_date' => $dateTimeObject->format("D, M d, Y"),
                'reserved_time' => $dateTimeObject->format("h:i A"),
                'reserved_date_to' => $dateTimeObjectTable->format("D, M d, Y"),
                'reserved_time_to' => $dateTimeObjectTable->format("h:i A"),
                'user_name' => ucfirst($username),
                'address' => $restAddress,
                'order_url' => $webUrl . '/order',
                'reserve_url' => $webUrl . '/reservation',
                'loyalty_points' => $loyaltyPoints,
                'instruction' => $reservationDetails['user_instruction'],
                'reply_to' => DASHBOARD_EMAIL_FROM,
                'template_img_path' => MAIL_IMAGE_PATH,
                'reserved_seats' => $reservationDetails['seats'],
                'phone_no' => $reservationDetails['phone'],
                'facebook_url' => $restDetail->facebook_url,
                'instagram_url' => $restDetail->instagram_url,
                'receivedDate' => $dateTimeObject->format("D, M d, Y"),
                'receivedTime' => $dateTimeObject->format("h:i A"),
                'receipt_number' => $reservationDetails['booking_id'],
                'res_address' => str_replace('"', '', $reservationDetails['address']),
            );
            $data = array(
                'to' => array($reservationDetails['email']),
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
        }
        $dashboardFunctions->sendMails($data);
    }

    public function sendReservationConfirmationMail($reservationDetails, $loyaltyPoints = 0) {
        $dashboardFunctions = new DashboardFunctions();
        $sl = \MCommons\StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $dateTimeObject = new \DateTime($reservationDetails['date_time']);
        $restModel = new Restaurant();
        //$options = array('columns'=>array('restaurant_logo_name','facebook_url','instagram_url','twitter_url'),'where'=>array('id' => $reservationDetails['restaurant_id']));
        $restDetail = $restModel->getRestaurantDetails($reservationDetails['restaurant_id']);        
        $restaurantAddress = $restModel->restaurantAddress($reservationDetails['restaurant_id']);
        $username = $this->getUsername($reservationDetails);       
        $restAddress = '<tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_westvillage.jpg" alt="Aria hell\'s kitchen 369 W 51st St, New York, NY 10019 b/t 9th Ave & 8th Ave" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr><tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_hellkitchen.jpg" alt="Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr>';
        if($reservationDetails['host_name']==PROTOCOL.SITE_URL){
            $layout = "email-layout/default_new";
            $template = CONFIRM_RESERVATION_TEMPLATES;
            $subject = sprintf(SUBJECT_RESERVATION_CONFIRM_, $reservationDetails['restaurant_name']);
        }else{
            $layout = "email-layout/ma_default";
            $template = "ma_micro_reservation_confirm";
            $subject = "We Can Squeeze You In at " .$reservationDetails['restaurant_name']."!";
        }
        //$timeToReservation = $dineinFunctions->holdTableDateTime($reservationDetails['hold_time'], $reservationDetails['reservation_date']);
        if (!empty($reservationDetails)) {
            $variables = array(
                'restaurant_name' => iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']),
                'restaurant_logo'=>$restDetail['restaurant_logo_name'],
                'map_address'=>iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']).", ". $restaurantAddress,
                'restaurant_address'=>$restaurantAddress,
                'number_of_people' => $reservationDetails['reserved_seats'],
                'reserved_date' => $dateTimeObject->format("D, M d, Y"),
                'reserved_time' => $dateTimeObject->format("h:i A"),
                'user_name' => ucfirst($username),
                'address' => $restAddress,
                'order_url' => $webUrl . '/order',
                'reserve_url' => $webUrl . '/reservation',
                'loyalty_points' => $loyaltyPoints,
                'instruction' => $reservationDetails['user_instruction'],
                'reply_to' => DASHBOARD_EMAIL_FROM,
                'template_img_path' => MAIL_IMAGE_PATH,
                'reserved_seats' => $reservationDetails['reserved_seats'],
                'phone_no' => $reservationDetails['phone'],
                'facebook_url' => $restDetail['facebook_url'],
                'instagram_url' => $restDetail['instagram_url'],
                'twitter_url' => $restDetail['twitter_url'],
                'receivedDate' => $dateTimeObject->format("D, M d, Y"),
                'receivedTime' => $dateTimeObject->format("h:i A"),
                'receipt_number' => $reservationDetails['receipt_no'],
                'status'=>"confirmed",
                'host_name'=>$reservationDetails['host_name'],
                'rest_code'=>$restDetail['rest_code']
                    //'res_address' => str_replace('"', '', $reservationDetails['address']),
            );
            $data = array(
                'to' => array($reservationDetails['email']),
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
        }
        $dashboardFunctions->sendMails($data);
    }

    public function sendStagTableModificationMail($reservationDetails, $loyaltyPoints = 0) {
        $dashboardFunctions = new DashboardFunctions();
        $dineinFunctions = new \Restaurantdinein\RestaurantdineinFunctions();
        $sl = \MCommons\StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $dateTimeObject = new \DateTime($reservationDetails['reservation_date']);
        $restModel = new Restaurant();
        $options = array(
            'id' => $reservationDetails['restaurant_id'],
        );
        $restDetail = $restModel->findByRestaurantId($options);
        $username = $this->getUsername($reservationDetails);
        //$restAddress =  $restDetail['address'] . ", " . $restDetail['city_name'] . ", " . $restDetail['state_code'] . ", " . $restDetail['zipcode'];
        $restAddress = '<tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_westvillage.jpg" alt="Aria hell\'s kitchen 369 W 51st St, New York, NY 10019 b/t 9th Ave & 8th Ave" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr><tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_hellkitchen.jpg" alt="Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr>';
        $layout = "email-layout/default_new";
        $template = RESERVATION_MODIFICATION;
        $subject = sprintf(SUBJECT_RESERVATION_MODIFICATION, $reservationDetails['restaurant_name']);
        //$timeToReservation = $dineinFunctions->holdTableDateTime($reservationDetails['hold_time'], $reservationDetails['reservation_date']);
        $dateTimeObjectTable = new \DateTime($reservationDetails['hold_table_time']);
        if (!empty($reservationDetails)) {
            $variables = array(
                'restaurant_name' => iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']),
                'number_of_people' => $reservationDetails['seats'],
                'reserved_date' => $dateTimeObject->format("D, M d, Y"),
                'reserved_time' => $dateTimeObject->format("h:i A"),
                'reserved_date_to' => $dateTimeObjectTable->format("D, M d, Y"),
                'reserved_time_to' => $dateTimeObjectTable->format("h:i A"),
                'user_name' => ucfirst($username),
                'address' => $restAddress,
                'order_url' => $webUrl . '/order',
                'reserve_url' => $webUrl . '/reservation',
                'loyalty_points' => $loyaltyPoints,
                'instruction' => $reservationDetails['user_instruction'],
                'reply_to' => DASHBOARD_EMAIL_FROM,
                'template_img_path' => MAIL_IMAGE_PATH,
                'reserved_seats' => $reservationDetails['seats'],
                'phone_no' => $reservationDetails['phone'],
                'facebook_url' => $restDetail->facebook_url,
                'instagram_url' => $restDetail->instagram_url,
                'receivedDate' => $dateTimeObject->format("D, M d, Y"),
                'receivedTime' => $dateTimeObject->format("h:i A"),
                'receipt_number' => $reservationDetails['booking_id'],
                'res_address' => str_replace('"', '', $reservationDetails['address']),
                'reservation_id' => $reservationDetails['reservation_id'],
            );
            $data = array(
                'to' => array($reservationDetails['email']),
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
        }
        $dashboardFunctions->sendMails($data);
    }

    public function sendReservationModificationMail($reservationDetails, $loyaltyPoints = 0) {
        $dashboardFunctions = new DashboardFunctions();
        $dineinFunctions = new \Restaurantdinein\RestaurantdineinFunctions();
        $sl = \MCommons\StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $dateTimeObject = new \DateTime($reservationDetails['date_time']);
        $restModel = new Restaurant();
        //$options = array('columns'=>array('restaurant_logo_name','facebook_url','instagram_url','twitter_url'),'where'=>array('id' => $reservationDetails['restaurant_id']));
        $restDetail = $restModel->getRestaurantDetails($reservationDetails['restaurant_id']);
        $restaurantAddress = $restModel->restaurantAddress($reservationDetails['restaurant_id']);
        $username = $this->getUsername($reservationDetails);
        //$restAddress =  $restDetail['address'] . ", " . $restDetail['city_name'] . ", " . $restDetail['state_code'] . ", " . $restDetail['zipcode'];
        $restAddress = '<tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_westvillage.jpg" alt="Aria hell\'s kitchen 369 W 51st St, New York, NY 10019 b/t 9th Ave & 8th Ave" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr><tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_hellkitchen.jpg" alt="Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr>';
       
        if($reservationDetails['host_name']==PROTOCOL.SITE_URL){
            $layout = "email-layout/default_new";
            $template = RESERVATION_MODIFICATION_TEMPLATES;
            $subject = sprintf(SUBJECT_MODIFICATION_RESERVATION, $reservationDetails['restaurant_name']);
        }else{
            $layout = "email-layout/ma_default";
            $template = "ma_micro_reservation_confirm";
            $subject = "About Your Table Request at ".$reservationDetails['restaurant_name']."!";
        }
        if (!empty($reservationDetails)) {
            $variables = array(
                'restaurant_name' => iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']),
                'restaurant_logo'=>$restDetail['restaurant_logo_name'],
                'map_address'=>iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']).", ". $restaurantAddress,
                'restaurant_address'=>$restaurantAddress,
                'number_of_people' => $reservationDetails['reserved_seats'],
                'reserved_date' => $dateTimeObject->format("D, M d, Y"),
                'reserved_time' => $dateTimeObject->format("h:i A"),
                'user_name' => ucfirst($username),
                'address' => $restAddress,
                'order_url' => $webUrl . '/order',
                'reserve_url' => $webUrl . '/reservation',
                'loyalty_points' => $loyaltyPoints,
                'instruction' => $reservationDetails['user_instruction'],
                'reply_to' => DASHBOARD_EMAIL_FROM,
                'template_img_path' => MAIL_IMAGE_PATH,
                'reserved_seats' => $reservationDetails['reserved_seats'],
                'phone_no' => $reservationDetails['phone'],
                'facebook_url' => $restDetail['facebook_url'],
                'instagram_url' => $restDetail['instagram_url'],
                'twitter_url'=>$restDetail['twitter_url'],
                'receivedDate' => $dateTimeObject->format("D, M d, Y"),
                'receivedTime' => $dateTimeObject->format("h:i A"),
                'receipt_number' => $reservationDetails['receipt_no'],
                'status'=>"modified",
                'host_name'=>$reservationDetails['host_name'],
                'rest_code'=>$restDetail['rest_code']
                    //'res_address' => str_replace('"', '', $reservationDetails['address']),
                    // 'reservation_id' => $reservationDetails['reservation_id'],
            );
            $data = array(
                'to' => array($reservationDetails['email']),
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
        }
        $dashboardFunctions->sendMails($data);
    }

    public function sendReservationCancelMailToFriend($reservationDetails, $userId, $friendEmail, $restComments, $loyaltyPoints) {
        $sl = \MCommons\StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $dateTimeObject = new \DateTime($reservationDetails['reserved_on']);
        $restModel = new Restaurant();
        
        $restDetail = $restModel->getRestaurantDetails($reservationDetails['restaurant_id']);
        $restaurantAddress = $restModel->restaurantAddress($reservationDetails['restaurant_id']);
        //$restAddress =  $restDetail['address'] . ", " . $restDetail['city_name'] . ", " . $restDetail['state_code'] . ", " . $restDetail['zipcode'];
        $restAddress = '<tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_westvillage.jpg" alt="Aria hell\'s kitchen 369 W 51st St, New York, NY 10019 b/t 9th Ave & 8th Ave" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr><tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_hellkitchen.jpg" alt="Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr>';
        if($reservationDetails['host_name']==PROTOCOL.SITE_URL){
            $layout = "email-layout/default_new";
            $template = CANCEL_RESERVATION_TEMPLATES;
            $subject = sprintf(SUBJECT_RESERVATION_CANCEL, $reservationDetails['restaurant_name']);
        }else{
            $layout = "email-layout/ma_default";
            $template = "ma_micro_reservation_confirm";
            $subject = "Oh No! No Spot for You! ";
        }
        if (!empty($reservationDetails)) {
            $variables = array(
                'restaurant_name' => iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']),
                'restaurant_logo'=>$restDetail['restaurant_logo_name'],
                'map_address'=>iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']).", ". $restaurantAddress,
                'restaurant_address'=>$restaurantAddress,
                'number_of_people' => $reservationDetails['reserved_seats'],
                'reserved_date' => $dateTimeObject->format("D, M d, Y"),
                'reserved_time' => $dateTimeObject->format("h:i A"),
                'user_name' => ucfirst($reservationDetails['first_name']),
                'receipt_no' => $reservationDetails['receipt_no'],
                'address' => $restAddress,
                'order_url' => $webUrl . '/order',
                'reserve_url' => $webUrl . '/reservation',
                'loyalty_points' => $loyaltyPoints,
                'instruction' => $reservationDetails['user_instruction'],
                'reply_to' => DASHBOARD_EMAIL_FROM,
                'template_img_path' => MAIL_IMAGE_PATH,
                'reserved_seats' => $reservationDetails['reserved_seats'],
                'phone_no' => $reservationDetails['phone'],
                'facebook_url' => $restDetail['facebook_url'],
                'instagram_url' => $restDetail['instagram_url'],
                'twitter_url' => $restDetail['twitter_url'],
                'receivedDate' => $dateTimeObject->format("D, M d, Y"),
                'receivedTime' => $dateTimeObject->format("h:i A"),
                'status' => 'rejected',
                'host_name'=>$reservationDetails['host_name'],
                'rest_code'=>$restDetail['rest_code']
            );
            $data = array(
                'to' => array($friendEmail),
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
        }
        return $data;
    }

    public function sendStagTableCancelMailToHost($reservationDetails, $loyaltyPoints = 0, $restComments) {
        $dashboardFunctions = new DashboardFunctions();
        $sl = \MCommons\StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $dateTimeObject = new \DateTime($reservationDetails['reservation_date']);
        $restModel = new Restaurant();
        $options = array(
            'id' => $reservationDetails['restaurant_id'],
        );
        $restDetail = $restModel->findByRestaurantId($options);
        $username = $this->getUsername($reservationDetails);
        $restComments = $this->to_utf8($restComments);
        $htmlRestComments = '';
        if ($restComments === 'Customer called to cancel reservation') {
            $htmlRestComments = '<tr><td bgcolor="#ffffff" style="font-family:arial; font-size:18px;line-height:24px; color:#333333;"><i style="display:block;color:#867f7c;padding-top:14px;padding-bottom:14px;padding-left:20px;padding-right:20px;">Cancellation Reason: You cancelled your reservation by phone.</i><br /></td></tr>';
        } else {
            $htmlRestComments = '';
        }
        $message = 'This is to inform you thast your reservation for <strong>' . $reservationDetails['seats'] . '</strong> on <strong>' . $dateTimeObject->format("D, M d, Y") . '</strong> at <strong>' . $dateTimeObject->format("h:i A") . '</strong> has been cancelled.<br /><br />We suggest you to please contact us at <strong>' . $reservationDetails['phone'] . '</strong> if you wish to inquire about the reservation at another available time slot.<br /><br />We remain at your service for any future bookings you might wish to make.';
        //$restAddress =  $restDetail['address'] . ", " . $restDetail['city_name'] . ", " . $restDetail['state_code'] . ", " . $restDetail['zipcode'];
        $restAddress = '<tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_westvillage.jpg" alt="Aria hell\'s kitchen 369 W 51st St, New York, NY 10019 b/t 9th Ave & 8th Ave" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr><tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_hellkitchen.jpg" alt="Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr>';
        $layout = "email-layout/default_new";
//        if ($reservationDetails['order_id'] != '') {
//            $template = RESERVATION_CANCEL_PRE_ORDER;
//            $subject = SUBJECT_CANCEL_RESERVATION_PRE_ORDER;
//        } else {
//            $template = CANCEL_RESERVATION;
//            $subject = sprintf(SUBJECT_CANCEL_RESERVATION, $reservationDetails['restaurant_name']);
//        }
        $template = CANCEL_RESERVATION;
        $subject = sprintf(SUBJECT_CANCEL_RESERVATION, $reservationDetails['restaurant_name']);
        if (!empty($reservationDetails)) {
            $variables = array(
                'restaurant_name' => iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']),
                'number_of_people' => $reservationDetails['seats'],
                'reserved_date' => $dateTimeObject->format("D, M d, Y"),
                'reserved_time' => $dateTimeObject->format("h:i A"),
                'user_name' => ucfirst($username),
                'friend_name' => ucfirst($username),
                'address' => $restAddress,
                'order_url' => $webUrl . '/order',
                'reserve_url' => $webUrl . '/reservation',
                'loyalty_points' => $loyaltyPoints,
                'restaurant_comment' => $htmlRestComments,
                'instruction' => $htmlRestComments,
                'reply_to' => DASHBOARD_EMAIL_FROM,
                'template_img_path' => MAIL_IMAGE_PATH,
                'reserved_seats' => $reservationDetails['seats'],
                'phone_no' => $reservationDetails['phone'],
                'facebook_url' => $restDetail->facebook_url,
                'instagram_url' => $restDetail->instagram_url,
                'receivedDate' => $dateTimeObject->format("D, M d, Y"),
                'receivedTime' => $dateTimeObject->format("h:i A"),
                'res_address' => str_replace('"', '', $reservationDetails['address']),
                'content' => $message,
            );
            $data = array(
                'to' => array($reservationDetails['email']), //'wecare@munchado.com',
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
            $dashboardFunctions->sendMails($data);
        }
    }

    public function sendRservationCancelMailToHost($reservationDetails, $loyaltyPoints = 0, $restComments) {
        $dashboardFunctions = new DashboardFunctions();
        $sl = \MCommons\StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $webUrl = PROTOCOL . $config['constants']['web_url'];
        $dateTimeObject = new \DateTime($reservationDetails['date_time']);
        $restModel = new Restaurant();
        
        $restDetail = $restModel->getRestaurantDetails($reservationDetails['restaurant_id']);
        $restaurantAddress = $restModel->restaurantAddress($reservationDetails['restaurant_id']);
        $username = $this->getUsername($reservationDetails);
        $restComments = $this->to_utf8($restComments);
        $htmlRestComments = '';
        if ($restComments === 'Customer called to cancel reservation') {
            $htmlRestComments = '<tr><td bgcolor="#ffffff" style="font-family:arial; font-size:18px;line-height:24px; color:#333333;"><i style="display:block;color:#867f7c;padding-top:14px;padding-bottom:14px;padding-left:20px;padding-right:20px;">Cancellation Reason: You cancelled your reservation by phone.</i><br /></td></tr>';
        } else {
            $htmlRestComments = '';
        }
        $message = 'This is to inform you thast your reservation for <strong>' . $reservationDetails['reserved_seats'] . '</strong> on <strong>' . $dateTimeObject->format("D, M d, Y") . '</strong> at <strong>' . $dateTimeObject->format("h:i A") . '</strong> has been cancelled.<br /><br />We suggest you to please contact us at <strong>' . $reservationDetails['phone'] . '</strong> if you wish to inquire about the reservation at another available time slot.<br /><br />We remain at your service for any future bookings you might wish to make.';
        //$restAddress =  $restDetail['address'] . ", " . $restDetail['city_name'] . ", " . $restDetail['state_code'] . ", " . $restDetail['zipcode'];
        $restAddress = '<tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_westvillage.jpg" alt="Aria hell\'s kitchen 369 W 51st St, New York, NY 10019 b/t 9th Ave & 8th Ave" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr><tr><td align="center" style="font-size:0"><img src="' . TEMPLATE_IMG_PATH . 'add_hellkitchen.jpg" alt="Aria West Village 117 Perry St, New York, NY 10014 b/t Hudson St & Greenwich St" style="border: none; width: 100%; max-width: 100%; height: auto;display:block;"></td></tr>';
       if($reservationDetails['host_name']==PROTOCOL.SITE_URL){
            $layout = "email-layout/default_new";
            $template = CANCEL_RESERVATION_TEMPLATES;
            $subject = sprintf(SUBJECT_RESERVATION_CANCEL, $reservationDetails['restaurant_name']);
        }else{
            $layout = "email-layout/ma_default";
            $template = "ma_micro_reservation_confirm";
            $subject = "Oh No! No Spot for You!";
        }
        if (!empty($reservationDetails)) {
            $variables = array(
                'restaurant_name' => iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']),
                'restaurant_logo'=>$restDetail['restaurant_logo_name'],
                'map_address'=>iconv('CP1252', 'UTF-8', $reservationDetails['restaurant_name']).", ". $restaurantAddress,
                'restaurant_address'=>$restaurantAddress,
                'number_of_people' => $reservationDetails['reserved_seats'],
                'reserved_date' => $dateTimeObject->format("D, M d, Y"),
                'reserved_time' => $dateTimeObject->format("h:i A"),
                'user_name' => ucfirst($username),
                'friend_name' => ucfirst($username),
                'address' => $restAddress,
                'order_url' => $webUrl . '/order',
                'reserve_url' => $webUrl . '/reservation',
                'loyalty_points' => $loyaltyPoints,
                'restaurant_comment' => $restComments,
                'instruction' => $restComments,
                'reply_to' => DASHBOARD_EMAIL_FROM,
                'template_img_path' => MAIL_IMAGE_PATH,
                'reserved_seats' => $reservationDetails['reserved_seats'],
                'phone_no' => $reservationDetails['phone'],
                'facebook_url' => $restDetail['facebook_url'],
                'instagram_url' => $restDetail['instagram_url'],
                'twitter_url' => $restDetail['twitter_url'],
                'receivedDate' => $dateTimeObject->format("D, M d, Y"),
                'receivedTime' => $dateTimeObject->format("h:i A"),
                //'res_address' => str_replace('"', '', $reservationDetails['address']),
                'content' => $message,
                'status'=>'rejected',
                'host_name'=>$reservationDetails['host_name'],
                'rest_code'=>$restDetail['rest_code']
            );
            $data = array(
                'to' => array($reservationDetails['email']), //'wecare@munchado.com',
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => $template,
                'layout' => $layout,
                'subject' => $subject,
                'variables' => $variables
            );
            $dashboardFunctions->sendMails($data);
        }
    }

    public function sendReservationCancelMail($reservationDetails, $loyaltyPoints = 0, $restComments) {
        $dashboardFunctions = new DashboardFunctions();
        if (!empty($reservationDetails['id'])) {
            $userSettingsModel = new UserSettings();
            $userSettings = $userSettingsModel->getUserSettings($reservationDetails['user_id']);
            $sendMail = false;
            if (!empty($userSettings)) {
                $reservationConfirmation = $userSettings['reservation_confirmation'];
                $sendMail = ($reservationConfirmation == 0 || $reservationConfirmation == NULL) ? false : true;
            } else {
                $sendMail = true;
            }
            if ($sendMail) {
                $emailData = $this->sendRservationCancelMailToHost($reservationDetails, $loyaltyPoints, $restComments);
            }
            $userInvitationModel = new UserReservationInvitation();
            $invitations = $userInvitationModel->getUserAllInvitations($reservationDetails);
            if (!empty($invitations)) {
                foreach ($invitations as $invitation) {
                    if (!empty($invitation['friend_email'])) {
                        $userSettings = $userSettingsModel->getUserSettings($invitation['user_id']);
                        $sendMail = false;
                        if (!empty($userSettings)) {
                            $reservationConfirmation = $userSettings['reservation_confirmation'];
                            $sendMail = ($reservationConfirmation == 0 || $reservationConfirmation == NULL) ? false : true;
                        } else {
                            $sendMail = true;
                        }
                        if ($sendMail) {
                            $emailResponce = $this->sendReservationCancelMailToFriend($reservationDetails, $invitation['user_id'], $invitation['friend_email'], $restComments, $loyaltyPoints);
                            $dashboardFunctions->sendMails($emailResponce);
                        }
                    }
                }
            }
        }
    }

    public function getUsername($details) {
        if (!empty($details['first_name'])) {
            $userName = $details['first_name'];
        } else {
            $useremail = explode("@", $details['email']);
            $userName = $useremail[0];
        }
        return $userName;
    }

    public function sendSMSReservation($reservationDetails) {
        if ($reservationDetails['host_name'] === "munchado.com" || $reservationDetails['host_name'] === "munchado") { //send SmS 
            $status = $reservationDetails['status'];
            $restName = $reservationDetails['restaurant_name'];
            $smsMsg = "";
            if ($reservationDetails['order_id'] != '') {
                if ($status == 4) {
                    $smsMsg = $restName . " approved the changes to your reservation. Congrats!";
                } else if ($status == 3) {
                    $smsMsg = $restName . " has officially rejected your pre-paid reservation.";
                }
            } else {
                if ($status == 4) {
                    $smsMsg = $restName . " has accepted your reservations and is likely waiting patiently for your patronage.";
                } else if ($status == 3) {
                    $smsMsg = $restName . " had to cancel your reservation :( Check your inbox for more details. Don’t let it keep you down, make another reservation!";
                } else if ($status == 2) {
                    $smsMsg = "We've successfully canceled your reservation for you. Bummer.";
                }
            }
            $smsMessage = array(
                "user_mob_no" => $reservationDetails['phone'],
                "msg" => $smsMsg
            );
            \MCommons\StaticOptions::sendSmsClickaTell($smsMessage, $reservationDetails['user_id']);
        }
    }

    public function getGuestTotalReservations($id, $restId, $time = "") {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_reservation' => new Expression('COUNT(id)')
        ));
        $where = new Where ();
        $where->equalTo('user_id', $id);
        $where->equalTo('restaurant_id', $restId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));
        $records = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        // pr($records['total_reservation']);
        return $records['total_reservation'];
    }

}
