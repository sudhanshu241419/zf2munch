<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use MCommons\StaticOptions;
use Zend\Db\Sql\Predicate\Expression;
use User\UserFunctions;
use User\Model\UserOrder;

class UserNotification extends AbstractModel {

    public $id;
    public $user_id;
    public $msg;
    public $type;
    public $restaurant_id;
    public $channel;
    public $created_on;
    public $status;
    public $pubnub_info;
    protected $_db_table_name = 'Dashboard\Model\DbTable\PubnubNotificationTable';
    protected $_primary_key = 'id';

    const CLASS_ORDER = 'dishIconPoint';
    const CLASS_GROUP_ORDER = 'dishIconPoint';
    const CLASS_RESERVATION = 'reservIconPoint';
    const CLASS_WELCOME = 'welcomeIconPoint';
    const ONE_CLASS_ORDER = 'dishIconPoint-orange';
    const ONE_CLASS_GROUP_ORDER = 'dishIconPoint-orange';
    const ONE_CLASS_RESERVATION = 'reservIconPoint-orange';
    const ONE_CLASS_WELCOME = 'welcomeIconPoint-orange';
    const NO_NOTIFICATION = 'nonotificationIconPoint-orange';
    const NOTIFICATION_TYPE_ORDER = 1;
    const NOTIFICATION_TYPE_GROUP_ORDER = 2;
    const NOTIFICATION_TYPE_RESERVATION = 3;
    const NOTIFICATION_TYPE_REVIEWS = 6;
    const NOTIFICATION_TYPE_DEALS = 4;
    const NOTIFICATION_TYPE_INVITE_FRIENDS = 5;
    const NOTIFICATION_CLASS_ORDER = "dishIconPoint-orange";
    const NOTIFICATION_CLASS_GORDER = "dishIconPoint-orange";
    const NOTIFICATION_CLASS_RESERVATION = "reservIconPoint-orange";
    const NOTIFICATION_CLASS_REVIEW = "review_notification";

