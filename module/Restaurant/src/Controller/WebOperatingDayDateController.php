<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Calendar;
use MCommons\StaticOptions;

class WebOperatingDayDateController extends AbstractRestfulController {

    private $nextSevenDay = array();
    private $currentDate = false;
    private $restaurantDay = array();
    private $finalDayDate = array();
    private $sortDate = array();

    public function get($restaurant_id = 0) {
        $outputDatetimeFormat = $this->getQueryParams('output_datetime_format', 'Y-m-d');
        $calendarModel = new Calendar ();
        $this->restaurantDay = $calendarModel->getRestaurantCalender($restaurant_id);

        $currentDateTime = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $restaurant_id
                ))->format('Y-m-d H:i');

        $dateTime = explode(" ", $currentDateTime);
        $this->currentDate = $dateTime[0];
        $currentTime = $dateTime[1];
        $data['restaurant_id'] = $restaurant_id;
        $data['date'] = $this->currentDate;
        $timeSlotController = $this->getServiceLocator()->get("Restaurant\Controller\WebRestaurantTimeSlotController");
        $timeSlots = $timeSlotController->create($data);

        $this->getNextSevenDays();
        $this->finalDayDate();
        if (!empty($this->finalDayDate)) {
            array_multisort($this->sortDate, SORT_ASC, $this->finalDayDate);
        }

        foreach ($this->finalDayDate as $key => $val) {
            if ($key == 0) {
                if (empty($timeSlots['timeslots'])) {
                    unset($this->finalDayDate[$key]);
                } else {
                    $this->finalDayDate[0]['formated_date'] = 'Today';
                }
            }
        }

        if (empty($timeSlots['timeslots'])) {
            $finalDaytime = array();
            $i = 0;
            foreach ($this->finalDayDate as $k => $val) {
                $finalDaytime[$i]['date'] = $val['date'];
                $finalDaytime[$i]['formated_date'] = $val['formated_date'];
                $i++;
            }
            return $finalDaytime;
        }

        return $this->finalDayDate;
    }

    private function getNextSevenDays() {
        if ($this->currentDate) {
            for ($i = 0; $i < 7; $i++) {
                $day = date('D', strtotime($this->currentDate . ' +' . $i . ' day'));
                $date = date('Y-m-d', strtotime($this->currentDate . ' +' . $i . ' day'));
                $formatedDate = date('D, M d', strtotime($this->currentDate . ' +' . $i . ' day'));
                $sortDayName = substr(strtolower($day), 0, 2);
                $this->nextSevenDay[$sortDayName]['date'] = $date;
                $this->nextSevenDay[$sortDayName]['formated_date'] = $formatedDate;
            }
        }
    }

    private function finalDayDate() {
        $i = 0;
        if (!empty($this->restaurantDay)) {
            foreach ($this->restaurantDay as $key => $val) {
                foreach ($this->nextSevenDay as $k => $v) {
                    if ($k == $val['calendar_day']) {
                        $this->finalDayDate[$i]['date'] = $v['date'];
                        $this->finalDayDate[$i]['formated_date'] = $v['formated_date'];
                        $this->sortDate[$i] = strtotime($v['date']);
                    }
                }
                $i++;
            }
        }
    }

}
