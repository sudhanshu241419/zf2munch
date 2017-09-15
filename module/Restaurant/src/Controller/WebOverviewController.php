<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\RestaurantReview;
use Restaurant\Model\Restaurant;
use Restaurant\Model\Image;
use Restaurant\RestaurantDetailsFunctions;
use User\Model\UserReview;
use Restaurant\Model\Feature;

class WebOverviewController extends AbstractRestfulController {

    public function get($id) {
        $masterResponse = array();
        $reviewModel = new RestaurantReview ();
        $userReviewModel = new UserReview ();
        $userReviewCountOptions = array(
            'columns' => array(
                'total' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $id,
                'status' => 1
            )
        );
        $countOptions = array(
            'columns' => array(
                'total' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $id,
                'review_type' => 'N'
            )
        );
        $reviewOptions = array(
            'columns' => array(
                'review' => 'reviews'
            ),
            'where' => array(
                'restaurant_id' => $id,
                'sentiments' => 'Positive'
            ),
            'order' => array(
                'date' => 'desc'
            ),
            'limit' => 1
        );
        $reviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $reviewCount = $reviewModel->find($countOptions)->current()->getArrayCopy();
        $userReviewCount = $userReviewModel->find($userReviewCountOptions)->current()->getArrayCopy();
        $consolidatedReview = $reviewModel->find($reviewOptions)->toArray();
        // Commented this code to change the order

        /*
         * $masterResponse ['review'] ['status'] = ! empty ( $consolidatedReview ) ? true : false; $masterResponse ['review'] ['count'] = $reviewCount ['total'] + $userReviewCount ['total']; $masterResponse ['review'] ['detail'] = ! empty ( $consolidatedReview ) ? $consolidatedReview [0] ['review'] : '';
         */
        
//         $restaurantDealsModel = new DealsCoupons (); 
//         $options = array ( 
//             'columns' => array ( 
//                 'title', 
//                 'description', 
//                 'price', 
//                 'discount', 
//                 'discount_type', 
//                 'image', 
//                 'coupon_code' ), 
//             'where' => array ( 
//                 'status' => 1 
//                 ) ); 
//         $restaurantDealsModel->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' ); 
//         $deals = $restaurantDealsModel->find ( $options )->toArray (); 
//         $masterResponse ['deals'] ['status'] = true; 
//         $masterResponse ['deals'] ['detail'] = '';
         

        $restaurantModel = new Restaurant ();
        $joins = array();
        $joins [] = array(
            'name' => array(
                'rs' => 'restaurant_stories'
            ),
            'on' => 'rs.restaurant_id = restaurants.id',
            'columns' => array(
                'title' => 'title'
            ),
            'type' => 'left'
        );
        $options = array(
            'columns' => array(
                'description'
            ),
            'joins' => $joins,
            'where' => array(
                'restaurants.id' => $id
            )
        );
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $detailResponse = $restaurantModel->find($options)->current()->getArrayCopy();
        $masterResponse ['story'] ['status'] = $detailResponse ['title'] != null ? true : false;
        $masterResponse ['story'] ['detail'] = $detailResponse ['title'];
        $masterResponse ['review'] ['status'] = !empty($consolidatedReview) ? true : false;
        $masterResponse ['review'] ['count'] = $reviewCount ['total'] + $userReviewCount ['total'];
        $masterResponse ['review'] ['detail'] = !empty($consolidatedReview) ? $consolidatedReview [0] ['review'] : '';

        // $masterResponse ['overview'] ['status'] = $detailResponse ['description'] != null ? true : false;
        // $masterResponse ['overview'] ['detail'] = $detailResponse ['description'];
        $masterResponse ['about'] ['status'] = $detailResponse ['description'] != null ? true : false;
        $masterResponse ['about'] ['detail'] = $detailResponse ['description'];
        $restaurantImageModel = new Image ();
        $options = array(
            'columns' => array(
                'image',
                'image_type'
            ),
            'where' => array(
                'restaurant_id' => $id,
                'status' => 1
            ),
            'limit' => 6
        );
        $restaurantImageModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $restaurantImages = $restaurantImageModel->find($options)->toArray();
        $masterResponse ['gallery'] ['status'] = count($restaurantImages) > 0 ? true : false;
        $masterResponse ['gallery'] ['detail'] = $restaurantImages;
        $resturantFeatures = new Feature ();
        $resturantFeatures->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'f' => 'features'
            ),
            'on' => 'f.id = restaurant_features.feature_id',
            'columns' => array(
                'features' => 'features',
                'feature_type',
                'features_key'
            ),
            'type' => 'inner'
        );
        $options = array(
            'columns' => array(
                'id'
            ),
            'joins' => $joins,
            'where' => array(
                'restaurant_features.status' => 1,
                'f.status' => 1,
                'restaurant_id' => $id
            )
        );
        $features = $resturantFeatures->find($options)->toArray();
        $masterResponse ['features'] ['detail'] = $features;
        $restaurantDetailsFunctions = new RestaurantDetailsFunctions ();        
        if($restaurantDetailsFunctions->isRestaurantOpenTwentyFourHours($id)){
            $masterResponse ['calendar'] ['open_twenty_four_hours'] = 'Open 24 hours! All year around!';
            $masterResponse ['calendar'] ['detail'] = null;            
        }else{
            $finalTimings = $restaurantDetailsFunctions->getRestaurantDisplayTimings($id);
            $masterResponse ['calendar'] ['open_twenty_four_hours'] = '';
            $masterResponse ['calendar'] ['detail'] = $finalTimings;
        }
        
        $i = 0;
        foreach ($masterResponse as $single) {
            if (isset($single ['status']) && $single ['status']) {
                $i ++;
            }
        }
        $masterResponse ['deals'] ['status'] = false;
        $masterResponse ['deals'] ['detail'] = null;
        $masterResponse ['count'] = $i;
        return $masterResponse;
    }

}
