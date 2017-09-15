<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Restaurant;
use Zend\Db\Sql\Predicate\Expression;

class MyReservationSearchController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $userId = $session->getUserId();
        if (!$isLoggedIn) {
            throw new Exception('Ivalid user');
        }
         $q = $this->getQueryParams('q');
       
        if (strlen($q) < 3) {
            throw new \Exception('You need to enter atleast 3 characters');
        }
        $restaurantModel = new Restaurant ();
        $restaurantModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $joins = array();
        $joins [] = array(
            'name' => array(
                'ur' => 'user_reservations'
            ),
            'on' => new Expression("restaurants.id = ur.restaurant_id AND ur.user_id=" . $userId),
            'columns' => array(
                'reservation_id'=>'id',
                'receipt_no',				
				'reservation_member_count'=>'party_size',
				'reservation_date'=>'time_slot',
				'reservation_created_on'=>'reserved_on',
				'first_name',
				'user_instruction',
                'status',
                'is_reviewed',
                'review_id',
                'user_id'
            ),
            'type' => 'inner'
        );

        $options = array(
            'columns' => array(                
                'is_restaurant_exists' => new Expression('if(inactive = 1 or closed = 1,0,1)'),
                'restaurant_id'=>'id',
				'restaurant_name',
            ),
            'like' => array(
                'field' => 'restaurants.restaurant_name',
                'like' => '%' . $q . '%'
            ),
            'where' => new Expression('(reservations = 1 OR dining = 1)'),
            'joins' => $joins,
//            'limit' => 10,
            'order' => array(
                'ur.time_slot' => 'desc'
            )
        );
        $response = $restaurantModel->find($options)->toArray(); 
        foreach($response as $key => $value){
            $response[$key]['is_restaurant_exist'] = (int)$value['is_restaurant_exists'];
            if($value['status']== 0){
                $status = 'archived';
            }elseif($value['status']== 1){
                $status = 'upcoming';
            }elseif($value['status']== 2){
                $status = 'canceled';
            }elseif($value['status']== 3){
                $status = 'rejected';
            }elseif($value['status']== 4){
                $status = 'confirmed';
            }
            $response[$key]['reservation_status'] = $status;
            unset($response[$key]['status']);
        }

        return $response;
    }

}
