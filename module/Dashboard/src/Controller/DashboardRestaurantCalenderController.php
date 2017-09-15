<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\Model\RestaurantCalendars;
use Dashboard\DashboardFunctions;
use Dashboard\Model\Restaurant;
use Dashboard\Model\RestaurantSeats;

class DashboardRestaurantCalenderController extends AbstractRestfulController {

    const TERM_AROUND_TIME = 30;

    public function getList() {
        $dashboardFunctions = new DashboardFunctions();
        $restId = $dashboardFunctions->getRestaurantId();
        $date = $this->params('date');
        $slotLists = $this->get_slot_lists($restId, $date);
        return $slotLists;
    }

    private function get_slot_lists($restaurant_id, $date, $check_day_slots = false, $return_slot = false) {
        $restCalenderModel = new RestaurantCalendars();
        $restModel = new Restaurant();
        $restSeatsModel = new RestaurantSeats();
        $day = $this->extract_day_from_date($date);
        $rest_open_and_close_time = $restCalenderModel->get_restaurent_open_and_close_time($restaurant_id, $day);
        if (empty($rest_open_and_close_time)) {
            $next_day = date('Y-m-d', strtotime('+1 day'));
            $day = $this->extract_day_from_date($next_day);
            $rest_open_and_close_time = $restCalenderModel->get_restaurent_open_and_close_time($restaurant_id, $day);
        }
        if (empty($rest_open_and_close_time)) {
            return ['msg' => 'Restaurant not found'];
        }

        $rest_open_time = $rest_open_and_close_time['open_time'];
        $rest_close_time_today = $rest_close_time = $rest_open_and_close_time['close_time'];

        $prev_date = date('Y-m-d', strtotime($date . '-1 day'));
        $prev_day = $this->extract_day_from_date($prev_date);
        //print_r($date);   print_r($prev_date);   print_r($prev_day);        
        $prev_rest_open_and_close_time = $restCalenderModel->get_restaurent_open_and_close_time($restaurant_id, $day);
        //print_r($prev_rest_open_and_close_time);
        if (!empty($prev_rest_open_and_close_time)) {
            $opened_time = $prev_rest_open_and_close_time['open_time'];
            $closed_time = $prev_rest_open_and_close_time['close_time'];
            if (strtotime($opened_time) > strtotime($closed_time)) {
                $opened_time = "00:00:00";
                $prev_slots_list = $this->extract_slots_list($date, $opened_time, $closed_time);
            }
        }
        if (strtotime($rest_open_time) > strtotime($rest_close_time)) {
            $rest_close_time_today = '23:59:59';
        }
        $total_slots_list = $this->extract_slots_list($date, $rest_open_time, $rest_close_time_today);
        if (!empty($prev_slots_list)) {
            $total_slots_list = array_merge($prev_slots_list, $total_slots_list);
        }
        if (!empty($total_slots_list)) {
            $reserved_slots = $restSeatsModel->get_reserved_seats_with_slots($restaurant_id, $date);
            $slots = $this->format_reserved_slots($reserved_slots);
            $rest_ttl_seats = $restModel->getRestaurantTotalSeats($restaurant_id);
            $slots_lists = $this->arrange_slots($rest_ttl_seats, $total_slots_list, $slots);
            if ($check_day_slots === true) {
                $is_available = 0;
                $slot_detail = null;
                foreach ($slots_lists as $slot) {
                    $available_seats = (int) $slot['available_seats_to_reserve'];
                    if ($available_seats > 0) {
                        $is_available = 1;
                        $slot_detail = $slot;
                        break;
                    }
                }
                return ($return_slot === false) ? $is_available : array("is_available" => $is_available, "slot" => $slot_detail);
            }
        } else {
            $total_slots_list = $this->extract_default_slots_list($date, $rest_open_time, $rest_close_time);
            $reserved_slots = $restSeatsModel->get_reserved_seats_with_slots($restaurant_id, $date);
            $slots = $this->format_reserved_slots($reserved_slots);
            $rest_ttl_seats = $restModel->getRestaurantTotalSeats($restaurant_id);
            $slots_lists = $this->arrange_default_slots($rest_ttl_seats[0]['total_seats'], $total_slots_list, $slots);
        }

        return $slots_lists;
    }

