<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\RestaurantDineinCalendars;
use Dashboard\Model\RestaurantDineinCloseDays;

class DashboardDineInController extends AbstractRestfulController {

    public function getList() {
        $data = [];
        $dashboardFunctions = new DashboardFunctions();
        $restId = $dashboardFunctions->getRestaurantId();
        $restDineCalendarModel = new RestaurantDineinCalendars();
        $dineinCloseDayModel = new RestaurantDineinCloseDays();
        $data = (array) $restDineCalendarModel->get_restaurant_day_hours($restId);
        $data['close_days'] = $dineinCloseDayModel->get_restaurant_day_hours($restId);
        return $data;
    }

    public function create($data) {
        $dashboardFunctions = new DashboardFunctions();
        $restId = $dashboardFunctions->getRestaurantId();
        $restDineCalendarModel = new RestaurantDineinCalendars();
        $dineinCloseDayModel = new RestaurantDineinCloseDays();
        if (isset($data['rev_close_date']) && $data['rev_close_date'] != '') {
            $dineinCloseDayModel->create_restaurant_dinein_calendar($restId, $data, $data['rev_close_date']);
        }
        unset($data['rev_close_date']);
        unset($data['rev_close_from']);
        unset($data['rev_close_to']);
        unset($data['rev_close_whole']);
        if ($restId) {
            $data = $restDineCalendarModel->create_restaurant_dinein_calendar($restId, $data);
        }
        return $data;
    }

}
