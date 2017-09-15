<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\UserReview;
use Dashboard\Model\RestaurantReview;
use Dashboard\Model\UserMenuReview;
use Dashboard\Model\Restaurant;
use Dashboard\Model\OwnerResponse;
use MCommons\StaticOptions;

class DashboardReviewController extends AbstractRestfulController {

    public $restaurantId;
    public $page = 1;
    public $limit = 1;
    public $type;
    public $offset = 0;

    const RECORD_PER_PAGE = 20;

    public function getList() {
        $data = [];
        $userReviewsModel = new UserReview();
        $restReviewsModel = new RestaurantReview();
        $dashboardFunctions = new DashboardFunctions();
        $this->restaurantId = $dashboardFunctions->getRestaurantId();
        $this->orderby = $this->getQueryParams('orderby');
        $this->page = $this->getQueryParams('page');
        $this->type = $this->getQueryParams('type');
        //$this->limit = $this->getQueryParams('limit', RECORD_PER_PAGE)
        if ($this->page > 0) {
            $this->page = ($this->page < 1) ? 1 : $this->page;
            $this->offset = ($this->page - 1) * ($this->limit);
        }
        $options = array(
            'restaurant_id' => $this->restaurantId,
            'offset' => $this->offset,
            'orderby' => $this->orderby,
            'limit' => self::RECORD_PER_PAGE,
        );
        if ($this->type == 'user') {
            $data['reviews_count'] = $userReviewsModel->dashboardTotalUserReviews($this->restaurantId)[0]['total_review'];
            $data['negative_reviews_count'] = $userReviewsModel->dashboardTotalNegativeReviews($this->restaurantId)[0]['total_review'];
            $data['reviews'] = $userReviewsModel->getDashboardRestaurantUserReviews($options);
        }
        if ($this->type == 'other') {
            $data['other_review_count'] = $restReviewsModel->getDashboardTotalOthersReviews($this->restaurantId)[0]['total_review'];
            $data['other_review'] = $restReviewsModel->getDashboardRestaurantOtherReviews($options);
        }
        return $data;
    }

    public function get($id) {
        $data = [];
        $userReviewsModel = new UserReview();
        $restReviewsModel = new RestaurantReview();
        $userMenuReviewModel = new UserMenuReview();
        $dashboardFunctions = new DashboardFunctions();
        $restModel = new Restaurant();
        $this->restaurantId = $dashboardFunctions->getRestaurantId();
        $restCode = $restModel->getRestaurantCode(array($this->restaurantId));
        $type = $this->type = $this->getQueryParams('type');
        if ($type == 'user') {
            $data = (array) $userReviewsModel->getUserReviewDetails($id, $this->restaurantId);
        }
        if ($type == 'other') {
            $data = (array) $restReviewsModel->getRestaurantReviewDetails($id);
        }
        $data['order_details'] = $userMenuReviewModel->getMenusReview($id, $restCode[0]['rest_code']);
        return $data;
    }

    public function update($id, $data) {
        $dashboardFunctions = new DashboardFunctions();
        $this->restaurantId = $dashboardFunctions->getRestaurantId();
        $userReviewsModel = new UserReview();
        if (!empty($data)) {
            $review = $userReviewsModel->getReviewDetails($id, $this->restaurantId);
        }
        if (!empty($review)) {
            $updateData = array(
                'owner_response_date' => date('Y-m-d H:i:s'),
                'replied' => 1
            );
            if ($userReviewsModel->update($id, $updateData)) {
                $updateData = array(
                    'review_id' => $id,
                    'response' => $data['restaurant_response'],
                    'response_date' => date("Y:m:d H:i:s")
                );
                $ownerResModel = new OwnerResponse();
                $ownerResModel->save($updateData);
                if (\MCommons\StaticOptions::getPermissionToSendMail($review['user_id'])) {
                    $reviewDetail = $userReviewsModel->getReviewDetails($id, $this->restaurantId);
                    $this->send_review_response_to_user($id, $data['restaurant_response'], $reviewDetail);
                }
                $this->review_update_notification($reviewDetail,$id, 'replied');
                return ['status' => 'success'];
            } else {
                return ['status' => 'failure'];
            }
        }
    }

