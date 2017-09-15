<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\ReportAbuse;
use MCommons\StaticOptions;
class WebReportAbuseController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function create($data) {
        if (!isset($data['restaurant_id']) || $data['restaurant_id'] == null) {
            throw new \Exception('Please provide restaurant_id');
        }
        if (!isset($data['review_id']) || $data['review_id'] == null) {
            throw new \Exception('Please provide review id');
        }
        $reportAbuseRestaurantsModel = new ReportAbuse();
        $userId = $this->getUserSession()->getUserId();
        $reportAbuseRestaurantsModel->user_id = $userId;
        $reportAbuseRestaurantsModel->abuse_type = isset($data['abuse_type']) ? $data['abuse_type'] : '';
        $reportAbuseRestaurantsModel->restaurant_id = $data['restaurant_id'];
        $reportAbuseRestaurantsModel->review_id = $data['review_id'];
        $reportAbuseRestaurantsModel->comment = $data['comment'];
        $reportAbuseRestaurantsModel->created_on = StaticOptions::getDateTime()->format(StaticOptions::MYSQL_DATE_FORMAT);
        try {
            $reportAbuseRestaurantsModel->addReport();
            return array('success' => true);
        } catch (\Exception $ex) {
            if ($ex->getPrevious()->getCode() == 23000) {
               throw new \Exception('You have already reported this abuse.');
            }
            throw new \Exception('Something apparantly went wrong');
        }
    }

}
