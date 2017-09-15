<?php
namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;

class FinalCheckingController extends AbstractRestfulController {
    public function getList() {
       return array('muncher_unlock'=>'Health nut','is_checkin_first_time'=>0,'last_min'=>'Sud');
    }
}

