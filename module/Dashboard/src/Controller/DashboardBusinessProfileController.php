<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\Restaurant;
use Dashboard\Model\RestaurantCalendars;

class DashboardBusinessProfileController extends AbstractRestfulController {

    public function getList() {
        $dashboardFunctions = new DashboardFunctions();
        $restModel = new Restaurant();
        $restCalendarModel = new RestaurantCalendars();
        $restId = $dashboardFunctions->getRestaurantId();
        $data = [];
        $data['restaurant_id'] = $restId;
        $calender = $restCalendarModel->getRestaurantOpeningHours($restId);
        $data['calender'] = $calender;
        $restDetail = $restModel->getRestaurantDetail($restId);
        $data['delivery_charge'] = $restDetail['minimum_delivery'];
        $data['delivery_area'] = $restDetail['delivery_area'];
        $data['updated_at'] = date("M d, Y", strtotime($restDetail['updated_on']));
        if (empty($restDetail['updated_at'])) {
            $data['updated_at'] = date('M d, Y');
        }
        $data['accept_cc'] = $restDetail['accept_cc'];
        $data['offer_delivery'] = $restDetail['delivery'];
        return $data;
    }

    public function create($data) {
        $dashboardFunctions = new DashboardFunctions();
        $restCalendarModel = new RestaurantCalendars();
        $restId = $dashboardFunctions->getRestaurantId();
        $record = $restCalendarModel->updateRestaurantCalendar($data, $restId);
        if (empty($record)) {
            return ['msg' => 'failure'];
        } else {
            return ['msg' => 'success'];
        }
    }
}
