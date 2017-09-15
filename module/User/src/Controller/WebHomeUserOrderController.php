<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\UserOrder;

class WebHomeUserOrderController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $userFunctions = new UserFunctions();

        $userOrderModel = new UserOrder();
        $placedOrder = array();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);

        $page = $this->getQueryParams('page', 1);
        $type = $this->getQueryParams('type');

        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * (SHOW_PER_PAGE);
        }
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $orderStatus = isset($config['constants']['order_status']) ? $config['constants']['order_status'] : array();
        $options = array(
            'userId' => $userId,
            'offset' => $offset,
            'orderby' => '',
            'orderStatus' => $orderStatus,
            'currentDate' => $currentDate,
            'restaurantId' => '',
            'limit' => 3
        );
        $individualOrder = $userFunctions->getIndividualOrder('ordered');
        $placedOrder = $userFunctions->getIndividualOrder('placed');
        //$rejeectOrder = $userFunctions->getUserRejeectOrder();
        $rejeectOrder = $userFunctions->getIndividualOrder('rejected');
        $archiveOrder = $userOrderModel->getUserArchiveOrder($options);

        if (!empty($individualOrder)) {

            return $individualOrder;
        } elseif (!empty($placedOrder)) {
            return $placedOrder;
        } elseif (!empty($rejeectOrder)) {
            return $rejeectOrder;
        } else {
            return $archiveOrder;
        }
    }

}
