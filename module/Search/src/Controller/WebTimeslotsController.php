<?php

namespace Search\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use Home\Model\City;

class WebTimeslotsController extends AbstractRestfulController {

    public function getList() {
        $session = $this->getUserSession();
        $selectedLocation = $session->getUserDetail('selected_location', array());
        $cityId = isset($selectedLocation ['city_id']) ? $selectedLocation ['city_id'] : '23637';
        // $inputDate = isset($this->getQueryParams('date_selected')) ? ($this->getQueryParams('date_selected')) : 'Today';
        $inputDate = 'Today';
        // $inputDate = 'Thu,16 Jan 2014';
        if (empty($inputDate)) {
            throw new \Exception("Invalid Date");
        }
        if (empty($cityId)) {
            throw new \Exception("Invalid City Id");
        }
        $cityModel = new City ();
        $cityDetails = $cityModel->cityDetails($cityId);
        if (!empty($cityDetails)) {
            $timeZone = $cityDetails [0] ['time_zone'];
            $cityDateTime = StaticOptions::getRelativeCityDateTime(array(
                        'state_code' => $cityDetails [0] ['state_code']
                    ));
        }
        $dateTimeObject = clone $cityDateTime;
        if (strtolower($inputDate) == 'today') {
            $date = $cityDateTime->format('Y-m-d');
        } else {
            $dateObject = new \DateTime($inputDate);
            $date = $dateObject->format('Y-m-d');
        }
        $data = array();
        $data ['days'] = $this->getDayDate($dateTimeObject);
        $timeslots = $this->getTimeSlots($date, $cityDetails [0] ['state_code']);
        $data ['current_timeslot'] = $timeslots [0] ['time'];
        $data ['timeslots'] = $timeslots;
        return $data;
    }

    private function getTimeSlots($date, $state_code) {
        $timeSlots = StaticOptions::getAllTimeSlots($date, $state_code);
        foreach ($timeSlots ['slots'] as $key => $val) {
            $dateTime = new \DateTime($timeSlots ['date'] . $val);
            $Slots [] ['time'] = $dateTime->format('h:i A');
        }
        unset($timeSlots ['date']);
        unset($timeSlots ['day']);
        return $Slots;
    }

    private function getDayDate($dateTimeObject) {
        $dayDate = array();
        $currentDate = $dateTimeObject->format('Y-m-d');
        $dayDate [] ['day'] = 'Today';
        $currentTime = $dateTimeObject->format('H:i');
        $i = 1;
        if (strtotime($currentTime) >= strtotime("23:00:00")) { // check if time is above 23:00 hours add one to date and show next day slots
            $dayDate [0] ['day'] = 'TOMORROW';
            $i = 2;
        }
        for ($i; $i < 7; $i ++) {
            $dateTimeObject->setDate($dateTimeObject->format('Y'), $dateTimeObject->format('m'), $dateTimeObject->format('d') + 1);
            $dayDate [] ['day'] = $dateTimeObject->format('D, M d');
        }
        return $dayDate;
    }

}
