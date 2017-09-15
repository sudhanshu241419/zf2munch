<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReview;
use MCommons\StaticOptions;
use User\Model\UserReservation;
use User\Model\UserOrder;

class UserReviewController extends AbstractRestfulController {
	public function get($review_id) { 
		$session = $this->getUserSession ();
		if ($session) {
			$login = $session->isLoggedIn ();
			if (! $login) {
				throw new \Exception ( 'No Active Login Found.' );
			}
		} else {
			throw new \Exception ( 'No Active Login Found.' );
		}
		if (! $review_id) {
			throw new \Exception ( "Invalid Parameters", 400 );
		}
		$user_id = $session->getUserId ();
        $friendId = $this->getQueryParams('friendid',false);
        if($friendId){
            $user_id = $friendId;
        }
		$userReview = array ();
		$userReview['Data'] = $this->getUserReviewDetail($user_id,$review_id);
		return $userReview;
	}
	private function getUserReviewDetail($user_id,$review_id) {
		$userReviewModel = new UserReview ();
		$reviews = $userReviewModel->getUserReviewDetail($user_id,$review_id);
	    $final = array ();
		foreach ( $reviews as $single ) {
			if (isset($final [$single ['restaurant_id']])) {
				$final [$single ['restaurant_id']] ['order_items'][] = array (
						'item_name' => $single ['item_name'],
						'item_id' => $single ['item_id'] 
				);
			} else {
				$dateTimeObject = new \DateTime($single ['created_on']);
				$final [$single ['restaurant_id']] = array ();
				$final [$single ['restaurant_id']]['restaurant_name'] = $single['restaurant_name'];
				$final [$single ['restaurant_id']]['restaurant_id'] = $single['restaurant_id'];
				if(!empty($single['image_path'])){
					$final [$single ['restaurant_id']]['restaurant_review_image_url'] = USER_REVIEW_IMAGE.$single['image_path'];
				}else{
					$final [$single ['restaurant_id']]['restaurant_review_image_url'] = '';
				}	
				$final [$single ['restaurant_id']]['review_id'] = $single['id'];
				$final [$single ['restaurant_id']]['overall_rating'] = $single['rating'];
				$final [$single ['restaurant_id']]['transaction_type'] = StaticOptions::$review_for [$single ['review_for']];
				$final [$single ['restaurant_id']]['transaction_date'] = $dateTimeObject->format('M d,Y');
				$final [$single ['restaurant_id']]['review_text'] = $single['review_desc'];
				$final [$single ['restaurant_id']]['order_items'][] = array (
						'item_name' => $single ['item_name'],
						'item_id' => $single ['item_id']
				);
				$final [$single ['restaurant_id']]['order_details'] = array (
						'is_on_time' => StaticOptions::$on_time [$single ['on_time']],
						'is_freshly_prepared' => StaticOptions::$fresh_prepared[$single ['fresh_prepared']],
						'is_specified' => StaticOptions::$as_specifications[$single ['as_specifications']],
						'food_temperature' => StaticOptions::$temp_food[$single ['temp_food']],
						'food_taste' => StaticOptions::$taste_test[$single ['taste_test']],
						'would_order_again' => StaticOptions::$order_again[$single ['order_again']]
				);
				$final [$single ['restaurant_id']]['dine_in_experience'] = array (
						'service' => StaticOptions::$services[$single ['services']],
						'noise_level' => StaticOptions::$noise_level[$single ['noise_level']],
						'taste_level' => StaticOptions::$taste_test[$single ['taste_test']],
						'would_come_back' => StaticOptions::$come_back[$single ['come_back']]
				);
			}
		}
		return array_values($final);
	}
}