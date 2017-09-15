<?php
namespace Restaurant;

use Restaurant\Model\Calendar;
use Restaurant\Model\RestaurantBookmark;
use Restaurant\Model\Cuisine;
use Restaurant\Model\Image;
use Zend\Db\Sql\Expression;
use Restaurant\Model\Story;
use Restaurant\Model\Feature;
use Restaurant\Model\City;
use MCommons\StaticOptions;
use MCommons\CommonFunctions;
use Restaurant\Model\RestaurantAccounts;

class OverviewFunctions {
    public $cityData = array();
    public $restaurantId;
    public $restCode;
    public $userId = '';
    public $limit;
    public $isMobile;
    public $queryParams=array();
    public $page = 1;
    public $formatRestaurantReview = array();
    public $restaurantTotalReview;
    public $totalRestaurantTips;
    public $userReviewForRestaurant = array();
    
    public function getMenu(&$response,$menuModel){        
        $item = array('menu_id' => 'id', 'item_name', 'item_desc', 'image_name');
        $popularMenu = $menuModel->getPopularMenues(array(
                    'columns' => array(
                        'restaurant_id' => $this->restaurantId,
                        'limit' => $this->limit
                    )
                        ), $item)->toArray();

        $response['most_popular'] = $popularMenu;
    }
    
    public function getCuisine(&$response){
        $cuisineModel = new Cuisine ();
        $cuisineData = $cuisineModel->getRestaurantCuisine(array(
                    'columns' => array(
                        'restaurant_id' => $this->restaurantId
                    )
                ))->toArray();

        $cuisines = array();
        $cuisineText = '';
        if (!empty($cuisineData)) {
            foreach ($cuisineData as $cuisine) {
                $cuisines [] = $cuisine ['cuisine'];
            }
            $cuisineText = implode(', ', $cuisines);

            $response ['cuisine_offerd'] = $cuisineText;
        } else {
            $response ['cuisine_offerd'] = '';
        }
    }
    
    public function getRestaurantStory(&$response){
        $storyModel = new Story ();
        $storyModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $story = $storyModel->findStory(array(
                    'columns' => array(
                        'title',
                        'story' => 'cuisine'
                    ),
                    'where' => array(
                        'restaurant_id' => $this->restaurantId
                    )
                ))->toArray();
        $response ['story'] = $story;
    }
    
    public function getOpeningAndClosingHours(&$response){
        $calendarModel = new Calendar ();
        $openingHours = $calendarModel->getOpeningHours(array(
            'columns' => array(
                'calendar_day',
                'open_time',
                'close_time',
                'open_close_status',
                'operation_hrs_ft'
            ),
            'where' => array(
                'status' => 1,
                'restaurant_id' => $this->restaurantId
            )
        ));       
       
        
        if ($openingHours) {            
            $response ['opening_hours'] = $openingHours;            
        } else {
            $response ['opening_hours'] = '';
        }
    }
    public function typeOfPlace(&$response){
        $featureModel = new Feature ();
        $response ['type_of_place'] = $featureModel->getRestaurantTop($this->restaurantId)->toArray();
    }
    
