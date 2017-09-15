<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Review;
use User\Model\UserReview;
use User\Model\UserReservation;
use User\Model\UserOrder;
use User\Model\UserMenuReview;
use MCommons\StaticOptions;

class ReviewDetailController extends AbstractRestfulController {

    public function get($restaurant_id = 0) {
        $response = array();
        $review = array();
        if (!$restaurant_id)
            throw new \Exception("Invalid Parameters", 400);

        // Get consolidate review and description
        $reviewModel = new Review ();
        $reservationModel = new UserReservation ();
        $orderModel = new UserOrder ();
        $ratedFoodItemModel = new UserMenuReview ();

        // $reviews = $reviewModel->findReviews();
        $reviews = $reviewModel->getReviews(array(
            'columns' => array(
                'id',
                'restaurant_id',
                'date',
                'reviews',
                'review_type',
                'sentiments',
                'source'
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id
            )
                )
        );

        $normalReviews = array_filter($reviews, function (&$review) {
//			$review ['date'] = StaticOptions::getFormattedDateTime ( $review ['date'], 'Y-m-d', 'M d,Y' );
            return $review ['review_type'] == "N";
        });
        $positiveNormalReviews = array_filter($normalReviews, function ($review) {
            return strtolower($review ['sentiments']) == "positive";
        });

        /*
         * User Review and User Detail
         */

        $reviewModel = new UserReview ();
        
        $userReview = $reviewModel->getReviews(array(
                    'columns' => array(
                        'restaurant_id' => $restaurant_id
                    )
                ))->toArray(); 
        //pr($userReview,1);
        foreach ($reviews as $key => $val) {
            $review [$key] ['restaurant_id'] = $val ['restaurant_id'];
            $review [$key] ['review_id'] = $val ['id'];
            $review [$key] ['review_date'] = $val ['date'];
            $review [$key] ['review_desc'] = $val ['reviews'];
            $review [$key] ['review_type'] = $val ['review_type'];
            $review [$key] ['sentiments'] = $val ['sentiments'];
            $review [$key] ['source'] = $val ['source'];
            $review [$key] ['order_on_time'] = '';
            $review [$key] ['fresh_prepared'] = '';
            $review [$key] ['as_specifications'] = '';
            $review [$key] ['temp_food'] = '';
            $review [$key] ['taste_test'] = '';
            $review [$key] ['order_again'] = '';
            $review [$key] ['rating'] = '';
            $review [$key] ['user_id'] = '';
            $review [$key] ['review_for'] = '';
            $review [$key] ['review_image'] = '';
            $review [$key] ['first_name'] = '';
            $review [$key] ['last_name'] = '';
            $review [$key] ['reviewer_image'] = '';
            $review [$key] ['reviewer_joining'] = '';
            $review [$key] ['city'] = '';
            $review [$key] ['rated_food_item'] = array();
            $review [$key] ['total_user_review'] = '';
            $review [$key] ['total_user_reservation'] = '';
            $review [$key] ['total_user_order'] = '';
        }
        $totalRestaurantReview = count($reviews) - 1;
        $totalUserReview = count($userReview);
        $i = 1;
        //pr($userReview,1);
        foreach ($userReview as $key => $val) {
            $review [$totalRestaurantReview + $i] ['restaurant_id'] = $val ['restaurant_id'];
            $review [$totalRestaurantReview + $i] ['review_id'] = $val ['id'];
            $review [$totalRestaurantReview + $i] ['review_date'] = $val ['created_at'];
            $review [$totalRestaurantReview + $i] ['review_desc'] = $val ['review_desc'];
            $review [$totalRestaurantReview + $i] ['review_type'] = '';
            $review [$totalRestaurantReview + $i] ['sentiments'] = $val ['sentiment'];
            $review [$totalRestaurantReview + $i] ['source'] = '';
            $review [$totalRestaurantReview + $i] ['order_on_time'] = $val ['on_time'];
            $review [$totalRestaurantReview + $i] ['fresh_prepared'] = $val ['fresh_prepared'];
            $review [$totalRestaurantReview + $i] ['as_specifications'] = $val ['as_specifications'];
            $review [$totalRestaurantReview + $i] ['temp_food'] = $val ['temp_food'];
            $review [$totalRestaurantReview + $i] ['taste_test'] = $val ['taste_test'];
            $review [$totalRestaurantReview + $i] ['order_again'] = $val ['order_again'];
            $review [$totalRestaurantReview + $i] ['rating'] = $val ['rating'];
            $review [$totalRestaurantReview + $i] ['user_id'] = $val ['user_id'];
            $review [$totalRestaurantReview + $i] ['review_for'] = StaticOptions::$review_for [$val ['review_for']];
            $review [$totalRestaurantReview + $i] ['review_image'] = $val ['image_path'];
            $review [$totalRestaurantReview + $i] ['first_name'] = $val ['first_name'];
            $review [$totalRestaurantReview + $i] ['last_name'] = $val ['last_name'];
            $review [$totalRestaurantReview + $i] ['reviewer_image'] = $val ['display_pic_url'];
            $review [$totalRestaurantReview + $i] ['reviewer_joining'] = $val ['created_at'];
            $review [$totalRestaurantReview + $i] ['city'] = $val ['city'];

            /*
             * Get rated food item
             */

            $ratedFoodItem = $ratedFoodItemModel->getRatedFoodItem(array(
                        'columns' => array(
                            'restaurant_id' => $restaurant_id,
                            'review_id' => $val ['id']
                        )
                    ))->toArray();

            $review [$totalRestaurantReview + $i] ['rated_food_item'] = $ratedFoodItem;

            /*
             * get total review, total Reservation, total Order of user
             */
            $trOfu = $reviewModel->getTotalUserRreview(array(
                        'columns' => array(
                            'user_id' => $val ['user_id']
                        )
                    ))->toArray();
            $tresOfu = $reservationModel->getTotalUserReservations($val ['user_id']);
            $tOrderOfu = $orderModel->getCountUserOrders($val ['user_id'],'I');

            $review [$totalRestaurantReview + $i] ['total_user_review'] = (count($trOfu) > 0) ? $trOfu [0] ['total_review'] : '';
            $review [$totalRestaurantReview + $i] ['total_user_reservation'] = ($tresOfu[0]['total_reservation'] > 0) ? $tresOfu [0] ['total_reservation'] : '';
            $review [$totalRestaurantReview + $i] ['total_user_order'] = ($tOrderOfu[0]['total_order'] > 0) ? $tOrderOfu [0] ['total_order'] : '';
            $i ++;
        }
        // #################### End of user Review and user detail #####################

        $percentPositive = 0;
        if (count($normalReviews)) {
            $percentPositive = round(count($positiveNormalReviews) * 100 / count($normalReviews));
        }

        $restaurantReviewDetails = array();
        $restaurantReviewDetails['reviews'] = $review;
        $restaurantReviewDetails['positive_sentiment_percent'] = $percentPositive;
        $restaurantReviewDetails['total_review_count'] = count($normalReviews) + count($userReview);

        return $restaurantReviewDetails;
    }

}
