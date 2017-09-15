<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\PreOrder;
use MCommons\StaticOptions;

class PreOrderController extends AbstractRestfulController {
	public function get($restaurant_id) {
		$response = array ();
		if (! $restaurant_id) {
			return $this->sendError ( 'Input restaurant id', 404 );
		}
		$statusType = $this->getQueryParams ( 'status' );
		$token = $this->getQueryParams ( 'token' );
		$PreorderModel = new PreOrder ();
		$orderDetails = $PreorderModel->getDetails ( $restaurant_id, $statusType, $token );
		// Fri, Oct 11#12:30 AM
		$deliveryTime = $PreorderModel->manipulateDeliveryTime ( 'TODAY#08:30 PM', $restaurant_id );
		$response = $orderDetails;
		return $response;
	}
	public function create($data) {
		$preOrderModel = new PreOrder ();
		$preOrderModel->address = $data ['address'];
		$preOrderModel->delivery_time = $data ['delivery_time'];
		$preOrderModel->restaurant_id = $data ['restaurant_id'];
		$preOrderModel->created_at = StaticOptions::getRelativeCityDateTime(array(
                    'restaurant_id' => $data ['restaurant_id']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT);;
		
		$preOrderModel->order_status = $preOrderModel::ORDER_PENDING;
		$response = $preOrderModel->addtoPreOrder ();
		if (! $response) {
			return $this->sendError ( array (
					'error' => 'Unable to save reservation' 
			), 404 );
		}
		return $response;
	}
}
