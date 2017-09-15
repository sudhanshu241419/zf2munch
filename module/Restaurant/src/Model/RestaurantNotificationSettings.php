<?php

namespace Restaurant\Model;

use MCommons\Model\AbstractModel;

class RestaurantNotificationSettings extends AbstractModel {
	public $id;
	public $restaurant_id;
	public $new_order_received;
	public $order_cancellation;
	public $new_reservation_received;
	public $reservation_cancellation;
	public $new_deal_coupon_purchased;
	public $new_review_posted;
	public $important_system_updates;
	public $create_at;
	protected $_db_table_name = 'Restaurant\Model\DbTable\RestaurantNotificationSettingsTable';
	
	/**
	 * Get details of restaurant based on the restaurant ID
	 *
	 * @param number $restaurant_id        	
	 * @return Ambigous <\ArrayObject,false>
	 */
	public function getRestaurantNotificationSetting($restaurant_id = 0) {
		$data = array ();
		$getRestaurantSettings = $this->find ( array (
				'where' => array (
						'restaurant_id' => $restaurant_id
				)
		) )->current ();
				if ($getRestaurantSettings) {
					$data ['notification_setting'] ['id'] = $getRestaurantSettings->id;
					$data ['notification_setting'] ['restaurant_id'] = $getRestaurantSettings->restaurant_id;
					$data ['notification_setting'] ['new_order_received'] = $getRestaurantSettings->new_order_received;
					$data ['notification_setting'] ['order_cancellation'] = $getRestaurantSettings->order_cancellation;
					$data ['notification_setting'] ['new_reservation_received'] = $getRestaurantSettings->new_reservation_received;
					$data ['notification_setting'] ['reservation_cancellation'] = $getRestaurantSettings->reservation_cancellation;
					$data ['notification_setting'] ['new_deal_coupon_purchased'] = $getRestaurantSettings->new_deal_coupon_purchased;
					$data ['notification_setting'] ['new_review_posted'] = $getRestaurantSettings->new_review_posted;
					$data ['notification_setting'] ['important_system_updates'] = $getRestaurantSettings->important_system_updates;
					
				}
				return $data;
	}
	public function getRestaurantSettingStatus($restaurantId,$flag=NULL){
		$restaurantNotification = $this->getRestaurantNotificationSetting($restaurantId);
		$sendMailToOwner = false;
		$restaurantSettingStatus=0;
		if($restaurantNotification){
			if($flag=='reservation'){
				$userSettingStatus = $restaurantNotification['notification_setting']['new_reservation_received'];
			}elseif ($flag=='orderconfirm'){
				$userSettingStatus = $restaurantNotification['notification_setting']['new_order_received'];
			}
			$status = $userSettingStatus;
			$sendMailToOwner = ($status == 0 || $status == NULL) ? false : true;
			return $sendMailToOwner;
		}else{
            return true;
		}
	}
}