    public function extract_day_from_date($date) {
        $restaurant_day = array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa');
        if (empty($date))
            return '';
        $date = strtotime($date);
        $day = date('w', $date);
        return $restaurant_day[$day];
    }

    public function arrange_slots($total_seats, $slots_list, $reserved_slots) {
        $i = 1;
        $tat = self::TERM_AROUND_TIME;
        $cur_time = strtotime(date("H:i:s"));
        foreach ($slots_list as $key => $slot) {
            //print_r($slot);
            if (isset($reserved_slots[$key])) {
                $total_reserved_seats = $reserved_slots[$key];
                $slots_list[$key]['total_reserved_seats'] = $total_reserved_seats;
                //$slots_list[$key]['status'] = ($total_seats > $total_reserved_seats) ? 1 : 0;
                $slots_list[$key]['status'] = 1;
                //print_r(array($total_seats, $total_reserved_seats));
                $slots_list[$key]['available_seats_to_reserve'] = ($total_seats - $total_reserved_seats);
            } else {
                //print_r($slot);
                //$slots_list[$key]['status'] = 1;
                $slots_list[$key]['available_seats_to_reserve'] = $total_seats;
                $open_time = $slot['start_time'];
                //$s_time = strtotime(date("H:i:s", strtotime("$open_time + $start_time_interval minutes")));
                $start_date_time = date('Y-m-d', strtotime($slot['start_date_time']));

                if ($start_date_time == date('Y-m-d') && $cur_time > strtotime($open_time)) {

                    $slots_list[$key]['status'] = 0; //currently this is set active
                } else {
                    $slots_list[$key]['status'] = 1;
                }
            }
            $i++;
        }

        return array_values($slots_list);
    }

    public function format_reserved_slots($slots) {
        $reserved_slots = array();
        if (!empty($slots)) {
            foreach ($slots as $slot) {
                $key = strtotime($slot['start_time']->format("Y-m-d H:i:s"));
                $reserved_slots[$key] = $slot['total_reserved_seats'];
            }
        }
        return $reserved_slots;
    }

    public function extract_slots_list($date, $open_time, $close_time) {
        $cur_date = strtotime(date("Y-m-d"));
        $given_date = strtotime($date);
        $cur_time = strtotime(date("H:i:s"));
        $tat = self::TERM_AROUND_TIME;
        $slots_list = array();

        $start_time = strtotime($date . " " . $open_time);
        $end_time = strtotime($date . " " . $close_time);
        $total_operational_time = $end_time - $start_time;
        $total_slots = round($total_operational_time / ($tat * 60));

        for ($i = 1; $i <= $total_slots; $i++) {


            $slot = array();
            $start_time_interval = ($i - 1) * $tat;
            $end_time_interval = $i * $tat;
            $s_time = strtotime(date("H:i:s", strtotime("$open_time + $start_time_interval minutes")));
            //if ($cur_date == $given_date && $cur_time > $s_time) continue;

            $slot['start_display_time'] = date("h:i A", strtotime("$open_time + $start_time_interval minutes"));
            $slot['start_time'] = date("H:i:s", strtotime("$open_time + $start_time_interval minutes"));
            $slot['end_time'] = date("H:i:s", strtotime("$open_time + $end_time_interval minutes"));
            $slot['start_time_24'] = date("H:i", strtotime("$open_time + $start_time_interval minutes"));
            $slot['end_time_24'] = date("H:i", strtotime("$open_time + $end_time_interval minutes"));
            $slot['start_date_time'] = $date . " " . $slot['start_time'];
            $slot['end_date_time'] = $date . " " . $slot['end_time'];
            $key = strtotime($slot['start_date_time']);
            $slots_list[$key] = $slot;
        }
        return $slots_list;
    }

}
