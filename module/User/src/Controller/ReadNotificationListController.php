<?php
namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserNotification;
use MCommons\StaticOptions;

class ReadNotificationListController extends AbstractRestfulController{
	public function getList(){ 
		$session = $this->getUserSession ();
		if ($session) {
			$login = $session->isLoggedIn ();
			if (!$login) {
				throw new \Exception ( 'No Active Login Found.' );
			}
		} else {
			throw new \Exception ( 'No Active Login Found.' );
		}
		$userId = $session->getUserId ();
		$UserNotifications = array();
		$UserNotifications['user_notifications'] = $this->readNotificationList($userId);
		if(!empty($UserNotifications)){
			$notifications = $UserNotifications;
		}else {
			$notifications = $this->noNotification();
		}
		return $notifications;
	}
	
	private function readNotificationList($userId){
		$notificationModel = new UserNotification();
		$userNotifications = $notificationModel->readNotificationList($userId);
		$dateTime = new \DateTime();
		$currnetDate = $dateTime->format('Y-m-d');
		$notificationData = array();
		foreach($userNotifications as $key=>$val){
			$dateTimeObject = new \DateTime($val['created_on']);
			$notificationDate = $dateTimeObject->format('Y-m-d');
			if($currnetDate == $notificationDate){
				$notificationData[] = $val;
				$notificationData[$key]['type'] = StaticOptions::$notification_type[$val['type']];
				$notificationData[$key]['msg_time'] = $dateTimeObject->format('h:i A');
				unset($notificationData[$key]['created_on']);
			}else{
				$notificationData[] = $val;
				$notificationData[$key]['type'] = StaticOptions::$notification_type[$val['type']];
				$notificationData[$key]['msg_time'] = $dateTimeObject->format('M d Y');
				unset($notificationData[$key]['created_on']);
			}
		}
		if(empty($notificationData)){
			return $notificationData = 'You have no notifications';
		}
		return $notificationData;
	
	}
	private function noNotification(){
		$UserNotifications = array();
		$UserNotifications['notifications_count'] = 0;
		$UserNotifications['type']  = 0;
		$UserNotifications['classes'] = 'nonotificationIconPoint-orange';
		$UserNotifications['notification_msg']  = "You currently have no new notifications";
		return $UserNotifications;
	}
}
