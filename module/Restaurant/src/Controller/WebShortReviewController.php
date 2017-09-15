<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Review;
use User\Model\UserReview;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class WebShortReviewController extends AbstractRestfulController {

    public function get($restaurant_id = 0) {
        $response = array();
        $review = array();
        if (!$restaurant_id)
            throw new \Exception("Invalid Parameters", 400);

        // Get consolidate review and description
        $reviewModel = new Review ();
        $userReviewModel = new UserReview();


         //$reviews = $reviewModel->findReviews();
        $consolidatedReviews = $reviewModel->getReviews(array(
            'columns' => array(
                'consolidated_review' => 'reviews',
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,
                'review_type' => 'C'
            )
                )
        );
//        $consolidatedReviewCount = count($consolidatedReviews);



        $NormalReviews = $reviewModel->getReviews(array(
            'columns' => array(
                'consolidated_review' => 'reviews',
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,
                'review_type' => 'N'
            )
                )
        );

        $NormalReviewCount = count($NormalReviews);

        $positiveSentiments = $reviewModel->getReviews(array(
            'columns' => array(
                'positive_review' => 'reviews',
                'created_on'=>'date'
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,
                'sentiments' => 'Positive'                
            ),
            //'limit'=>'1',
            'order'=>'id desc'
                )
        );
        $select = new Select();
        $select->from('user_reviews');
        $select->columns(array(	
				'positive_review' =>'review_desc','created_on'                
		));
        $where = new Where();
        $where->NEST->equalTo('restaurant_id', $restaurant_id)->AND->equalTo('sentiment', 1)->AND->equalTo('status', 1)->AND->notEqualTo('review_desc','')->UNNEST;
        //$select->limit (1);
        $select->order('id desc');
        $select->where($where);
        //pr($select->getSqlString($reviewModel->getPlatform('READ')),true);
        $positiveUserSentiments = $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        
        $positiveSentimentsCount = count($positiveSentiments) + count($positiveUserSentiments);
       // pr($positiveSentimentsCount,1);
        if ($positiveSentimentsCount>=1) {
            //$postiveReview = array_pop($positiveSentiments);
            if(count($positiveSentiments)>0){
                foreach($positiveSentiments as $key=>$value){
                    $positiveSentiments[$key]['created_on']=$value['created_on'].' 00:00:00';
                }
            }
            $postiveReview = array_merge($positiveSentiments,$positiveUserSentiments);
            if($postiveReview){
                foreach ($postiveReview as $key => $val) {
                    $sortDate[$key] = strtotime($val['created_on']);           
                }
                 array_multisort($sortDate, SORT_DESC, $postiveReview);
                 $postiveReview = current($postiveReview);
            }
            
        } else {
            $postiveReview = '';
        }

        //count user reviews also
        $userReviewCountOptions = array(
            'columns' => array(
                'total' => new \Zend\Db\Sql\Expression('COUNT(*)')
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,
                'status' => 1
            )
        );
        $userReviewModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $userReviewCount = $userReviewModel->find($userReviewCountOptions)->current()->getArrayCopy();
        $total_review_count = $NormalReviewCount + $userReviewCount['total'];
        $review = array();
        $review['total_review_count'] = $total_review_count;
        $review['consolidated_review'] = isset($postiveReview['positive_review']) ? $postiveReview['positive_review'] : "";
        //changed without taking consent. Need to be verified.
        $review['positive_sentiment_count'] = $total_review_count != 0 ? ceil(($positiveSentimentsCount * 100) / $total_review_count) . '%' : '0%';
        return $review;
    }

}