    public static function review_update_notification($reviewDetail, $reviewId, $status) {
        if (!empty($reviewId)) {
            if (!empty($reviewDetail)) {
                $data = array();
                $restModel = new Restaurant();
                $restaurant = $restModel->getRestaurantDetail($reviewDetail['restaurant_id']);
                if (!empty($restaurant)) {
                    $restName = $restaurant['restaurant_name'];
                }
                $userModel = new \Dashboard\Model\User();
                $userName = $userModel->getUserName($reviewDetail['user_id']);
                $currDateTime = StaticOptions::getRelativeCityDateTime(array(
                            'restaurant_id' => $reviewDetail['restaurant_id']
                ));
                $msg_time = "Today," . $currDateTime->format('H:i a');
                $data ['user_id'] = $reviewDetail['user_id'];
                $data ['created_on'] = $currDateTime->format(StaticOptions::MYSQL_DATE_FORMAT);
                $data ['type'] = 4;
                $data ['restaurant_id'] = $reviewDetail['restaurant_id'];
                if ($status == 'replied') {
                    $msg = ucwords($restName) . " read and replied to your review. Eeep!";
                    $data ['notification_msg'] = $msg;
                    $data ['channel'] = "mymunchado_" . $reviewDetail['user_id'];
                    $pubnubInfo = array("user_id" => $reviewDetail['user_id'], "restaurant_id" => $reviewDetail['restaurant_id'], "restaurant_name" => $restName, "review_id" => $reviewId);
                    $reviewNotificationArray = array(
                        "msg" => $msg,
                        "channel" => "mymunchado_" . $reviewDetail['user_id'],
                        "userId" => $reviewDetail['user_id'],
                        "type" => 'reviews',
                        "restaurantId" => $reviewDetail['restaurant_id'],
                        'restaurantName' => $restName,
                        'firstName' => $userName,
                        'isFriend' => 0,
                        'curDate' => $currDateTime->format(StaticOptions::MYSQL_DATE_FORMAT),
                    );
                    $userNotificationModel = new \Dashboard\Model\UserNotification();
                    $userNotificationModel->createPubNubNotification($reviewNotificationArray, $pubnubInfo);
                    \MCommons\StaticOptions::pubnubPushNotification($reviewNotificationArray);
                }
            }
        }
    }

    public function send_review_response_to_user($reviewID, $restResponse, $reviewDetail) {
        $dashboardFunctions = new DashboardFunctions();
        $userModel = new \Dashboard\Model\User();
        $user = $userModel->getUserDetails($reviewDetail['user_id']);
        $restModel = new Restaurant();
        $restaurant = $restModel->getRestaurantDetails($reviewDetail['restaurant_id']);
        $rname = strtolower(str_replace(" ", "-", $restaurant['restaurant_name']));
        $cta_url = WEB_HOST_URL . 'restaurants/' . $rname . '/' . $reviewDetail['restaurant_id'] . '/reviews';
        $layout = "email-layout/default_new";
        if (!empty($user['email'])) {
            $variables = array(
                'user_name' => $user['first_name'],
                'restaurant_name' => $restaurant['restaurant_name'],
                'restaurant_response' => $this->to_utf8($restResponse),
                'ctalink_url' => $cta_url,
                'reply_to' => DASHBOARD_EMAIL_FROM,
            );
            $data = array(
                'to' => $user['email'],
                'from' => DASHBOARD_EMAIL_FROM,
                'template_name' => RASTURANT_RESPONSE_ON_REVIEW,
                'layout' => $layout,
                'subject' => sprintf(SUBJECT_ON_REVIEW, $restaurant['restaurant_name']),
                'variables' => $variables
            );
            $dashboardFunctions->sendMails($data);
        }
    }

    public function to_utf8($in) {
        if (is_array($in)) {
            foreach ($in as $key => $value) {
                $out[$this->to_utf8($key)] = $this->to_utf8($value);
            }

        } elseif (is_string($in)) {
            if (mb_detect_encoding($in) != "UTF-8")
                return utf8_encode($in);
            else
                return $in;
        } else {
            return $in;
        }
        return $out;
    }
}
