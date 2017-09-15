<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReview;
use MCommons\StaticOptions;
use User\Model\UserReservation;
use User\Model\UserOrder;
use User\UserFunctions;
class UserReviewsListController extends AbstractRestfulController {
	public function getList() {
		$session = $this->getUserSession ();
		if ($session) {
			$login = $session->isLoggedIn ();
			if (! $login) {
				throw new \Exception ( 'No Active Login Found.' );
			}
		} else {
			throw new \Exception ( 'No Active Login Found.' );
		}
		$user_function = new UserFunctions ();
		$locationData = $session->getUserDetail ( 'selected_location' );
		$currentDate = $user_function->userCityTimeZone ( $locationData );
		$userId = $session->getUserId ();
		$userReviews = array ();
		$userReviews['reviewed_restaurants'] = $this->getUserReviews($userId);
		$userReviews ['unReviewed_restaurants'] = $this->getUserUnreviews ( $userId,$currentDate);
		return $userReviews;
	}
	private function getUserReviews($userId) {
		$userReviewModel = new UserReview ();
		$reviews = $userReviewModel->getUserReviews ( $userId );
		$reviewsArray = array ();
		foreach ( $reviews as $key => $val ) {
			$dateTimeObject = new \DateTime($val ['created_on']);
			$reviewsArray [$key] ['restaurant_name'] = $val ['restaurant_name'];
			$reviewsArray [$key] ['restaurant_id'] = $val ['restaurant_id'];
			if(!empty($val['image_path'])){
				$reviewsArray [$key] ['restaurant_review_image_url'] = USER_REVIEW_IMAGE.$val['image_path'];
			}else {
				$reviewsArray [$key] ['restaurant_review_image_url'] = '';
			}
			$reviewsArray [$key] ['review_id'] = $val ['id'];
			$reviewsArray [$key] ['overall_rating'] = $val ['rating'];
			$reviewsArray [$key] ['transaction_type'] = StaticOptions::$review_for [$val ['review_for']];
			$reviewsArray [$key] ['transaction_date'] = $dateTimeObject->format('M d,Y');
			$reviewsArray [$key] ['review_description'] = $val ['review_desc'];
		}
		return $reviewsArray;
	}
	private function getUserUnreviews($userId,$currentDate) {
		$reservationModel = new UserReservation ();
		$userReservation = $reservationModel->getUserReservationDetails ( $userId,$currentDate );
		$reservationData = array ();
		foreach ( $userReservation as $key => $val ) {
			$reservationData [$key] = $val;
			$reservationData [$key] ['transaction_type'] = 'dine_in';
			$reservationData [$key] ['transaction_date'] = $val ['reserved_on'];
			unset ( $reservationData [$key] ['reserved_on'] );
		}
		$orderModel = new UserOrder ();	
		$orderData = array ();
		$userOrder = $orderModel->getUserOrderDetails ( $userId );
		$final = array ();
		foreach ( $userOrder as $single ) {
			if (isset($final [$single ['restaurant_id']])) {
				$final [$single ['restaurant_id']] ['(order/dine)_items'][] = array (
						'item_name' => $single ['item_name'],
						'item_id' => $single ['item_id'] 
				);
			} else {
				
				$final [$single ['restaurant_id']] = array ();
				$final [$single ['restaurant_id']]['id'] = $single['id'];
				$final [$single ['restaurant_id']]['restaurant_name'] = $single['restaurant_name'];
				$final [$single ['restaurant_id']]['restaurant_id'] = $single['restaurant_id'];
				$final [$single ['restaurant_id']]['transaction_type'] = 'delivery';
				$final [$single ['restaurant_id']]['transaction_date'] = $single['created_at'];
				$final [$single ['restaurant_id']]['(order/dine)_items'][] = array (
						'item_name' => $single ['item_name'],
						'item_id' => $single ['item_id']
				);
			}
		}
		$data = array_merge($reservationData,$final);
		$userReviewModel = new UserReview ();
		$reviews = $userReviewModel->getUserReviews ( $userId );
		$reviewId = array();
		foreach($reviews as $key=>$val){
			$reviewId['review_id'] = $val['order_id'];
		}
		$finalData = array();
		foreach($data as $key1=>$val1){
			if(!in_array($val1['id'], $reviewId)){
				$dateTime = new \DateTime($val1['transaction_date']);
				$finalData[$key1] = $val1;
				$finalData[$key1]['transaction_date'] = $dateTime->format('M d,Y');
				
			}
		}
		
		return $finalData;
	}
}