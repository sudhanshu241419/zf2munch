<?php

namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class UserReview extends AbstractModel {

    public $id;
    public $user_id;
    public $restaurant_id;
    public $review_for;
    public $on_time = 0;
    public $fresh_prepared = 0;
    public $temp_food = 2;
    public $as_specifications = 0;
    public $taste_test;
    public $services;
    public $noise_level;
    public $rating = 0;
    public $order_again = 0;
    public $come_back = 0;
    public $review_desc;
    public $created_on;
    public $status = 0;
    public $approved_by = 0;
    public $sentiment;
    public $order_id = 0;
    public $replied = 0;
    public $restaurant_response;
    public $userReviewForRestaurant;

    const REVIEW_STATUS = 1;
    const REPLIED_STATUS = 0;
    const COME_BACK_NO = 2;
    CONST UNACCEPTABLY_CLASS = 'service-unacceptably-unfriendly-preview'; //'unacceptablyIcon';
    CONST JUSTRIGHT_CLASS = 'service-just-right-preview'; //'JustrightIcon';
    CONST EXTREMELY_NICE_CLASS = 'service-extremely-nice-preview'; //'extremelyniceIcon';
    CONST LOUD_CLASS = 'loudIcon';
    CONST NORMAL_CLASS = 'normalIcon';
    CONST CONVERSATIONAL_CLASS = 'conversationaIcon';
    CONST HORRIBLE_CLASS = 'taste-test-horrible-preview'; //negativeIcon'; 
    CONST COULD_BETTER_CLASS = 'taste-test-ok-preview'; //couldbetterIcon';
    CONST LOVED_IT_CLASS = 'taste-test-loved-it-preview'; //'OkIcon';
    CONST YES_CLASS = 'yesIcon';
    CONST NO_CLASS = 'notIcon';
    const READED = 1;
    const UNREADED = 0;

    protected $_db_table_name = 'Dashboard\Model\DbTable\UserReviewTable';

    public function getReviews(array $options = array()) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'id',
            'restaurant_id',
            'user_id',
            'review_desc',
            'review_for',
            'sentiment',
            'on_time',
            'fresh_prepared',
            'as_specifications',
            'temp_food',
            'taste_test',
            'order_again',
            'rating',
            'created_on'
        ));

        $select->join(array(
            'uri' => 'user_review_images'
                ), 'uri.user_review_id = user_reviews.id', array(
            'image_path' => new Expression('if(uri.image is NULL,"' . REST_DEFAULT_IMAGE . '",uri.image)')
                ), $select::JOIN_LEFT);

        $select->join(array(
            'u' => 'users'
                ), 'u.id = user_reviews.user_id', array(
            'first_name',
            'last_name',
            'display_pic_url' => new Expression('if(display_pic_url is NULL,"' . REST_DEFAULT_IMAGE . '",display_pic_url)'),
            'created_at'
                ), $select::JOIN_LEFT);

        $select->join(array(
            'ua' => 'user_addresses'
                ), 'ua.user_id = user_reviews.user_id', array(
            'city' => new Expression('if(city is NULL,"",city)')
                ), $select::JOIN_LEFT);

        $select->where(array(
            'user_reviews.restaurant_id' => $options ['columns'] ['restaurant_id'],
            'user_reviews.status' => 1
        ));

        //pr($select->getSqlString($this->getPlatform('READ')),1);

        $reviewDetail = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $reviewDetail;
    }

    public function getTotalUserRreview(array $options = array()) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_review' => new Expression('COUNT(user_reviews.id)')
        ));
        $select->where(array(
            'user_reviews.user_id' => $options ['columns'] ['user_id'],
            'user_reviews.status' => 1
        ));
        $select->group('user_reviews.user_id');
        //var_dump($select->getSqlString($this->getPlatform('READ')));
        $totalReview = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $totalReview;
    }

    public function createReview() {
        $data = $this->toArray();
        $writeGateway = $this->getDbTable()->getWriteGateway();
        if (!$this->id) {
            $rowsAffected = $writeGateway->insert($data);
        } else {
            $rowsAffected = $writeGateway->update($data, array(
                'id' => $this->id
            ));
        }

        $lastInsertId = $writeGateway->getAdapter()->getDriver()->getLastGeneratedValue();

        if ($rowsAffected >= 1) {
            $this->id = $lastInsertId;
            return $this->toArray();
        }
        return false;
    }

    public function insert($data) {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $rowsAffected = $writeGateway->insert($data);
        $lastInsertId = $writeGateway->getAdapter()->getDriver()->getLastGeneratedValue();
        return $lastInsertId;
    }

    public function getUserReviews($user_id) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'id',
            'user_id',
            'restaurant_id',
            'review_for',
            'created_on',
            'review_desc',
            'order_id',
            'rating',
            'status'
        ));

        $select->join(array(
            'r' => 'restaurants'
                ), 'r.id = user_reviews.restaurant_id', array(
            'restaurant_name',
            'rest_code'
                ), $select::JOIN_LEFT);

        $select->join(array(
            'umr' => 'user_menu_reviews'
                ), 'umr.user_review_id = user_reviews.id', array(
            'image_path' => 'image_name'
                ), $select::JOIN_LEFT);
        $select->where(array(
            'user_reviews.user_id' => $user_id,
            'user_reviews.status' => 1
        ));

        //var_dump($select->getSqlString($this->getPlatform('READ')));

        $reviewDetail = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $reviewDetail->toArray();
    }

    public function getUserReviewDetail($user_id, $review_id) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'id',
            'user_id',
            'restaurant_id',
            'review_for',
            'created_on',
            'review_desc',
            'order_id',
            'rating',
            'status',
            'on_time',
            'fresh_prepared',
            'as_specifications',
            'temp_food',
            'taste_test',
            'order_again',
            'services',
            'noise_level',
            'come_back'
        ));

        $select->join(array(
            'r' => 'restaurants'
                ), 'r.id = user_reviews.restaurant_id', array(
            'restaurant_name',
            'rest_code'
                ), $select::JOIN_LEFT);

        $select->join(array(
            'uod' => 'user_order_details'
                ), 'uod.user_order_id = user_reviews.order_id', array(
            'item_id' => 'id',
            'item_name' => 'item'
                ), $select::JOIN_LEFT);

        $select->join(array(
            'umr' => 'user_menu_reviews'
                ), 'umr.user_review_id = user_reviews.id', array(
            'image_path' => 'image_name'
                ), $select::JOIN_LEFT);
        $select->where(array(
            'user_reviews.user_id' => $user_id,
            'user_reviews.id' => $review_id,
            'user_reviews.status' => 1
        ));

        //var_dump($select->getSqlString($this->getPlatform('READ')));die;

        $reviewDetail = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $reviewDetail->toArray();
    }

    public function getUserMenuReviews($user_id) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());

        $select->columns(array(
            'id',
            'user_id',
            'restaurant_id',
            'review_for',
            'created_on',
            'review_desc',
            'order_id',
            'rating',
            'status'
        ));

        $select->join(array(
            'umr' => 'user_menu_reviews'
                ), 'umr.user_review_id = user_reviews.id', array(
            'image_path' => 'image_name'
                ), $select::JOIN_LEFT);

        $select->join(array(
            'm' => 'menues'
                ), 'm.id = umr.menu_id', array(
            'menu_name' => 'item_name'
                ), $select::JOIN_LEFT);

        $select->where(array(
            'user_reviews.user_id' => $user_id,
            'm.id = umr.menu_id',
            'user_reviews.status' => 1
        ));

        //var_dump($select->getSqlString($this->getPlatform('READ')));

        $reviewDetail = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $reviewDetail->toArray();
    }

    public function getUserTotalRreview($userId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_review' => new Expression('COUNT(user_reviews.id)')
        ));
        $select->where(array(
            'user_reviews.user_id' => $userId,
            'user_reviews.status' => array(0, 1, 2)
        ));

        $totalReview = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        //pr($select->getSqlString($this->getPlatform('READ')),true);
        return $totalReview;
    }

    public function getRestaurantReviewCount($restaurant_id = 0) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_count' => new Expression('COUNT(restaurant_id)')
        ));
        $select->where(array(
            'restaurant_id' => $restaurant_id,
            'status' => 1
        ));
        $totalReview = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        $this->userReviewForRestaurant = $totalReview;
        return $totalReview;
    }

    public function getAllUserReview(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $reservations = $this->find($options)->toArray();
        return $reservations;
    }

    public function updateReview() {
        $data = array(
            'replied' => $this->replied,
        );

        $writeGateway = $this->getDbTable()->getWriteGateway();
        $dataUpdated = array();
        if ($this->id == 0) {
            throw new \Exception("Invalid review ID provided", 500);
        } else {
            $dataUpdated = $writeGateway->update($data, array(
                'id' => $this->id
            ));
        }
        if ($dataUpdated) {
            return true;
        } else {
            return false;
        }
    }

    public function delete() {
        $writeGateway = $this->getDbTable()->getWriteGateway();
        $data = array(
            'status' => 3
        );
        $rowsAffected = $writeGateway->update($data, array('id' => $this->id));

        if ($rowsAffected) {
            return true;
        } else {
            return false;
        }
    }

    public function UserMenuTotalReview($userId) {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array());
        $select->join(array(
            'rew' => 'user_menu_reviews'
                ), 'user_review_id=user_reviews.id', array('id', 'image_name'), $select::JOIN_INNER);
        $select->join(array(
            'res' => 'restaurants'
                ), 'res.id=user_reviews.restaurant_id', array(), $select::JOIN_INNER);
        $select->where(array(
            'user_reviews.user_id' => $userId
        ));
        //pr($select->getSqlString($this->getPlatform('READ')),true);
        $userEatingHabitDetails = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        $menuImages = $userEatingHabitDetails->toArray();
        $totalMenuImages = 0;
        if (count($menuImages) > 0) {
            foreach ($menuImages as $key => $val) {
                if (!empty($val['image_name'])) {
                    $totalMenuImages += 1;
                }
            }
        }
        return (int) $totalMenuImages;
    }

    public function getUserAllReview($userId = false) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('id', 'restaurant_id'));
        $select->where(array('user_id' => $userId, 'assignMuncher' => '0', 'status' => '1'));
        $select->where->notEqualTo('review_desc', '');
        $totalCheckin = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select);
        return $totalCheckin->toArray();
    }

    public function updateMuncher($data) {
        $this->getDbTable()->getWriteGateway()->update($data, array(
            'id' => $this->id
        ));
        return true;
    }

    public function updateCronOrder($id = false) {
        $this->getDbTable()->getWriteGateway()->update(array('cronUpdate' => 1), array(
            'id' => $id
        ));
        return true;
    }

    /*  this function is used to get the winners data
     *  No parameter required
     *  find data where winner status has 4
     *  five tables are require to get the data(user_reviews,user_review_images,user_restaurant_image)
     */

    public function getWinnersUserRestaurantIds() {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'restaurant_id',
            'user_id', 'type' => new Expression("'userReview'")
        ));
        $select->join(array('uri' => 'user_review_images'), 'uri.user_review_id = user_reviews. id', array('id', 'created_at', 'image_path' => new Expression('image_url')), $select::JOIN_INNER
        );
        $select->where(array(
            'uri.image_status' => '4',
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $openNightData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $openNightData;
    }

    public function getMenuWinnersUserRestaurantIds() {
        $select = new Select ();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'restaurant_id',
            'user_id', 'type' => new Expression("'userReview'"), 'created_at' => new Expression('user_reviews.created_on')
        ));
        $select->join(array('uri' => 'user_menu_reviews'), 'uri.user_review_id = user_reviews. id', array('id', 'image_path' => new Expression('image_name')), $select::JOIN_INNER
        );
        $select->join(array('r' => 'restaurants'), 'r.id= user_reviews.restaurant_id', array('rest_code'), $select::JOIN_INNER
        );
        $select->where(array(
            'uri.image_status' => '4',
        ));
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $openNightData = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        //$path=WEB_URL.APP_PUBLIC_PATH.USER_IMAGE_UPLOAD .strtolower($restDetail['rest_code']) . DS . 'reviews' . DS;
        if (count($openNightData) > 0) {

            foreach ($openNightData as $key => $val) {
                $path = WEB_URL . USER_IMAGE_UPLOAD . strtolower($val['rest_code']) . DS . 'reviews' . DS . $val['image_path'];
                $openNightData[$key]['image_path'] = $path;
            }
        }
        return $openNightData;
    }

    public function getRestaurantReviewsRatings($restId) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'user_id',
            'rating',
        ));
        $select->join(array(
            'rs' => 'restaurant_servers'
                ), 'rs.user_id = user_reviews.user_id', array(
                ), $select::JOIN_INNER);
        $where = new Where ();
        $where->equalTo('user_reviews.restaurant_id', $restId);
        $where->equalTo('user_reviews.status', '1');
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->toArray();
        $rating = [];
        $positive = 0;
        $negative = 0;
        if (!empty($data)) {
            foreach ($data as $value) {
                if ($value['rating'] >= 3) {
                    $positive ++;
                } else {
                    $negative ++;
                }
            }
        }
        $rating['positive'] = $positive;
        $rating['negative'] = $negative;
        return $rating;
    }

    public function dashboardTotalUserReviews($restaurantId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_review' => new Expression('COUNT(id)')
        ));
        $select->where(array(
            'restaurant_id' => $restaurantId, 'status' => 1
        ));

        //var_dump($select->getSqlString($this->getPlatform('READ')));

        $totalReview = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $totalReview;
    }

    public function dashboardTotalNegativeReviews($restId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_review' => new Expression('COUNT(id)')
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('status', '1');
        $where->lessThan('rating', '3');
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));
        $totalReview = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $totalReview;
    }

    public function getTotalUserReviews($restId, $userId) {
        $select = new Select();
        $select->from($this->getDbTable()
                        ->getTableName());
        $select->columns(array(
            'total_reviews' => new Expression('COUNT(id)'),
        ));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('user_id', $userId);
        $select->where($where);
        // var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $data = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        if (!empty($data)) {
            return $data['total_reviews'];
        } else {
            return 0;
        }
    }

    public function getDashboardRestaurantUserReviews($options) {
        $rlists = array();
        $sorting = "created_on DESC";
        if ($options['orderby'] == 'type') {
            $sorting = "review_for ASC ";
        } else if ($options['orderby'] == 'user_id') {
            $sorting = "user_id DESC";
        } else if ($options['orderby'] == 'rating') {
            $sorting = "rating DESC";
        } else if ($options['orderby'] == 'date') {
            $sorting = "created_on DESC";
        }
        $records = $this->getRestaurantUserReviews($options, $sorting);
        //pr($records,1);
        if (!empty($records)) {
            foreach ($records as $key => $value) {
                if ($value['review_for'] == 1) {
                    $reviewFor = 'Delivery';
                    $display_icon = 'deliveryIcon';
                } elseif ($value['review_for'] == 2) {
                    $reviewFor = 'Takeout';
                    $display_icon = 'takeoutIcon';
                } elseif ($value['review_for'] == 3) {
                    $reviewFor = 'Dine-In';
                    $display_icon = 'dineInIcon';
                } else {
                    $reviewFor = 'Delivery';
                    $display_icon = 'deliveryIcon';
                }
                $value['username'] = '&nbsp;';
                $userModel = new User();
                $username = $userModel->getUserName($value['user_id']);
                if ($username) {
                    $value['username'] = $username;
                }
                $value['review_for'] = $reviewFor;
                $value['class_icon'] = $display_icon;
                $value['created_on'] = date('M d, Y', strtotime($value['created_on']));
                if (strlen($value['review_desc']) > 63)
                    $value['review_desc'] = substr($value['review_desc'], 0, 63) . ' ...';
                $menuReviewModel = new UserMenuReview();
                $value['image'] = $menuReviewModel->getUserReviewImageByorder($value['id'], $value['review_for'], $options['restaurant_id']);
                $rlists[$key] = $value;
            }
        }
        if ($options['orderby'] == 'user_id') {
            if ($rlists) {
                foreach ($rlists as $key1 => $value1) {
                    $name[] = $value1['username'];
                }
                array_multisort($name, SORT_ASC, $rlists);
            }
        }
        $reviews = array_slice($rlists, $options['offset'], $options['limit']);
        return $reviews;
    }

    public function getRestaurantUserReviews($options, $sorting) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $options['restaurant_id']);
        $where->equalTo('status', '1');
        $select->where($where);
        $select->order($sorting);
        //var_dump($select->getSqlString($this->getPlatform('READ')));
        $totalReview = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->toArray();
        return $totalReview;
    }

    public function getUserReviewDetails($id, $restId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('id', $id);
        $select->where($where);
        $data = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        $ownerResModel = new OwnerResponse();
        $ownerResponce = $ownerResModel->getOwnerResponce($id);
        if (!empty($data)) {
            if ($ownerResponce) {
                $n = array();
                foreach ($ownerResponce as $key => $value) {
                    $n[] = array('response' => $value['response'], 'response_date' => date('M d, Y', strtotime($value['response_date'])));
                }
            }
            $data['restaurant_response'] = isset($n) ? $n : array();
            $data['review_type'] = $this->review_type($data['review_for']);
            $services = $data['services'];
            if ($services == '1') {
                $data['services_class'] = self::UNACCEPTABLY_CLASS;
            } else if ($services == '2') {
                $data['services_class'] = self::JUSTRIGHT_CLASS;
            } else if ($services == '3') {
                $data['services_class'] = self::EXTREMELY_NICE_CLASS;
            } else {
                $data['services_class'] = self::EXTREMELY_NICE_CLASS;
            }
            $data['services'] = $this->services_type($data['services']);
            $taste_test = $data['taste_test'];
            if ($taste_test == '1') {
                $data['taste_class'] = self::HORRIBLE_CLASS;
            } else if ($taste_test == '2') {
                $data['taste_class'] = self::COULD_BETTER_CLASS;
            } else if ($taste_test == '2') {
                $data['taste_class'] = self::LOVED_IT_CLASS;
            } else {
                $data['taste_class'] = self::LOVED_IT_CLASS;
            }
            $data['taste_test'] = $this->taste_test_type($data['taste_test']);
            $noise_level = $data['noise_level'];
            if ($noise_level == '1') {
                $data['noise_class'] = self::CONVERSATIONAL_CLASS;
            } else if ($noise_level == '2') {
                $data['noise_class'] = self::NORMAL_CLASS;
            } else if ($noise_level == '3') {
                $data['noise_class'] = self::LOUD_CLASS;
            } else {
                $data['noise_class'] = self::LOUD_CLASS;
            }

            $data['noise_level'] = $data['noise_level']; //self::noise_level_type($data['noise_level']);
            $order = $data['order_again'];
            if ($order == '1') {
                $data['order_class'] = self::YES_CLASS;
            } else if ($order == '2') {
                $data['order_class'] = self::NO_CLASS;
            }
            $data['order_again'] = $data['order_again']; //self::order_again_type($data['order_again']);
            $data['created_on'] = date("M d, Y", strtotime($data['created_on']));
            $options = array(
                'columns' => array(
                    'created_at',
                    'first_name',
                    'display_pic_url',
                    'billing_address',
                    'email',
                ),
                'where' => array('id' => $data['user_id'])
            );
            $userModel = new User();
            $userDetails = $userModel->getUserDetail($options); //User::get_details($data['user_id']);
            if (!empty($userDetails)) {
                $data['posted_by'] = $userDetails['first_name'];
                $data['Joined'] = $this->joinbefore($userDetails['created_at']);
                if ($userDetails['display_pic_url'] == 'NULL' || $userDetails['display_pic_url'] == '') {
                    $data['user_image'] = $this->checkImage('no_img.jpg');
                } else {
                    $data['user_image'] = $this->checkImage($userDetails['display_pic_url'], $data['user_id']);
                }
                $data['user_address'] = $userDetails['billing_address'];
            }
            if(empty($data['user_id'])){
                $email = $userDetails['email'];
            }else{
                $email = '';
            }
            $data['statistic_details'] = $this->getUserStaticsDetails($data['user_id'],$email, $restId);
            $menuReviewModel = new UserMenuReview();
            $Image = $menuReviewModel->getUserReviewImageByorder($data['id'], $data['review_type'], $restId);
            //$Image = UserMenuReview::get_user_review_image_byorder($data['id'], $data['review_type'], $restaurant_id);
            $data['review_image'] = $Image;
            $data['readflag'] = 0;
            // echo "dsd";die;

            if ($data['is_read'] == 0) {
                $this->update_read_status($id);
                $data['readflag'] = 1;
            }
            return $data;
        }
    }

    public function joinbefore($start, $end = "NOW") {
        $timeshift = "";
        $sdate = strtotime($start);
        $edate = strtotime($end);

        $time = $edate - $sdate;

        if ($edate > $sdate) {
            if ($time >= 0 && $time <= 59) {
                // Seconds
                $timeshift = $time . ' Seconds ago';
            } elseif ($time >= 60 && $time <= 3599) {
                // Minutes 
                $pmin = $time / 60;
                $premin = explode('.', $pmin);
                $timeshift = $premin[0] . ' Minutes ago';
            } elseif ($time >= 3600 && $time <= 86399) {
                // Hours 
                $phour = $time / 3600;
                $prehour = explode('.', $phour);
                $timeshift = $prehour[0] . ' Hours ago';
            } elseif ($time >= 86400 && $time <= 2591999) {
                // Days
                $pday = $time / 86400;
                $preday = explode('.', $pday);
                $timeshift = $preday[0];
                if ($preday[0] <= 30) {
                    $timeshift = $preday[0] . ' Days ago';
                }
            } elseif ($time >= 2592000 && $time <= 946079999) {
                // Months
                $pmonth = $time / 2592000;
                $premonth = explode('.', $pmonth);
                $timeshift = $premonth[0];
                if ($pmonth[0] <= 12) {
                    $timeshift = $premonth[0] . ' Months ago';
                }
            } elseif ($time >= 946080000) {
                //year
                $pyear = $time / 946080000;
                $preyear = explode('.', $pyear);
                $timeshift = $preyear[0] . ' Year ago';
            }
        }
        return $timeshift;
    }

    public function review_type($review_for) {
        if ($review_for == '1') {
            return 'Delivery';
        } else if ($review_for == '2') {
            return 'Takeout';
        } else if ($review_for == '3') {
            return 'Dine-In';
        }
    }

    public function services_type($services) {
        if ($services == '1') {
            return 'Un-acceptable Unfriendly';
        } else if ($services == '2') {
            return 'Just right';
        } else if ($services == '3') {
            return 'Extremly nice';
        } else {
            return '0';
        }
    }

    public function taste_test_type($taste_test) {
        if ($taste_test == '1') {
            return 'Horrible';
        } else if ($taste_test == '2') {
            return 'Ok but could be better';
        } else if ($taste_test == '3') {
            return 'Loved it';
        } else {
            return '0';
        }
    }

    public function noise_level_type($noise_level) {
        if ($noise_level == '1') {
            return 'Quiet and conversational';
        } else if ($noise_level == '2') {
            return 'Normal';
        } else if ($noise_level == '3') {
            return 'Loud';
        } else {
            return '0';
        }
    }

    public function order_again_type($order_again) {
        if ($order_again == '1') {
            return 'Yes, would order again';
        } else if ($order_again == '2') {
            return 'No, would not order again';
        } else {
            return '0';
        }
    }

    public function checkuploadImage($image) {
        if (preg_match('/http/', strtolower($image))) {
            return $image;
        } else {
            return USER_UPLOADED_IMAGE_BASE_PATH . $image;
        }
    }

    public function checkImage($image, $user_id = null) {
        if (preg_match('/http/', strtolower($image))) {
            return $image;
        } elseif ($image == 'no_img.jpg') {
            return REST_DEFAULT_IMAGE;
        } else {
            return USER_REVIEW_IMAGE . 'profile/' . $user_id . '/' . $image;
        }
    }

    public function update_read_status($id) {
        $update = $this->getDbTable()->getWriteGateway()->update(array('is_read' => self::READED), array(
            'id' => $id
        ));
        if ($update) {
            return true;
        } else {
            return false;
        }
    }

    public function getUserStaticsDetails($userId,$email, $restId) {
        $orderModel = new DashboardOrder();
        $reservationModel = new DashboardReservation();
        $reviewModel = new UserReview();
        $totalOrders = $orderModel->getTotalUserOrder($userId, $email, $restId);
        $data['total_order_count'] = ($totalOrders) ? $totalOrders[0]['total_order'] : 0 ;
        $data['total_reservation_count'] = $reservationModel->getTotalUserReservations($restId, $userId, $email);
        $data['total_reviews_count'] = $reviewModel->getGuestReviewsAndTips($userId, $restId);
        return $data;
    }
    public function update($id,$data) {
        $this->getDbTable()->getWriteGateway()->update($data, array(
            'id' => $id
        ));
        return true;
    }
    public function getReviewDetails($id,$restId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array('*'));
        $where = new Where ();
        $where->equalTo('restaurant_id', $restId);
        $where->equalTo('id', $id);
        $select->where($where);
        $data = $this->getDbTable()->setArrayObjectPrototype('ArrayObject')->getReadGateway()->selectWith($select)->current();
        if(!empty($data)){
            return $data;
        }else{
            return [];
        }
    }
    public function getGuestReviewsAndTips($id, $restId) {
        $select = new Select();
        $select->from($this->getDbTable()->getTableName());
        $select->columns(array(
            'total_reviews' => new Expression('COUNT(user_id)')
        ));
        $where = new Where ();
        $where->equalTo('user_id', $id);
        $where->equalTo('restaurant_id', $restId);
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ')));die;
        $records = $this->getDbTable()
                        ->setArrayObjectPrototype('ArrayObject')
                        ->getReadGateway()
                        ->selectWith($select)->current();
        $reviews = empty($records) ? 0 : $records['total_reviews'];
        return $reviews;
    }
}
