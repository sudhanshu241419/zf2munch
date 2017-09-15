<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\RestaurantReview;
use User\Model\UserReview;
use MCommons\CommonFunctions;
use MCommons\StaticOptions;

class WebReviewController extends AbstractRestfulController {

    public $formatCount = array();
    public $formatRestaurantReview = array();
    private static $orderMapping = array(
        'date' => 'desc',
        'rating' => 'desc',
        'type' => 'desc'
    );

    public function get($id) {
        $userId = $this->getUserSession()->getUserId();
        $config = $this->getServiceLocator()->get('Config');
        $commonFucntions = new CommonFunctions ();
        $queryParams = $this->getRequest()->getQuery()->toArray();
        $order = isset($queryParams ['sort']) ? $queryParams ['sort'] : '';
        if (!preg_match('/(date|rating|type)$/', $order)) {
            $order = false;
        }
        $restaurantReviewModel = new RestaurantReview ();
        $options = array(
            'columns' => array(
                'date',
                'reviewer',
                'reviews',
                'sentiments',
                'source',
                'source_url',
                'sort_date' => 'date'
            ),
            'where' => array(
                'restaurant_id' => $id,
                'review_type' => 'N'
            )
        );
        $restaurantReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $restaurantReview = $restaurantReviewModel->find($options)->toArray();
        array_walk($restaurantReview, array(
            $this,
            'formatDate'
        ));
        $userReviewModel = new UserReview ();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'umr' => 'user_menu_reviews'
            ),
            'on' => 'umr.user_review_id = user_reviews.id',
            'columns' => array(
                'menu_id',
                'image_name',
                'liked'
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'm' => 'menus'
            ),
            'on' => 'm.id = umr.menu_id',
            'columns' => array(
                'menu_name' => 'item_name'
            ),
            'type' => 'left'
        );       
        $joins [] = array(
            'name' => array(
                'u' => 'users'
            ),
            'on' => 'u.id = user_reviews.user_id',
            'columns' => array(
                'first_name',
                'display_pic_url',
                'created_at',
                'shipping_address'
            ),
            'type' => 'left'
        );
        $joins [] = array(
            'name' => array(
                'oresponse' => 'owner_response'
            ),
            'on' => 'user_reviews.id = oresponse.review_id',
            'columns' => array(
                'owner_response_id' => 'id',
                'response',
                'response_date'
            ),
            'type' => 'left'
        );
         $joins[]=array(
            'name'=>array(
                'r'=>'restaurants'
            ),
            'on' => 'r.id=user_reviews.restaurant_id',
            'columns'=>array('rest_code'),
            'type'=>'left'
        );
        if ($userId) {
            $joins [] = array(
                'name' => array(
                    'uf' => 'user_feedback'
                ),
                'on' => new \Zend\Db\Sql\Expression('user_reviews.id = uf.review_id AND uf.user_id =' . $userId),
                'columns' => array(
                    'feedback'
                ),
                'type' => 'left'
            );
        }
        $options = array(
            'columns' => array(
                'review_id' => 'id',
                'user_id',
                'review_for',
                'on_time',
                'fresh_prepared',
                'as_specifications',
                'temp_food',
                'taste_test',
                'services',
                'noise_level',
                'rating',
                'order_again',
                'come_back',
                'review_desc',
                'restaurant_responded_on' => 'created_on',
                'sentiment',
                'owner_response' => 'restaurant_response',
                'date' => 'approved_date',
                'order_id'
            ),
            'where' => array(
                'user_reviews.restaurant_id' => $id,
                'user_reviews.status' => 1
            ),
            'joins' => $joins
        );
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userReviewDetails = $userReviewModel->find($options)->toArray();
        $finalReviewDetails = array();
        foreach ($userReviewDetails as $key => $value) {
            if (!isset($finalReviewDetails [$userReviewDetails [$key] ['review_id']])) {
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] = $value;
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['owner_response'] = array();
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['food_details'] = array();
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['feedback_count'] = $commonFucntions->getFeedbackCount($userReviewDetails [$key] ['review_id']);
            }
            if (isset($userReviewDetails [$key] ['picture'])) {
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['picture'] = WEB_URL . USER_IMAGE_UPLOAD . strtolower($value['rest_code']) . DS . 'reviews' .  DS . $userReviewDetails [$key] ['picture'];
            }
            if ($value['owner_response_id'] != null) {
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['owner_response'][$value['owner_response_id']] = array(
                    'response' => $value ['response'],
                    'restaurant_responded_on' => ($value ['response_date'] != null) ? StaticOptions::getFormattedDateTime($value['response_date'], 'Y-m-d H:i:s', 'd M Y') : ''
                );
            }
            if ($value ['menu_id'] != null && !isset($finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['food_details'][$value ['menu_id']])) {
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['food_details'] [$value ['menu_id']] = array(
                    'item_id' => $value ['menu_id'],
                    'item' => $value ['menu_name'],
                    'loved_it' => $value ['liked'],
                    'image' => ($value ['image_name']) ?WEB_URL . USER_IMAGE_UPLOAD . strtolower($value['rest_code']) . DS . 'reviews' .  DS . $value ['image_name'] : ""
                );
            }
        }
        $finalReview = array();
        $userIds = array();
        krsort($finalReviewDetails);
        foreach ($finalReviewDetails as $key => $value) {
            $finalReviewDetails[$key]['food_details'] = array_values($finalReviewDetails[$key]['food_details']);
            $finalReviewDetails[$key]['owner_response'] = array_values($finalReviewDetails[$key]['owner_response']);
            if (isset($value ['user_id'])) {
                $userIds [] = $value ['user_id'];
            }
            if (isset($finalReviewDetails [$key] ['image_path'])) {
                $finalReviewDetails [$key] ['image_path'] = WEB_URL . 'restaurant_images' . DS . $finalReviewDetails [$key] ['image_path'];
            }
            $finalReviewDetails [$key] ['stats'] ['joined_on'] = ($value ['created_at'] != null) ? $commonFucntions->datetostring($value ['created_at'],$id) : null;
            $finalReviewDetails [$key] ['stats'] ['first_name'] = $value ['first_name'];
            $data = $commonFucntions->checkProfileImageUrl(array(
                'display_pic_url' => $value ['display_pic_url'],
                'id' => $value ['user_id']
                    ));
            $finalReviewDetails [$key] ['stats'] ['display_pic_url'] = $data ['display_pic_url'];
            $finalReviewDetails [$key] ['stats'] ['shipping_address'] = $value ['shipping_address'];
            $keysToRemove = array(
                'food_reviewed',
                'food_ordered',
                'item',
                'menu_id',
                'liked',
                'image_name',
                'menu_name',
                'created_at',
                'shipping_address',
                'display_pic_url',
                'first_name',
                'owner_response_id',
                'response',
                'response_date',
                'restaurant_responded_on'
            );
            $finalReview [] = array_diff_key($finalReviewDetails [$key], array_flip($keysToRemove));
        }
        $userIds = array_unique($userIds);
        $data = $commonFucntions->getUserHistory($userIds);
        unset($data ['joined_on']);
        $review = array();
        foreach ($userIds as $userId) {
            if (!isset($review [$userId])) {
                $review [$userId] = array(
                    'total_reviews' => '',
                    'total_orders' => '',
                    'total_reserve' => ''
                );
            }
        }
        if (is_array($data)) {
            array_walk($data, array(
                $this,
                'mapper'
            ));
        }
        foreach ($finalReview as $key => $value) {

            $finalReview [$key] ['dashboard_url'] = $config['image_base_urls']['local-cms'] . DS . 'review' . DS . $finalReview [$key] ['review_id'];
            $finalReview [$key] ['date'] = StaticOptions::getFormattedDateTime($value ['date'], 'Y-m-d H:i:s', 'd M, Y');
            $finalReview [$key] ['sort_date'] = StaticOptions::getFormattedDateTime($value ['date'], 'Y-m-d H:i:s', 'Y-m-d H:i:s');
            $finalReview [$key] ['stats'] ['total_orders'] = isset($this->formatCount ['order'] [$value ['user_id']] ['total_orders']) ? $this->formatCount ['order'] [$value ['user_id']] ['total_orders'] : 0;
            $finalReview [$key] ['stats'] ['total_reviews'] = isset($this->formatCount ['review'] [$value ['user_id']] ['total_reviews']) ? $this->formatCount ['review'] [$value ['user_id']] ['total_reviews'] : 0;
            $finalReview [$key] ['stats'] ['total_reservations'] = isset($this->formatCount ['reserve'] [$value ['user_id']] ['total_reservations']) ? $this->formatCount ['reserve'] [$value ['user_id']] ['total_reservations'] : 0;
            if (($value['review_for'] == 1 || $value['review_for'] == 2)) {
                $finalReview [$key] ['order_id'] = $value['order_id'];
            } elseif ($value['review_for'] == 3) {
                $finalReview [$key] ['reservation_id'] = $value['order_id'];
                unset($finalReview [$key] ['order_id']);
            }
        }
        
        $length = isset($queryParams ['limit']) ? $queryParams ['limit'] : '50';
        $offset = isset($queryParams ['offset']) ? $queryParams ['offset'] : '0';
        $final1 = array_merge($this->formatRestaurantReview, $finalReview);

        foreach ($final1 as $key => $val) {
            $sortDate[$key] = strtotime($val['sort_date']);
        }
        if (!$order || $order == 'date' || $order=='type') {
            /* uasort ( $final, array (
              $this,
              'date_compare'
              ) ); */
            if ($final1) {
                array_multisort($sortDate, SORT_DESC, $final1);
            }
        } elseif ($order == 'rating') {
            uasort($final1, array(
                $this,
                'rating_compare'
            ));
        }

        $final = array_slice($final1, $offset, $length);
        
        if($order === "type"){
            $review = array();
            $delivery = array();
            $tekeout = array();
            $dinein = array();
            $social = array();
            $k = 0;
            foreach($final as $key => $val){
               if(isset($val["review_for"]) && $val["review_for"]== 1 ){
                  $delivery[$k]=$val;
               } elseif(isset($val["review_for"]) && $val["review_for"]== 2){
                  $tekeout[$k]=$val;
               }else if(isset($val["review_for"]) && $val["review_for"]== 3){
                  $dinein[$k]=$val;
               }else{
                  $social[$k]=$val;
               }
               $k++;
            }
               $reviewDetail = array_merge_recursive($delivery,$tekeout,$dinein,$social);
               return $reviewDetail;
        }else{
               return array_values($final);
        }
    }

    public function mapper($value, $key) {
        foreach ($value as $single) {
            $this->formatCount [$key] [$single ['user_id']] = $single;
        }
    }

    public function formatDate($value) {        
        //$value ['date'] = ($value ['date']=="0000-00-00" || $value ['date']=='' || $value ['date']==NULL)?"2014-05-11":$value ['date'];
            $year = date('Y',strtotime($value ['date']));
            
            $value ['date']=date('Y-m-d H:i:s',strtotime($value ['date']));
            $value ['sort_date']=date('Y-m-d H:i:s',strtotime($value ['date']));
            if($year > 1970){
                $value ['date'] = StaticOptions::getFormattedDateTime($value ['date'], 'Y-m-d H:i:s', 'd M, Y');
                $value ['sort_date'] = StaticOptions::getFormattedDateTime($value ['sort_date'], 'Y-m-d H:i:s', 'Y-m-d H:i:s');
            }else{
                $value ['date']='';
                $value ['sort_date']='';
            }
            $this->formatRestaurantReview [] = $value; 
        
        
    }

    function date_compare($a, $b) {
        $t1 = strtotime($a ['date']);
        $t2 = strtotime($b ['date']);
        $t3 = ($t1 > $t2) ? - 1 : 1;
        return $t3;
    }

    function rating_compare($a, $b) {
        $t1 = isset($a ['rating']) ? $a ['rating'] : 0;
        $t2 = isset($b ['rating']) ? $b ['rating'] : 0;
        if ($t1 == $t2) {
            $t1 = strtotime($a ['date']);
            $t2 = strtotime($b ['date']);
        }
        $t3 = ($t1 > $t2) ? - 1 : 1;
        return $t3;
    }

}
