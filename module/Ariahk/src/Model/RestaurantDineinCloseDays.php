<?php

namespace Ariahk\Model;

use MCommons\Model\AbstractModel;
use Authenticationchanel\Model\Authenticationchanel;
use MCommons\StaticOptions;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class RestaurantDineinCloseDays extends AbstractModel {

    public $restaurant_id;
    public $close_date;
    public $close_from;
    public $close_to;
    public $whole_day;
    protected $_db_table_name = 'Ariahk\Model\DbTable\RestaurantDineinCloseDaysTable';
    protected $_primary_key = 'id';

    public function getRestaurantDinein(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        return $this->find($options)->toArray();
    }

    public function getAllRestaurantDinein(array $options = array()) {
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $reservations = $this->find($options)->toArray();
        return $reservations;
    }
    
    public function getOpenCloseReservationslot($restaurant_id, $date) {
        if ($date == "") {
            $date = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                    ))->format('Y-m-d');
        } else {
            $tmpDate = new \DateTime($date);
            $date = StaticOptions::getAbsoluteCityDateTime(array(
                        'restaurant_id' => $restaurant_id
                            ), $tmpDate->format('Y-m-d'), 'Y-m-d')->format('Y-m-d');
        }
        $options = array(
            'columns' => array(
                'close_date',
                'close_from',
                'close_to',
                'whole_day'
            ),
            'where' => array(
                'restaurant_id' => $restaurant_id,
                'close_date' => $date
            ),
             'group'=>'close_from'
        );
        $this->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $this->find($options)->toArray();
        return $response;
    }

}
