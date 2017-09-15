<?php
namespace Ariahk\Model;

use MCommons\Model\AbstractModel;
use Authenticationchanel\Model\Authenticationchanel;
use MCommons\StaticOptions;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;

class UserReservationCardDetails extends AbstractModel
{

    public $reservation_id;
    
    public $card_number;

    public $encrypt_card_number;

    public $name_on_card;
    
    public $card_type;

    public $expired_on;

    public $billing_zip;

    public $created_on;

    public $stripe_card_id;

    public $stripe_cus_id;

    protected $_db_table_name = 'Ariahk\Model\DbTable\UserReservationCardDetailsTable';

    protected $_primary_key = 'id';
    
    
    public function getUserReservation(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		return $this->find ( $options )->toArray();
		
	}
	public function getAllReservation(array $options = array()) {
		$this->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
		$reservations = $this->find ( $options )->toArray ();
		return $reservations;
	}
    public function reserveTableCardDetails() {
		$data = $this->toArray ();
                $writeGateway = $this->getDbTable ()->getWriteGateway ();
		if ($this->reservation_id) {
			$rowsAffected = $writeGateway->insert ( $data );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'reservation_id' => $this->reservation_id 
			));
		}
                
		// Get the last insert id and update the model accordingly
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		
		if ($rowsAffected >= 1) {
			$this->id = $lastInsertId;
			return $this->toArray ();
		}
		return false;
	}    
 }    