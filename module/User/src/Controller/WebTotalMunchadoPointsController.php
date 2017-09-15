<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;

class WebTotalMunchadoPointsController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $userId = $this->getUserSession()->user_id;
        $userPoints = new \User\Model\UserPoint();
        $balancePoints = array();
        $totalPoints = $userPoints->countUserPoints($userId);
        $redeemPoint = $totalPoints[0]['redeemed_points'];
        $balancePoints['points'] = strval($totalPoints[0]['points'] - $redeemPoint);
        return $balancePoints;
    }

}
