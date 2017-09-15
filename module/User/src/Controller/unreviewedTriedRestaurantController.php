<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserReview;
use User\Model\UserOrder;
use User\Model\UserReservation;
use MCommons\CommonFunctions;
use User\Model\User;
use Restaurant\Model\Restaurant;
use Zend\Db\Sql\Predicate\Expression;
use MCommons\StaticOptions;
use User\UserFunctions;

class unreviewedTriedRestaurantController extends AbstractRestfulController {

       public function getList() {
        $userId = $this->getUserSession()->user_id;               
        if(!$userId){
            throw new \Exception('Not a valid user');
        }
        $userOrder = new UserOrder();
        $userReservation = new UserReservation();
        $userFunctions = new UserFunctions();
        $session = $this->getUserSession();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $joins_order = array();
        //User Order Detail
        $joins_order [] = array(
            'name' => array(
                'r' => 'restaurants'
            ),
            'columns'=>array('restaurant_name','rest_code','restaurant_primary_image'=>'restaurant_image_name'),
            'on' => 'r.id = user_orders.restaurant_id',
            'type' => 'inner'
        );
        $userOrder->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $optionsOrder = array(
            'columns' => array(
                'restaurant_id',
                'order_type', 
                'order_id'=>'id',
                'created_date'=>'created_at'
            ),           
            'where' => new Expression('(order_type != "Dinein") AND (status = "cancelled" OR status = "rejected" OR status="archived" OR delivery_time <"'.$currentDate.'") AND is_reviewed=0 AND user_id='.$userId.' AND r.closed=0 AND r.inactive=0'),
            'joins'=>$joins_order
            );
      
        $responseOrder = $userOrder->find($optionsOrder)->toArray();
        //pr($responseOrder,true);       
        //User Reservation Detail
        $joins_reservation = array();        
        $joins_reservation [] = array(
            'name' => array(
                'r' => 'restaurants'
            ),
            'columns'=>array('restaurant_name','rest_code','restaurant_primary_image'=>'restaurant_image_name'),
            'on' => 'r.id = user_reservations.restaurant_id',
            'type' => 'inner'
        );
        $userReservation->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $optionsReservation = array(
            'columns' => array(
                'restaurant_id', 
                'reservation_id'=>'id',                
                'created_date'=>'reserved_on',
            ),           
            'where' =>new Expression('(status = 0 OR status = 1 OR status = 4 ) AND is_reviewed=0 AND user_id='.$userId.' AND r.closed=0 AND r.inactive=0 AND time_slot <"'.$currentDate.'"'),
            'joins'=>$joins_reservation
        );
        $responseReservation = $userReservation->find($optionsReservation)->toArray();       
        
        $responses = array_merge($responseOrder,$responseReservation);
        $totalRecord = count($responses);
        if($totalRecord > 0){
            foreach ($responses as $key => $val) { 
                if(isset($val['reservation_id'])){
                    $responses[$key]['order_type']='Dinein';
                }
               $responses[$key]['rest_code']=  strtolower($val['rest_code']);
               $sortDate[$key] = strtotime($val['created_date']);
            }

            $limit = $this->getQueryParams('limit',SHOW_PER_PAGE);
            $page = $this->getQueryParams('page',1);
            $offset = 0;
            if ($page > 0) {
                $page = ($page < 1) ? 1 : $page;
                $offset = ($page - 1) * ($limit);
            }

                array_multisort($sortDate, SORT_DESC, $responses);

            $records['unreviewed'] = array_slice($responses, $offset, $limit);
            $records['total_records'] = $totalRecord;
            return $records;
        }else{
           $records['unreviewed'] = array();
           $records['total_records'] = 0;
           return $records;
        }
    }
}
