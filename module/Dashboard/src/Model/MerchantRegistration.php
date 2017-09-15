<?php
namespace Dashboard\Model;

use MCommons\Model\AbstractModel;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;

class MerchantRegistration extends AbstractModel
{

  public $id;
  public $restaurant_id;
  public $fee_structure_id=2;
  public $username;
  public $restaurant_name;
  public $street_address;
  public $apt_suite;
  public $city;
  public $state;
  public $zipcode;
  public $web_url;
  public $phone;
  public $email;
  public $payment_mode;
  public $name_oncard;
  public $cardno;
  public $amount=0;
  public $exp_month;
  public $exp_year;
  public $billingzip;
  public $stripes_token;
  public $stripe_card_id;
  public $cardtype;
  public $created_on;
  public $updated_on;
  public $status;
  public $street_address2;
  public $phone1;
  public $phone2;
  public $fax;
  public $owner_name;
  public $contact1;
  public $contact2;
  public $contact3;
  public $account_manager;
  public $rest_instructions;
  public $onlinegrowth;
  public $customadvsols;
  public $freelistingonma;
  public $chkdelivery;
  public $chktakeout;
  public $chkreservations;
  public $chkprepaidres;
  public $pmodeemail;
  public $pmodephone;
  public $pmodefax;
  public $accept_cc_card_phone;
  public $menu;
  public $menu2;
  public $hoo_week;
  public $hoo_sat;
  public $hoo_sun;
  public $dh_week;
  public $dh_sat;
  public $dh_sun;
  public $delivery_area;
  public $min_delivery_amt;
  public $delivery_fee;
  public $delivery_fee_type;
  public $delivery_fee_mode;
  public $delivey_instrucation;
  public $payment_method;
  public $payee_name_check;
  public $bank_account_no;
  public $rounting_no_ach;
  public $package;
  public $plan_period;
  public $waiving_period;
  public $cron_update;
  public $campaign_start_date;
  public $expires_on;
  public $associatename;
  public $associateemail;
  public $delivery_by_ma;
  public $fees_waived_off;
  public $dineloyalty;
  public $loyaltyduration;
  public $loyaltypay;
  public $cell_phone;
  public $title_name;
  public $sales_region;
  public $owneremail;
  public $ownercell;
  public $filled_by;
  public $agreement_copy;
  public $discount=0;
  
  
  
  protected $_db_table_name = 'Dashboard\Model\DbTable\MerchantTable';

      
    public function authRestaurantAccount($data){
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array('id','user_name','email','restaurant_id','city_id'));        
        $where = new Where();         
        $where->NEST->equalTo('status', 1)->AND->equalTo('user_password', $data['dashboard_password'])->UNNEST->AND->NEST->equalTo('email',$data['dashboard_username'])->OR->equalTo('user_name', $data['dashboard_username'])->UNNEST;       
        $select->where($where);    
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $restAccData = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->toArray();
        return $restAccData;
    }
   
    public function create(){
        $data = $this->toArray ();       
		$writeGateway = $this->getDbTable ()->getWriteGateway ();  
//        try{
//                $rowsAffected = $writeGateway->insert ($data);   
//                } catch (\Exception $e){
//                    \Zend\Debug\Debug::dump($e->__toString()); exit; 
//                }
		if (! $this->id) {
			$rowsAffected = $writeGateway->insert ( $data );
		} else {
			$rowsAffected = $writeGateway->update ( $data, array (
					'id' => $this->id 
			) );
		}
		
		$lastInsertId = $writeGateway->getAdapter ()->getDriver ()->getLastGeneratedValue ();
		
		if ($rowsAffected >= 1) {
			$this->id = $lastInsertId;
			return $this->toArray ();
		}
		return false;
    }
    
    public function getRestaurantAgreements($offset=0,$limit=0){
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array('id','restaurant_id','restaurant_name','onlinegrowth','agreement_copy','created_on'));        
        $where = new Where();         
        $where->NEST->notEqualTo('agreement_copy', '')->UNNEST;
        $select->where($where);    
        $select->order('created_on desc');
        $select->offset($offset);
        $select->limit($limit);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $restAccData = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->toArray();
        $pdfLocation=WEB_URL.''.CAREER.'agreements/';
        $growthArray=[ 'munchadoprogram'=>'The Munch Ado Restaurant Program','marketingprogram'=>'The Munch Ado Marketing Program','ecommprogram'=>' The Munch Ado eCommerce Program','socialprogram'=>'The Munch Ado Social Media Program', 'ecommprogrammarketingprogram'=>'The Munch Ado eCommerce And Marketing Program', 'ecommprogramsocialprogram'=>'The Munch Ado eCommerce And Social Media Program','freelistingonMA'=>'Free Profile on Munch Ado','marketingprogramsocialprogram'=>'The Munch Ado Marketing And Social Media Program', 'dineloyalty'=>'Dine & More Loyalty Program', 'customadvsols'=>'Advertising Solutions', 'onlinegrowth'=>'Online Growth (Orders & Reservations)'];
        if(count($restAccData) > 0 && !empty($restAccData)){
            foreach($restAccData as $key=>$val){
                $restAccData[$key]['link']=$pdfLocation.$val['agreement_copy'];
                $restAccData[$key]['created_on']=date('d M Y', strtotime($val['created_on']));
                $restAccData[$key]['onlinegrowth']=isset($growthArray[$val['onlinegrowth']])?$growthArray[$val['onlinegrowth']]:'--';
            }
        }
        return $restAccData;
    }
    
    public function countRestaurantAgreements(){
        $select = new Select();
        $select->from($this->getDbTable()
            ->getTableName());
        $select->columns(array('id'));        
        $where = new Where();         
        $where->NEST->notEqualTo('agreement_copy', '')->UNNEST;
        $select->where($where);
        //var_dump($select->getSqlString($this->getPlatform('READ'))); die();
        $restAccData = $this->getDbTable()
            ->setArrayObjectPrototype('ArrayObject')
            ->getReadGateway()
            ->selectWith($select)
            ->toArray();
        return $restAccData;
    }
}