    public function getCurrentNotification($user_id, $limit = NULL, $type, $todayDate) {
        $today = date('Y-m-d H:i:s', strtotime("-30 days", strtotime($todayDate)));
        $select = new Select();
        $orderDb = new UserOrder();
        $my_current_notification = array();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('id', 'msg' => 'notification_msg', 'type', 'restaurant_id', 'created_on', 'status', 'pubnub_info'));
        $where = new Where();
        $where->equalTo('user_id', $user_id);
        $where->equalTo('channel', 'mymunchado_' . $user_id);
        $where->greaterThanOrEqualTo('pubnub_notification.created_on', $today);
        $select->where($where);
        $select->limit($limit);
        $select->order('pubnub_notification.id DESC');
        if ($type == 'one') {
            //$select->where->in('type',array(1,3));
            $notificationData = $this->getDbTable()->getReadGateway()->selectWith($select)->current();
            $image_class = array(
                2 => "i_order",
                1 => "i_order",
                3 => "i_reserve_a_table active",
                4 => "i_ratereview",
                0 => "i_reserve_a_table",
                5 => "i_twopeople",
                6 => "i_ratereview",
                7 => "i_tip",
                8 => "i_upload_photo",
                9 => "i_bookmark",
                10 => "i_friendship",
                11 => "checkin",
                12 => "feed",
                13 => "transactions",
                14 => "i_point",
                15 => "i_reserve_a_table",
                16 => "i_deal",
                17 => "i_reserve_a_table",
                18 => "i_bill"
            );
            $type = array(
                2 => "myorders",
                1 => "myorders",
                3 => "myreservations",
                4 => "reviews",
                0 => "mymunchado",
                5 => "myfriends",
                6 => "myreviews",
                7 => "tip",
                8 => "upload_photo",
                9 => "bookmark",
                10 => "friendship",
                11 => "checkin",
                12 => "feed",
                13 => "transactions",
                14 => "mypoints",
                15 => "i_reserve_a_table",
                16 => "deal",
                17 => "dine_more",
                18 => "bill"
            );

            //var_dump($select->getSqlString($this->getPlatform('READ'))); die;

            $userFunction = new UserFunctions();
            if ($notificationData) {
                $my_current_notification['id'] = $notificationData->id;
                $my_current_notification['msg'] = $userFunction->to_utf8($notificationData->msg);
                $my_current_notification['type'] = $notificationData->type;
                $my_current_notification['restaurant_id'] = $notificationData->restaurant_id;
                $my_current_notification['created_on'] = StaticOptions::getFormattedDateTime($notificationData->created_on, 'Y-m-d H:i:s', 'Y-m-d H:i:s');
                $my_current_notification['classes'] = $image_class[$notificationData->type];
                $my_current_notification['status'] = $notificationData->status;

                if ($notificationData->pubnub_info != '') {
                    $pubnubArray = json_decode($notificationData->pubnub_info);
                    foreach ($pubnubArray as $key => $val) {
                        if (trim($key) == 'order_id') {
                            $getArchiveOrder = $orderDb->getArchiveOrderForNotification(trim($val), $todayDate);
                            if (count($getArchiveOrder) > 0) {
                                $my_current_notification['is_live'] = 0;
                            } else {
                                $my_current_notification['is_live'] = 1;
                            }
                        }
                        $my_current_notification[$key] = $val;
                    }
                }

                if (isset($notificationData->type)) {
                    $my_current_notification['classes'] = $image_class[$notificationData->type];
                }
                if (isset($notificationData->type)) {
                    $my_current_notification['link'] = $type[$notificationData->type];
                } // $my_current_notification['readable_created_date'] = StaticOptions::getFormattedDateTime($notificationData->created_on, 'Y-m-d H:i:s', 'M d Y');
            }

            return $my_current_notification;
        } elseif ($type == 'all') {
            $notificationlists = array();
            $notificationData = $this->getDbTable()
                    ->setArrayObjectPrototype('ArrayObject')
                    ->getReadGateway()
                    ->selectWith($select)
                    ->toArray();
            $image_class = array(
                2 => "i_order active",
                1 => "i_order active",
                3 => "i_reserve_a_table",
                0 => "i_reserve_a_table",
                5 => "i_twopeople",
                6 => "i_ratereview",
                4 => "i_ratereview",
                7 => "i_tip",
                8 => "i_upload_photo",
                9 => "i_bookmark",
                10 => "i_friendship",
                11 => "checkin",
                12 => "feed",
                13 => "transactions",
                14 => "i_point",
                15 => "i_reserve_a_table",
                16 => "i_deal",
                17 => "i_reserve_a_table",
                18 => "i_bill"
            );
            $type = array(
                2 => "myorders",
                1 => "myorders",
                3 => "myreservations",
                4 => "reviews",
                0 => "mymunchado",
                5 => "myfriends",
                6 => "myreviews",
                7 => "tip",
                8 => "upload_photo",
                9 => "bookmark",
                10 => "friendship",
                11 => "checkin",
                12 => "feed",
                13 => "transactions",
                14 => "mypoints",
                15 => "myreservations",
                16 => "deal",
                17 => "dine_more",
                18 => "bill"
            );
            $i = 0;
            if (!empty($notificationData)) {
                foreach ($notificationData as $key => $value) {
                    if ($i > 0) {
                        $value['classes'] = $image_class[$value['type']];
                        $value['link'] = $type[$value['type']];
                        if (!empty($value['created_on'])) {
                            $creation_date = StaticOptions::getFormattedDateTime($value['created_on'], 'Y-m-d H:i:s', 'Y-m-d H:i:s');
                            $current_date = $todayDate;
                            $time_msec = strtotime($current_date) - strtotime($creation_date);

                            $str_time = $this->getDayDifference($creation_date, $todayDate);
                            $value['msg_time'] = $str_time;
                            $pubnub = isset($value['pubnub_info']) && $value['pubnub_info'] != '' ? json_decode($value['pubnub_info']) : array();
                            if (count($pubnub) > 0) {
                                foreach ($pubnub as $key => $val) {
                                    if (trim($key) == 'order_id') {
                                        $getArchiveOrder = $orderDb->getArchiveOrderForNotification(trim($val), $current_date);
                                        if (count($getArchiveOrder) > 0) {
                                            $value['is_live'] = 0;
                                        } else {
                                            $value['is_live'] = 1;
                                        }
                                    }
                                    $value[$key] = $val;
                                }
                            }
                            unset($value['pubnub_info']);
                            $notificationlists[] = $value;
                        }
                    }
                    $i ++;
                }
            }
            return $notificationlists;
        }
    }

    public function getDayDifference($creation_date, $todayDate) {
        $date1 = StaticOptions::getFormattedDateTime($creation_date, 'Y-m-d H:i:s', 'Y-m-d'); // created on date
        $date5 = StaticOptions::getFormattedDateTime($todayDate, 'Y-m-d H:i:s', 'Y-m-d'); // StaticOptions::getDateTime ()->format ( 'Y-m-d' ); // today's date
        $today = $todayDate;
        $date7 = date("Y-m-d", strtotime('-7 days', strtotime($today)));

        if ($date1 <= $date5) {
            if ($date1 != $date5) {
                if ($date1 > $date7) {
                    for ($i = 1; $i <= 6; $i ++) {
                        $date6 = date("Y-m-d", strtotime('-' . $i . 'days', strtotime($date5)));
                        if ($date1 === $date6) {
                            if ($i == 1)
                                return date('M d, Y', strtotime($creation_date)) . " (" . $i . " day ago)"; // Feb 21, 2013 (1 day ago)
                            else
                                return date('M d, Y', strtotime($creation_date)) . " (" . $i . " days ago)"; // Feb 21, 2013 (2 days ago)
                        }
                    }
                } else {
                    return date('M d, Y', strtotime($creation_date)); // Feb 21, 2013
                }
            } elseif ($date1 === $date5) {
                if (date('H:i', strtotime($creation_date)) == '12:00') {
                    return "Today, " . date('h:i', strtotime($creation_date)) . " noon"; // Today, 12:00 noon
                } else {
                    return "Today, " . date('h:i a', strtotime($creation_date)); // Today, 6:35PM
                }
            }
        } else {
            return StaticOptions::getFormattedDateTime($creation_date, 'Y-m-d H:i:s', 'M d, Y'); // date('M d, Y',strtotime($creation_date));
        }
    }

    public function countPendingNotification($restaurantId) {        
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('total' => new Expression('count(id)')));
        $where = new Where();
        $where->equalTo('read_status', 0);
        $where->equalTo('channel', 'dashboard_' . $restaurantId);
        
        $select->where($where);
        // var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $userNotifications = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        return $userNotifications->toArray();
    }


    public function notificationStatusChange($restaurantId) {
        $data = array(
            'read_status' => 1
                )
        ;
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $dataUpdated = array();
        $dataUpdated = $writeGateway->update($data, array(            
            'channel' => "dashboard_".$restaurantId
        ));
        return $dataUpdated;
    }

    public function reservationNotifications($data) {
        if ($data['status'] == 'new') {
            $channel = "mymunchado_" . $data['user_id'];
            $message = "New reservation made at " . $data['restaurant_name'];
        }
        if ($data['status'] == 'update') {
            $channel = "mymunchado_" . $data['user_id'];
            $message = "Your reservation at " . $data['restaurant_name'] . " was modified.";
        }
        if ($data['status'] == 'cancelled') {
            $channel = "mymunchado_" . $data['user_id'];
            $message = 'Your reservation at ' . $data['restaurant_name'] . ' was cancelled';
        }
        $dataArray = array(
            'user_id' => $data['user_id'],
            'notification_msg' => $message,
            'type' => 3,
            'read_status' => 0,
            'restaurant_id' => $data['restaurant_id'],
            'channel' => $channel,
            'created_on' => $data['created_on']
        );
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->insert($dataArray);
        // $pubnub = StaticOptions::pubnubPushNotification($channel, $message);
        return $rowsAffected;
    }

    public function orderNotifications($data) {
        if ($data['status'] == 'new') {
            $channel = "mymunchado_" . $data['user_id'];
            $message = "Your Order Placed at " . $data['restaurant_name'];
        }
        $dataArray = array(
            'user_id' => $data['user_id'],
            'notification_msg' => $message,
            'type' => 1,
            'read_status' => 0,
            'restaurant_id' => $data['restaurant_id'],
            'channel' => $channel,
            'created_on' => $data['created_on']
        );
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->insert($dataArray);
        // $pubnub = StaticOptions::pubnubPushNotification($channel, $message);
        return $rowsAffected;
    }

    public function createPubNubNotification($data, $jsonData = array()) {
        $notId = isset($data['friend_iden_Id']) && $data['friend_iden_Id'] != '' ? $data['friend_iden_Id'] : $data['userId'];
        if (!StaticOptions::getPermissionToSendNotification($notId)) {
            return true;
        }
        if (!empty($data)) {
            if ($data['type'] == 'reservation') {
                $type = 3;
            } elseif ($data['type'] == 'cancelreservation') {
                $type = 15;
            } elseif ($data['type'] == 'order') {
                $type = 1;
            } elseif ($data['type'] == 'invite_friends') {
                $type = 5;
            } elseif ($data['type'] == 'reviews') {
                $type = 4;
            } elseif ($data['type'] == 'friendship') {
                $type = 10;
            } elseif ($data['type'] == 'tip') {
                $type = 7;
            } elseif ($data['type'] == 'upload_photo') {
                $type = 8;
            } elseif ($data['type'] == 'bookmark') {
                $type = 9;
            } elseif ($data['type'] == 'checkin') {
                $type = 11;
            } elseif ($data['type'] == 'feed') {
                $type = 12;
            } elseif ($data['type'] == 'dine_more') {
                $type = 17;
            } elseif ($data['type'] == 'bill') {
                $type = 18;
            } elseif($data['type'] == 'snag-a-spot'){
                $type = 19;
            }else {
                $type = 0;
            }
            $jsonDataArray = '';
            if (count($jsonData) > 0) {
                $jsonDataArray = json_encode($jsonData, JSON_HEX_APOS);
            }
            $cUpdate = 0;
            if (isset($data['cronUpdate']) && $data['cronUpdate'] != '') {
                $cUpdate = $data['cronUpdate'];
            }
            $userId = isset($data['friend_iden_Id']) && $data['friend_iden_Id'] != '' ? $data['friend_iden_Id'] : $data['userId'];
            $dataArray = array(
                'user_id' => $userId,
                'notification_msg' => $data['msg'],
                'type' => $type,
                // 'read_status' => 0,
                'restaurant_id' => $data['restaurantId'],
                'channel' => $data['channel'],
                'created_on' => $data['curDate'],
                'pubnub_info' => $jsonDataArray,
                'cronUpdate' => $cUpdate
            );

            $writeGateway = $this->getDbTable()->getWriteGateway();
            $rowsAffected = $writeGateway->insert($dataArray);
            if ($rowsAffected) {
                $notification = $this->countUserNotification($data['userId']);
                $count = $notification[0]['notifications'];
                $pubnub = StaticOptions::pubnubPushNotification(array(
                            'count' => $count,
                            'channel' => $data['channel']
                ));
                $pubnub = StaticOptions::pubnubPushNotification(array(
                            'count' => $count,
                            'channel' => 'ios_' . $data['channel']
                ));
                $pubnub = StaticOptions::pubnubPushNotification(array(
                            'count' => $count,
                            'channel' => 'android_' . $data['channel']
                ));
                return true;
            }
        }
    }

    public function restaurantNotification($options) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'id',
            'user_id',
            'notification_msg',
            'type',
            'restaurant_id',
            'created_on'
        ));
        $where = new Where();
        $where->equalTo('restaurant_id', $options['restaurant_id']);
        $where->equalTo('channel', $options['channel']);
        $select->where($where);
        $select->order('created_on DESC');
        $select->limit(20);
        $notificationsDetails = $this->getDbTable()
                ->setArrayObjectPrototype('ArrayObject')
                ->getReadGateway()
                ->selectWith($select);
        $notifications = $notificationsDetails->toArray();
        if (!empty($notifications)) {
            $i = 0;
            $currentDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $options['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT);
            foreach ($notifications as $key => $archivevalue) {
                $date1 = date('Y-m-d H:i:s', strtotime($archivevalue['created_on']));                
                $date3 = strtotime($currentDateTime) - strtotime($date1);

                $link = "";
                if ($archivevalue['type'] == 1) {
                    $classes = self::CLASS_ORDER;
                    $link = 'data-route="!orders" href="/#!orders" id="ipad-orders" class="a_linkstyle i_orders <%=App.helpers.setActiveLeft(\'!orders\') %>"';
                } elseif ($archivevalue['type'] == 2) {
                    $classes = self::CLASS_GROUP_ORDER;
                    $link = 'data-route="!orders" href="/#!orders" id="ipad-orders" class="a_linkstyle i_orders <%=App.helpers.setActiveLeft(\'!orders\') %>"';
                } elseif ($archivevalue['type'] == 3) {
                    $classes = self::CLASS_RESERVATION;
                    $link = 'data-route="!reservation" href="/#!reservation" id="ipad-reservation" class="a_linkstyle i_reservations <%=App.helpers.setActiveLeft(\'!reservation\') %>"';
                } elseif ($archivevalue['type'] == 5) {
                    $classes = "twoFriIconPoint";
                } elseif ($archivevalue['type'] == 4) {
                    $classes = "reviewIconPoint";
                    $link = 'data-route="!review" href="/#!review" id="review" class="a_linkstyle i_review <%=App.helpers.setActiveLeft(\'!review\') %>"';
                } elseif ($archivevalue['type'] == 17) {
                    $classes = "welcomeIconPoint";
                    $link = 'data-route="!guestbook" href="/#!guestbook" id="guest-book" class="a_linkstyle i_guest_book <%=App.helpers.setActiveLeft(\'!guestbook\') %>"';
                } elseif ($archivevalue['type'] == 19) {
                    $classes = self::CLASS_RESERVATION;
                    $link = 'data-route="!bookings" href="/#!bookings" id="ipad-booking" class="a_linkstyle i_reservations <%=App.helpers.setActiveLeft(\'!bookings\') %>"';
                } else {
                    $classes = self::CLASS_GROUP_ORDER;
                }
                $notifications[$key]['datediff'] = "1 second ago";
                if ($date3 > 0) {
                    $datediff = $this->calculate_day_time($date3);
                    $arr = split(' ', $datediff);
                    $notifications[$key]['datediff'] = $arr[0] . " " . $arr[1];
                }
                // pr($date1,1);
                if (date('Y-m-d', strtotime($date1)) == date('Y-m-d', strtotime($currentDateTime))) {
                    $notifications[$key]['date_time'] = $this->datetostring($archivevalue['created_on']);
                } else {
                    $notifications[$key]['date_time'] = $this->check_day($date1);
                }


                $notifications[$key]['time_diff'] = (int) $date3;
                $notifications[$key]['classes'] = $classes;
                $notifications[$key]['link'] = $link;
                $notifications[$key]['notification_msg'] = $archivevalue['notification_msg'];
                
            }
            
        }
        return $notifications;
    }

    public function calculate_day_time($secs) {
        $ret = array();
        $bit = array(
            ' year' => $secs / 31556926 % 12,
            ' week' => $secs / 604800 % 52,
            ' day' => $secs / 86400 % 7,
            ' hour' => $secs / 3600 % 24,
            ' minute' => $secs / 60 % 60,
            ' second' => $secs % 60
        );

        foreach ($bit as $k => $v) {
            if ($v > 1)
                $ret[] = $v . $k . 's';
            if ($v == 1)
                $ret[] = $v . $k;
        }
        array($ret, count($ret) - 1, 0, 'and');

        $ret[] = 'ago.';

        return join(' ', $ret);
    }

    public function datetostring($date) {
        $difference = time() - strtotime($date);
        $periods = array("second", "minute", "hour", "day", "week", "month", "years", "decade");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
        for ($j = 0; $difference >= $lengths[$j]; $j++)
            $difference /= $lengths[$j];
        $difference = round($difference);
        if ($difference != 1)
            $periods[$j].= "s";
        $text = "$difference $periods[$j] ago";
        return $text;
    }

    public function check_day($date) {

        $date1 = date('Y-m-d', strtotime($date)); // created on date
        $date5 = date('Y-m-d'); // today's date
        $today = date('Y-m-d H:i:s');

        $date7 = date("Y-m-d", strtotime('-7 days', strtotime($date5)));
        //return self::joinbefore($date,$today);
        if ($date1 != $date5) {
            if ($date1 > $date7) {
                for ($i = 1; $i <= 6; $i++) {
                    $date6 = date("Y-m-d", strtotime('-' . $i . 'days', strtotime($date5)));
                    if ($date1 === $date6) {
                        if ($i == 1)
                            return date('M d, Y', strtotime($date)) . " (" . $i . " day ago)"; //Feb 21, 2013 (1 day ago)
                        else
                            return date('M d, Y', strtotime($date)) . " (" . $i . " days ago)"; //Feb 21, 2013 (2 days ago)
                    }
                }
            }
            else {
                return date('M d, Y', strtotime($date)); // Feb 21, 2013
            }
        } elseif ($date1 === $date5) {
            //     return "Yesterday @ ".date('h:i A',strtotime($date));
            if (date('H:i', strtotime($date)) == '12:00') {
                return "Today, " . date('h:i', strtotime($date)) . " noon"; //Today, 12:00 noon	
            } else {
                return "Today, " . date('h:i a', strtotime($date)); //Today, 6:35PM
            }
        }
    }
    public function countUserNotification($user_id = 0,$todayDate=false)
    {
        $today = date('Y-m-d H:i:s',strtotime("-30 days",strtotime($todayDate)));   
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('notifications' => new Expression('count(id)')));
        $where = new Where();
        $where->equalTo('user_id', $user_id);
        $where->equalTo('read_status', 0);
        $where->equalTo('channel', 'mymunchado_'.$user_id);
        $where->greaterThanOrEqualTo('pubnub_notification.created_on', $today);
        $select->where($where);
       // var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $userNotifications = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select);
        return $userNotifications->toArray();
    }

}