    public function userRestaurantImage(){
        $restaurantUserImagesModel = new \User\Model\UserRestaurantimage();
        $restaurantUserImagesModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'image',
                'title'=>'caption'
            ),
            'where' => array(
                'image_status' => 0,
                'status' => 1,
                'restaurant_id' => $this->restaurantId,
                'image_type'=>'g'
            )
        );
        $userImages = $restaurantUserImagesModel->find($options)->toArray();
        $userUploadedImage = array();
        if (!empty($userImages)) {
            foreach ($userImages as $userImage) {
                $userUploadedImage [] = array(
                    'title' => '',
                    'image' => $userImage ['image'],
                    'rest_code'=>strtolower($this->restCode),
                    'type'=>'user'
                );
            }
        }
        return $userUploadedImage;
    }
    
    
    public function getRestaurantGallery(){
        $imageModel = new Image ();
        return $imageModel->getRestaurantGallery($this->restCode, array(
            'columns' => array(
                new Expression('DISTINCT(image) as image'),
                'title' => 'image'
            ),
            'where' => array(
                'restaurant_id' => $this->restaurantId,
                'status' => 1
            ),

                ), $this->isMobile);
    }
    
    public function getRestaurantBookmark(&$response){
        $restaurantBookmarkModel = new RestaurantBookmark ();
        $bookmarkTypes = $restaurantBookmarkModel->bookmark_types;

        $bookmarkData = $restaurantBookmarkModel->getRestaurantBookmarkCount($this->restaurantId);
        $bmdata = array();

        // Initializing Bookmark Types Count Values
        foreach ($bookmarkTypes as $type) {
            $bmdata [$type] = 0;
        }
        if (count($bookmarkData)>0) {
            foreach ($bookmarkData as $bdata) {
                $key = $bdata ['type'];
                $bmdata [$key] = $bdata ['total_count'];
            }
        }
        $response ['total_like_count'] = $bmdata ['li'];
        $response ['total_love_count'] = $bmdata ['lo'];
        $response ['total_been_count'] = $bmdata ['bt'];
        $response ['is_in_cravelist'] = $bmdata ['wl'];
        $response ['is_been_there'] = ($bmdata ['bt'] > 0) ? '1' : '0';
        $response ['is_love_it'] = ($bmdata ['lo'] > 0) ? '1' : '0';        
    }
    
   public function prepareRestaurantData(&$response,$resDetails){
        $cityName = '';
        $this->cityData = $this->getCityDetails($resDetails['city_id']);
        if ($this->cityData) {
            $cityName = $this->cityData['city_name'];
            $tax = $this->cityData['sales_tax'];
        }
        $response ['name'] = $resDetails['restaurant_name'];
        $response['dine_more_code'] = substr($resDetails['restaurant_name'],0,1).$resDetails['id']."00";
        $response ['rest_code'] = strtolower($resDetails['rest_code']);
        $response ['description'] = $resDetails['description'];
        $response['order_pass_through'] = $resDetails['order_pass_through'];
        $response['delivery_desc'] = $resDetails['delivery_desc'];
        $response['delivery_charge'] = $resDetails['delivery_charge']; 
        $response['min_partysize'] = (int)$resDetails['min_partysize'];
        $response ['address'] = $resDetails['address'];
        if(!empty($resDetails['street']) && $resDetails['street']!=NULL && strlen($resDetails['street'])>0){
            $response ['address'] .=", " . $resDetails['street'];
        }
        $response ['address'] .= $cityName ? ", " . $cityName : '';
        $response ['address'] =  htmlspecialchars_decode($response ['address']);
        $response ['address'] =  strip_tags($response ['address']);
        $response ['short_address'] = htmlspecialchars_decode($resDetails['address']);
        $response ['short_address'] = strip_tags($response ['short_address']);
        $response ['phone_no'] = $resDetails['phone_no'];
        $response ['cover_image'] = $resDetails['restaurant_image_name'];
        $response ['delivery_area'] = $resDetails['delivery_area'];
        $response ['tax_percentage'] = $tax ? $tax : '';
        $response ['is_running_deal_coupon'] = $response['deals'] ? 1 : 0;
        $response ['address'] .= $resDetails['zipcode'] ? ", " . $resDetails['zipcode'] : '';
        $response ['is_reservation'] = $resDetails['reservations'];       
        $response['is_dining'] = $resDetails['dining'];
        $response ['is_accept_cc'] = $resDetails['accept_cc_phone'];
        $response['allowed_zip'] = !empty($resDetails['allowed_zip'])?explode(',', $resDetails['allowed_zip']):array();
        $response ['is_register'] = ($this->getRestaurantAccount())? "1" : "0";
        $response['cod']=(int)$resDetails['cod'];
        $response['restaurant_video_name'] = ($resDetails['restaurant_video_name'])?"munch_videos/".strtolower($resDetails ['rest_code'])."/".$resDetails['restaurant_video_name']:"";
        if($resDetails['reservations'] && $response ['is_register']== "1" && $resDetails['accept_cc_phone'] && $resDetails['menu_available'] && ($resDetails['menu_without_price'] == 0))
        {
            $response['preordering_enabled'] = 1;
        }else{
            $response['preordering_enabled'] = 0;
        }
        // below lines has been comments as per instruction with sudhanshu sir.
        //$currentDayDelivery = StaticOptions::getPerDayDeliveryStatus ( $this->restaurantId);
        //if($resDetails['delivery'] == 1){
        // $response['is_delevery']=($currentDayDelivery)?"1":"0";
        // }else{
          $response ['is_delevery'] = $resDetails['delivery'];
        //}
        $response['menu_available'] = $resDetails['menu_available'];
        $response['menu_without_price'] = $resDetails['menu_without_price'];
        $response ['minimum_delivery_amount'] = $resDetails['minimum_delivery'];
        //$response ['minimum_delivery_time'] = $resDetails['delivery_time'];
        $response ['delivery_charge'] = $resDetails['delivery_charge'];
        $response ['is_takeout'] = $resDetails['takeout'];
        $response ['price'] = $resDetails['price'];
        $response ['contact_no'] = $resDetails['phone_no'];
        $response['is_delivery_o'] = $resDetails['delivery'];
        $response['is_takeout_o'] = $resDetails['takeout'];
         if ($resDetails['menu_without_price']==1 || $response ['is_accept_cc']==0) {
            $response ['is_delevery'] = "0";
            $response ['is_takeout'] = "0";
        }else{
            $response ['is_delevery'] = (string)$resDetails['delivery'];
            $response ['is_takeout'] = (string)$resDetails['takeout'];
        }
        $response['ratings'] = $resDetails['ratings'];
        $response['landmark'] = $resDetails['landmark'];
        $response['wating_time'] = WATING_TIME;
        
    }
    
    private function getUserTips($commonFucntions){
        $userTip = new \User\Model\UserTip();
        $this->totalRestaurantTips=$userTip->restaurantTotalTips($this->restaurantId);
        $joins = array();
        $joins [] = array(
            'name' => array(
                'u' => 'users'
            ),
            'on' => 'u.id = user_tips.user_id',
            'columns' => array(
                'user_id'=>new \Zend\Db\Sql\Expression('u.id'),
                'first_name',
                'display_pic_url',         
                'shipping_address',
                'joined_on'=>new \Zend\Db\Sql\Expression('u.created_at'),
            ),
            'type' => 'left'
        );
        $options = array(
          'columns' => array(
              'tip_id' => 'id',
              'tip',
              'created_at','approved_date'
          ),
          'where' => array(
              'restaurant_id' => $this->restaurantId, 
              'user_tips.status'=>'1',
          ),
          'order'=>'created_at',
          'limit'=>'3',
          'joins'=>$joins
          
        );
        $userTip->getDbTable()->setArrayObjectPrototype('ArrayObject');
        
        if ($userTip->find($options)->toArray()) {
           $tips = $userTip->find($options)->toArray();           
           foreach($tips as $key => $tip){               
                $data = $commonFucntions->checkProfileImageUrl(array(
                'display_pic_url' => $tips[$key]['display_pic_url'],
                'id' => $tips[$key]['user_id']
            ));
                $tips[$key]['display_pic_url'] = $data['display_pic_url'];
                $bookmarks = $commonFucntions->getUserHistoryForMob(array($tip['user_id'])); 
                $tips[$key]['joined_on']= ($tips[$key]['joined_on'] != null) ? $commonFucntions->datetostring($tip['joined_on'],$this->restaurantId) : null;
                $tips[$key]['badge']= 'Food Pandit';
                $tips[$key]['total_beenthere']= isset($bookmarks['beenthere'][0]['total_beenthere'])?$bookmarks['beenthere'][0]['total_beenthere']:0;
                $tips[$key]['total_tryit']= isset($bookmarks['totalOrder'][0]['total_order'])?$bookmarks['totalOrder'][0]['total_order']:0;
                $tips[$key]['total_reservations']= isset($bookmarks['reserve'][0]['total_reservation'])?$bookmarks['reserve'][0]['total_reservation']:0;
                $tips[$key]['total_reviews']= isset($bookmarks['review'][0]['total_reviews'])?$bookmarks['review'][0]['total_reviews']:0;              
                $tips[$key]['sort_date'] = date('Y-m-d H:i:s',strtotime($tip ['approved_date']));
                $tips[$key]['type']='tip';
                $tips[$key]['date']=($tips[$key]['created_at'] != null) ? $commonFucntions->datetostring($tip['created_at'],$this->restaurantId) : null;
                unset($tips[$key]['created_at']);
                
            }
            return $tips;
       
        }else{
           return $tips = array();
        }
    }
    
     private function getUserReview(){
       
        $userReviewModel = new \User\Model\UserReview();
        $userReviewModel->getRestaurantReviewCount($this->restaurantId);
        $this->userReviewForRestaurant = $userReviewModel->userReviewForRestaurant;
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
                'shipping_address',
                'city_id'
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
        if ($this->userId) {
            $joins [] = array(
                'name' => array(
                    'uf' => 'user_feedback'
                ),
                'on' => new \Zend\Db\Sql\Expression('user_reviews.id = uf.review_id AND uf.user_id =' . $this->userId),
                'columns' => array(
                    'feedback'
                ),
                'type' => 'left'
            );
        }
        $joins[]=array(
            'name'=>array(
                'r'=>'restaurants'
            ),
            'on' => 'r.id=user_reviews.restaurant_id',
            'columns'=>array('rest_code'),
            'type'=>'left'
        );
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
                'date' => 'created_on','approved_date',
                'order_id'
            ),
            'where' => array(
                'user_reviews.restaurant_id' => $this->restaurantId,
                'user_reviews.status' => 1
            ),
            'order'=>'user_reviews.created_on',
            'limit'=>'3',
            'joins' => $joins
        );
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $userReviewModel->find($options)->toArray();
    }
    
     private function getRestaurantReviewData(){
        $restaurantReviewModel = new \Restaurant\Model\RestaurantReview();
        $this->restaurantTotalReview = $restaurantReviewModel->restaurantTotalReview($this->restaurantId);
        $options = array(
            'columns' => array(
                'date',
                'reviewer',
                'reviews',
                'review_type',
                'sentiments',
                'source',
                'source_url',
                'sort_date' => 'date',
                'review_date' => new \Zend\Db\Sql\Expression('DATE_FORMAT(date, "%d %b,%Y")')
            ),
            'where' => array(
                'restaurant_id' => $this->restaurantId,                
            ),
            'order'=>'date',
            'limit'=>3
        );
        $restaurantReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $restaurantReviewModel->find($options)->toArray();
    }
    
    private function positiveSentimentPercentage(){ 
        // Get consolidate review and description
        $reviewModel = new \Restaurant\Model\Review();
        $userReviewModel = new \User\Model\UserReview();

        $consolidatedReviews = $reviewModel->getReviews(array(
            'columns' => array(
                'consolidated_review' => 'reviews',
            ),
            'where' => array(
                'restaurant_id' => $this->restaurantId,
                'review_type' => 'C'
            )
                )
        );
       $consolidatedReviewCount = count($consolidatedReviews);

       $NormalReviews = $reviewModel->getReviews(array(
            'columns' => array(
                'consolidated_review' => 'reviews',
            ),
            'where' => array(
                'restaurant_id' => $this->restaurantId,
                'review_type' => 'N'
            )
                )
        );

        $NormalReviewCount = count($NormalReviews);

        $positiveSentiments = $reviewModel->getReviews(array(
            'columns' => array(
                'positive_review' => 'reviews',
            ),
            'where' => array(
                'restaurant_id' => $this->restaurantId,
                'sentiments' => 'Positive',
                'review_type' => 'N'
            )
                )
        );
        $positiveUserSentiments = $userReviewModel->getAllUserReview(array(
            'columns' => array(
                'sentiment',
            ),
            'where' => array(
                'restaurant_id' => $this->restaurantId,
                'sentiment' => 1,
                'status' => 1
            )
        ));
        $positiveSentimentsCount = count($positiveSentiments) + count($positiveUserSentiments);
        if ($positiveSentiments) {
            $postiveReview = array_pop($positiveSentiments);
        } else {
            $postiveReview = '';
        }

        //count user reviews also
        $userReviewCountOptions = array(
            'columns' => array(
                'total' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $this->restaurantId,
                'status' => 1
            )
        );
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userReviewCount = $userReviewModel->find($userReviewCountOptions)->current()->getArrayCopy();
        $total_review_count = $NormalReviewCount + $userReviewCount['total'];
        $positive_sentiment_count = $total_review_count != 0 ? ceil(($positiveSentimentsCount * 100) / $total_review_count)  : '0';
        
        return $positive_sentiment_count;
    }
    
     public function getRestaurantReview(){
        $sl = StaticOptions::getServiceLocator();
        $config = $sl->get('Config');
        $commonFucntions = new CommonFunctions ();
        
        $order = isset($this->queryParams ['sort']) ? $this->queryParams ['sort'] : '';
        if (!preg_match('/(date|rating|type)$/', $order)) {
            $order = false;
        }
        
        $restaurantReview = $this->getRestaurantReviewData();
        
        $normalReviews = array_filter($restaurantReview, function (&$restaurantReview) {
            return $restaurantReview ['review_type'] == "N";
        });
        $positiveNormalReviews = array_filter($normalReviews, function ($restaurantReview) {
            return strtolower($restaurantReview ['sentiments']) == "positive";
        });
        
        if($restaurantReview){
            array_walk($restaurantReview, array(
                $this,
                'formatDate'
            ));
        }
        $userReviewDetails = $this->getUserReview();
        
        $finalReviewDetails = array();
        foreach ($userReviewDetails as $key => $value) {
            if (!isset($finalReviewDetails [$userReviewDetails [$key] ['review_id']])) {
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] = $value;
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['type'] = 'user';
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['owner_response'] = array();
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['food_details'] = array();
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['feedback_count'] = $commonFucntions->getFeedbackCount($userReviewDetails [$key] ['review_id']);
            }
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['review_for']=$config['constants']['review_for'][$value['review_for']];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['on_time']=$value['on_time'];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['fresh_prepared']=$value['fresh_prepared'];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['as_specifications']=$value['as_specifications'];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['temp_food']=$value['temp_food'];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['taste_test']=$value['taste_test'];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['services']=$value['services'];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['noise_level']=$value['noise_level'];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['order_again']=$value['order_again'];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['come_back']=$value['come_back'];
            $finalReviewDetails [$userReviewDetails [$key] ['review_id']]['city_id']=$value['city_id'];
            if ($value['owner_response_id'] != null) {
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['owner_response'][$value['owner_response_id']] = array(
                    'response' => $value ['response'],
                    'restaurant_responded_on' => $value ['response_date']
                );
            }
            
            if ($value ['menu_id'] != null && !isset($finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['food_details'][$value ['menu_id']])) {
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['menu_review_images'] [] = array(
                    'item_id' => $value ['menu_id'],
                    'item' => $value ['menu_name'],                    
                    'image' => ($value ['image_name']) ? WEB_URL . USER_IMAGE_UPLOAD . strtolower($value['rest_code']) . DS . 'reviews' .  DS . $value ['image_name'] : ""
                );
            }else{
                $finalReviewDetails [$userReviewDetails [$key] ['review_id']] ['menu_review_images'] =array();
            }
        }
        $finalReview = array();
        $userIds = array();
      
        foreach ($finalReviewDetails as $key => $value) {
            $finalReviewDetails[$key]['food_details'] = array_values($finalReviewDetails[$key]['food_details']);
            $finalReviewDetails[$key]['owner_response'] = array_values($finalReviewDetails[$key]['owner_response']);
            if (isset($value ['user_id'])) {
                $userIds [] = $value ['user_id'];
            }
            if (isset($finalReviewDetails [$key] ['image_path'])) {
                $finalReviewDetails [$key] ['image_path'] = WEB_URL . 'restaurant_images' . DS . $finalReviewDetails [$key] ['image_path'];
            }
            $finalReviewDetails [$key] ['stats'] ['joined_on'] = ($value ['created_at'] != null) ? $commonFucntions->datetostring($value ['created_at'],$this->restaurantId) : null;
            $finalReviewDetails [$key] ['stats'] ['first_name'] = $value ['first_name'];
            if(isset($value['city_id']) && !empty($value['city_id'])){
                $cityData = $this->getCityDetails($value['city_id']);   
                $finalReviewDetails[$key]['stats']['city'] = $cityData['city_name'];
            }else{
                $finalReviewDetails[$key]['stats']['city'] = '';
            }
             $data = $commonFucntions->checkProfileImageUrl(array(
                'display_pic_url' => $value ['display_pic_url'],
                'id' => $value ['user_id']
            ));
            $finalReviewDetails [$key] ['stats'] ['display_pic_url'] = $data ['display_pic_url'];
            $finalReviewDetails [$key] ['stats'] ['shipping_address'] = $value ['shipping_address'];
            $finalReviewDetails[$key]['stats']['badge']='Food Pandit';
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
        
        $data = $commonFucntions->getUserHistoryForMob(array_unique($userIds));
       
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
        //pr($this->formatCount,true); 
        foreach ($finalReview as $key => $value) {

            $bookmarks = $commonFucntions->getUserHistoryForMob(array($value['user_id'])); 
            $finalReview [$key] ['dashboard_url'] = $config['image_base_urls']['local-cms'] . DS . 'review' . DS . $finalReview [$key] ['review_id'];
            $finalReview [$key] ['date'] = $value ['date'];
            $finalReview [$key] ['sort_date'] = StaticOptions::getFormattedDateTime($value ['approved_date'], 'Y-m-d H:i:s', 'Y-m-d H:i:s');
            $finalReview [$key] ['stats'] ['total_beenthere'] = isset($bookmarks['beenthere'][0]['total_beenthere'])?$bookmarks['beenthere'][0]['total_beenthere']:0;
            $finalReview [$key] ['stats'] ['total_tryit']= isset($bookmarks['totalOrder'][0]['total_order'])?$bookmarks['totalOrder'][0]['total_order']:0;
            $finalReview [$key] ['stats'] ['total_reservations'] = isset($bookmarks['reserve'][0]['total_reservation'])?$bookmarks['reserve'][0]['total_reservation']:0;
            $finalReview [$key] ['stats'] ['total_reviews'] = isset($bookmarks['review'][0]['total_reviews'])?$bookmarks['review'][0]['total_reviews']:0;
            
            if (($value['review_for'] == 1 || $value['review_for'] == 2)) {
                $finalReview [$key] ['order_id'] = $value['order_id'];
            } elseif ($value['review_for'] == 3) {
                $finalReview [$key] ['reservation_id'] = $value['order_id'];
                unset($finalReview [$key] ['order_id']);
            }
            
            
            ###################
            $options = array('columns'=>array('image', 'image_url'),'where'=>array('user_review_id'=>$value['review_id']));
            $userReviewImage = new \User\Model\UserReviewImage();
            $userReviewImage->getDbTable()->setArrayObjectPrototype('ArrayObject');
            $reviewImages = $userReviewImage->find($options)->toArray();
           if($reviewImages){
                    $finalReview[$key]['review_images'] = $reviewImages;             
            }else{
                 $finalReview[$key]['review_images'] = array();
            }
            ###################
       }
       //pr($finalReview,true);
        $limit = ($this->limit)?$this->limit:SHOW_PER_PAGE;//('limit',SHOW_PER_PAGE);
        $page = $this->page;
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * ($limit);
        }
       
        $tips = $this->getUserTips($commonFucntions);        
        $final1 = array_merge($this->formatRestaurantReview, $finalReview, $tips);
        
        $totalReviews = $this->userReviewForRestaurant['total_count']+$this->totalRestaurantTips['total_count']+$this->restaurantTotalReview['total_count'];
        foreach ($final1 as $key => $val) {
            $sortDate[$key] = strtotime($val['sort_date']);           
        }
        if (!$order || $order == 'date' || $order == 'type') {
            if ($final1) {
                array_multisort($sortDate, SORT_DESC, $final1);
            }
        } elseif ($order == 'rating') {
            uasort($final1, array(
                $this,
                'rating_compare'
            ));
        }

        $final = array_slice($final1, $offset, $limit);
        
        $percentPositive = $this->positiveSentimentPercentage();                 
         //pr($totalReviews,true);   
        return array(
            'reviews'=>$final,
            'positive_sentiment_percent'=>$percentPositive,
            'total_review_count'=>$totalReviews,
        );
    }
    
    private function getRestaurantAccount() {
        $restaurantAccount = new RestaurantAccounts ();
        $resAcc = $restaurantAccount->getRestaurantAccountDetail(array(
            'columns' => array(
                'restaurant_id'
            ),
            'where' => array(
                'restaurant_id' => $this->restaurantId,
                'status' => 1
            )
        ));
        if($resAcc){
            return true;
        }
        return false;
    }
    
    private function getCityDetails($cityId){
        $cityModel = new City ();
        $cityData = $cityModel->fetchCityDetails($cityId);
        return $cityData;
    }
    private function formatDate($value) {
        $commonFucntions = new CommonFunctions ();
        $value['type']='restaurant';
        if(isset($value['date']) && $value['date']=="1970-01-01"){
            $value ['review_date'] = StaticOptions::getFormattedDateTime('2014-05-11', 'Y-m-d', 'd M Y');
        }
        if(isset($value ['date']) && strtotime($value ['date']) < strtotime("1980-01-01")){
            $value ['review_date'] = StaticOptions::getFormattedDateTime('2014-05-11', 'Y-m-d', 'd M Y');
        }
        $value ['date'] = ($value ['date']=="0000-00-00" || $value ['date']=='' || $value ['date']==NULL)?"2014-05-11":$value ['date'];
        //pr($value ['date']);
        $value ['date'] = $commonFucntions->datetostring($value ['date']);
        //$value ['date'] = $commonFucntions->datetostring('1914-07-01');
        if(isset($value ['sort_date']) && strtotime($value ['sort_date']) > strtotime("1980-01-01")){
            $value ['sort_date'] = StaticOptions::getFormattedDateTime($value ['sort_date'], 'Y-m-d', 'Y-m-d');
        }else{
            $value ['sort_date'] = StaticOptions::getFormattedDateTime('2014-05-11', 'Y-m-d', 'Y-m-d');
        }
        $this->formatRestaurantReview [] = $value;
    }
    private function mapper($value, $key) {
        foreach ($value as $single) {
            if(isset($single ['user_id'])){
            $this->formatCount [$key] [$single ['user_id']] = $single;
            }
        }
    }
}
