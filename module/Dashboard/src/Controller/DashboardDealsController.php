<?php

namespace Dashboard\Controller;

use MCommons\Controller\AbstractRestfulController;
use Dashboard\DashboardFunctions;
use Dashboard\Model\DealsCoupons;

class DashboardDealsController extends AbstractRestfulController {

    public $msg;
    public $status;
    public $restaurantId;

    public function create($data) {

        if (!empty($data)) {
            $dealsCoupons = new DealsCoupons();
            $dashboardFunctions = new \Dashboard\DashboardFunctions();
            $restaurant = new \Dashboard\Model\Restaurant();
            $restaurant_id = $dashboardFunctions->getRestaurantId();
            $currentDateTime = $dashboardFunctions->CityTimeZone();
            $cityId = $dashboardFunctions->getLocation()['city_id'];
            $deal_data['restaurant_id'] = $restaurant_id;
            $restaurant_details = $restaurant->findRestaurant(array('columns' => array('minimum_delivery'), 'where' => array('id' => $restaurant_id)));
            $deal_data['city_id'] = $cityId;
            
            if(isset($data['type'])){
                $data['type'] = "offer";
            }else{
                $data['type'] = "deals";
            }

            if($data['type'] =="deals"){
            if (!$data['min_order'] && $data['min_order'] < $restaurant_details->minimum_delivery && $data['type'] == "deals") {
                return array("error" => 1, "msg" => "Minimum order cannot be less than " . $restaurant_details->minimum_delivery);
            }
            }
            

            $deal_data['deal_for'] = $data['deal_for'];
            $deal_data['title'] = $data['title'];
            if ($data['description'] != '') {
                $deal_data['description'] = $data['description'];
            }
            if(isset($data['slots']) && !empty($data['slots'])){
                $deal_data['slots'] = $data['slots'];
            }
            if(isset($data['days']) && !empty($data['days'])){
                $deal_data['days'] = $data['days'];
            }
            $deal_data['price'] = 0;
            $deal_data['max_daily_quantity'] = isset($data['max_daily_quantity'])?$data['max_daily_quantity']:"";
            $deal_data['discount_type'] = isset($data['discount_type'])?$data['discount_type']:"flat";
            $deal_data['discount'] = isset($data['discount'])?$data['discount']:0;
            $deal_data['minimum_order_amount'] = (isset($data['type']) && $data['type']=="deals")?$data['min_order']:0;
            $deal_data['start_on'] = isset($data['start_on'])?$data['start_on']:$currentDateTime;
            $deal_data['end_date'] = isset($data['end_date'])? $data['end_date']. " " . "23:59:59":$currentDateTime;
            $deal_data['expired_on'] = $deal_data['end_date'];
            $deal_data['created_on'] = $currentDateTime;
            $deal_data['updated_at'] = $currentDateTime;
            $deal_data['status'] = 3;
            $deal_data['type']=(isset($data['type']) && !empty($data['type']))?$data['type']:"deals";
            $files = isset($data ['image'])?$data ['image']:false;
            $imageName = "";
                        
            if (isset($files) && $files && !empty($files)) {
               
                $offerImage = $dashboardFunctions->uploadBase64Image($files, APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . 'offer' . DS);
                if (empty($offerImage)) {
                    throw new \Exception('Offer image is not valid');
                }
                $imageName = array_pop(explode('/', $offerImage));
            }
            $deal_data['image'] = $imageName;
            if (isset($data['dealUsers']) && count($data['dealUsers']) > 0) {
                $this->createUserSpecificDeals($deal_data, $data);
                $dealsCoupons->addDealsCoupons($deal_data);
                $dealId = $dealsCoupons->id;
                //die('test');
                $this->createUserDeals($data['dealUsers'], $dealId, $currentDateTime);
                return array("error" => 0, "msg" => "Success");
            }
            
            $response = $dealsCoupons->addDealsCoupons($deal_data);
            if ($response) {
                return array("total_count" => 0, "dealscoupon" => $response);
            } else {
                return array("error");
            }
        } else {
            return array("error");
        }
    }

    public function getList() {
        $type = $this->getQueryParams('type',false); 
        $dealtype = $this->getQueryParams('dealtype',false);
        $dealsCoupons = new DealsCoupons();
        $dashboardFunctions = new DashboardFunctions();
        $orderBy = $this->getQueryParams('orderby', false);
        $this->restaurantId = $dashboardFunctions->getRestaurantId();
        
        if($type && $type==='user'){
            return $this->userDetails();
        }elseif($type && $type==="menu"){
            return $this->menuDetails();
        }elseif($type && $type=="offer"){
            return $this->offerDetails();
        }
        $currentDate = $dashboardFunctions->CityTimeZone();
        $limit = 10;
        
        $restaurantDealsDetils = $dealsCoupons->findDetailedDeals($dealtype,$this->restaurantId, $currentDate, $orderBy, $limit);
        return $restaurantDealsDetils;
    }

    public function get($id) {
        $dealsCoupons = new DealsCoupons();
        $dashboardFunction = new DashboardFunctions();
        $this->restaurantId = $dashboardFunction->getRestaurantId();
        $options = array('where' =>array('id'=>$id));
        $data['details']=$dealsCoupons->findDeals($options);
        //pr($data['details'],1);
        
        if ($data['details'][0] ['discount_type'] == 'p') {
            $data['details'][0] ['you_save'] = "$".$data['details'][0] ['price'] * $data['details'][0] ['discount'] / 100;
            $data['details'][0] ['actual_price'] = (string)$data['details'][0] ['price'] - $data['details'][0] ['you_save'];
            $data['details'][0] ['discount'] = $data['details'][0] ['discount'] . '%';
        } else {
            $data['details'][0]['you_save'] = "$".$data['details'][0] ['discount'];
            $data['details'][0]["actual_price"] = (string)$data['details'][0] ['price'] - $data['details'][0] ['discount'];
        }
        $data['details'][0]["deals_start_date"] = date("Y-m-d",  strtotime($data['details'][0]["start_on"]));
        $data['details'][0]["deals_end_date"] = date("Y-m-d",strtotime($data['details'][0]["end_date"]));
        $data['details'][0]["location"]="New York";
        $data['details'][0]["time_ago"]="";
        $data['details'][0]["unredeemed"] = $data['details'][0]['redeemed'];
        $data['details'][0]["revenue"] = $data['details'][0]['price'];
        $data['details'][0]["startDate"] =  date("Y-m-d",strtotime($data['details'][0]["start_on"]));
        $data['details'][0]["endDate"] =date("Y-m-d",strtotime($data['details'][0]["end_date"]));
        $data['details'][0]["DealstartDate"] =date("Y-m-d",strtotime($data['details'][0]["start_on"]));
        $data['details'][0]["DealendDate"]=date("Y-m-d",strtotime($data['details'][0]["end_date"]));
        $data['details'][0]['image'] = (isset($data['details'][0]['image']) && !empty($data['details'][0]['image']))?WEB_URL.USER_IMAGE_UPLOAD."offer/".$data['details'][0]['image']:"";
        $resUserDetails =$this->userDetails();  
        $userDeals = new \Dashboard\Model\UserDeals();
        $dealUser = $userDeals->getDealUser($id);
        
        $dUser = "";
        $dealAssignUserIds = [];
        if($dealUser){            
           foreach($dealUser as $key =>$val){
               $dUser .=$val['first_name'].", ";
               $dealAssignUserIds[] = $val['id'];
           } 
        }
          //pr($dealUser);
        $userhavenotDeal = $this->userHaveNotDeal($resUserDetails,$dealAssignUserIds);
       // pr($userhavenotDeal,1);
        $menu  = new \Dashboard\Model\Menu();        
        $menuDetails = $menu->restaurantMenues($this->restaurantId);
        $dealMenu = $menu->getDealMenu($id);
        
        $dmenu = "";
        $dm = "";
        if($dealMenu){
            foreach($dealMenu as $key=>$val){
                $dm .=$val['item_name'].", "; 
            }
            $dmenu = substr($dm,0,-2);
        }
        $data['details'][0]["chart"] = array();
        $data['details'][0]["menu"] = $menuDetails;
        $data['details'][0]["resUsers"] = (isset($userhavenotDeal) && !empty($userhavenotDeal))?$userhavenotDeal:[];
        $data['details'][0]["dealUsersArr"] = $dealUser;
        $data['details'][0]["users"] = (isset($resUserDetails['users']) && !empty($resUserDetails['users']))?$resUserDetails['users']:[];
        $data['details'][0]["dealUsers"]= substr($dUser,0,-2);
        $data['details'][0]["dealMenus"]= $dmenu;
        return $data;
        
    }

    public function update($id, $data) {
        $dashboardFunctions = new DashboardFunctions();
        //$restaurant_id = $dashboardFunctions->getRestaurantId();
        $currentDateTime = $dashboardFunctions->CityTimeZone();
        $dealsCoupons = new DealsCoupons();
        $dealsData = [];
        $files = (isset($data ['image']) && !empty($data['image']))?$data['image']:false;
        $imageName = "";
                        
        if (isset($files) && $files && !empty($files)) {

            $offerImage = $dashboardFunctions->uploadBase64Image($files, APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . 'offer' . DS);
            if (empty($offerImage)) {
                throw new \Exception('Offer image is not valid');
            }
            $imageName = array_pop(explode('/', $offerImage));
            $data['image'] = $imageName;
        }
        
        $this->prepairDealsData($dealsData, $data, $currentDateTime);
        
        $response = $dealsCoupons->updateDealsCoupons($dealsData, $id);
       
        if (isset($data['dealUsers']) && !empty($data['dealUsers'])) { 
            $this->addUserDeals($id, $data['dealUsers'], $currentDateTime);
        }
        if($response){
            return array("success");
        }else{
            return array("fail");
        }
    }

    public function delete($id) {
        $dealsCoupons = new DealsCoupons();
        $dealsCoupons->id = $id;
        $dealsCoupons->delete();
    }

    public function createUserSpecificDeals(&$deal_data, $data) {
        $deal_data['user_deals'] = 1;
        if (isset($data['menu_id']) && !empty($data['menu_id'])) {
            $deal_data['menu_id'] = $data['menu_id'];
        }
    }

    public function createUserDeals($userId, $dealId, $currentDate) {
       
        $userDeals = new \Dashboard\Model\UserDeals();
        
        $userDealsData['deal_id'] = $dealId;
        $userDealsData['deal_status'] = 1;
        $userDealsData['availed'] = 0;
        $userDealsData['date'] = $currentDate;
        $userDealsData['read'] = 0;
        
        foreach ($userId as $key => $value) {
            $userDealsData['user_id'] = $value;
            $userDeals->createUserDeal($userDealsData);
        }
        
        return true;
    }

    public function prepairDealsData(&$dealsData, $data, $currentDate) {
        if (isset($data['type']) && !empty($data['type'])) {
            $dealsData['type'] = $data['type'];
        }     
        if (isset($data['title']) && !empty($data['title'])) {
            $dealsData['title'] = $data['title'];
        }
        if (isset($data['end_date']) && !empty($data['end_date'])) {
            $dealsData['end_date'] = $data['end_date'] . " " . '23:59:59';
        }
        if (isset($data['discount']) && !empty($data['discount'])) {
            $dealsData['discount'] = $data['discount'];
        }
        if (isset($data['min_order']) && !empty($data['min_order']) && $data['type']=="deals") {
            $dealsData['minimum_order_amount'] = $data['min_order'];
        }

        if (isset($data['menu_id']) && !empty($data['menu_id'])) {
            $dealsData['menu_id'] = $data['menu_id'];
        }
        if (isset($data['description']) && !empty($data['description'])) {
            $dealsData['description'] = $data['description'];
        }

        if (isset($data['days']) && !empty($data['days'])) {
            $dealsData['days'] = $data['days'];
        }
        if (isset($data['slots']) && !empty($data['slots'])) {
            $dealsData['slots'] = $data['slots'];
        }
        if (isset($data['status']) && !empty($data['status'])) {
            $dealsData['status'] = $data['status'];
        }
        
        
        if(isset($data['image']) && !empty($data['image'])){
            $dealsData['image'] = $data['image'];
        }
        if(isset($data['deal_used_type']) && !empty($data['deal_used_type'])){
            $dealsData['deal_used_type'] = $data['deal_used_type'];
        }
        
        if(isset($data['trend']) && !empty($data['trend'])){
            $dealsData['trend'] = $data['trend'];
        }

        if (isset($dealsData['status']) && isset($dealsData['type'])) {
            $this->messageStatus($dealsData['status'], $dealsData['type'], $currentDate);
        }
       
        //$val = \RestaurantDealCoupon::get_full_deal_details($data['id']);
        //$this->respond($val);
    }

    public function addUserDeals($dealId, $users, $currentDate) {
        if (count($users > 0) && !empty($users)) {
            
            $userDeals = new \Dashboard\Model\UserDeals();
            $userDeals->delete($dealId);
                $this->createUserDeals($users, $dealId, $currentDate);
        }
    }

    public function messageStatus($status, $type, $currentDate) {
        if ($status == 2 && $type == "deals") {
            $this->msg = "This Deal Was Paused at " . date('H:i A', strtotime($currentDate)) . " on " . date('M d, Y', strtotime($currentDate)) . ".";
            $this->status = "Paused";
        } elseif ($status == 1 && $type = "deals") {
            $this->msg = "This Deal Has Been Resumed And is Available to Customers Again!";
            $this->status = "Live";
        } elseif ($status == 2 && $type == "coupons") {
            $this->msg = "This Coupons Was Paused at " . date('H:i A', strtotime($currentDate)) . " on " . date('M d, Y', strtotime($currentDate)) . ".";
            $this->status = "Paused";
        } elseif ($status == 1 && $type == "coupons") {
            $this->msg = "This Coupons Has Been Resumed And is Available to Customers Again!";
            $this->status = "Live";
        }
    }
    
    public function userDetails(){
        $user = new \Dashboard\Model\User();
        $users['users']=$user->dineAndMoreUserDetails($this->restaurantId);
        return $users;
        
    }
    
    public function menuDetails(){
        $menuDetails = [];
        $user = new \Dashboard\Model\User();        
        $menuDetails['users'] = $user->dineAndMoreUserDetails($this->restaurantId);
        
        $menu  = new \Dashboard\Model\Menu();        
        $menuDetails['menu'] = $menu->restaurantMenues($this->restaurantId);
        //pr($menuDetails,1);
        return $menuDetails;
    }
    
    public function offerDetails(){
       $dealsCoupons = new DealsCoupons();
       return $dealsCoupons->reservationOffers($this->restaurantId);
    }
    
    public function userHaveNotDeal($resUserDetails,$dealAssignUserIds){
        if(!empty($resUserDetails) && !empty($dealAssignUserIds)){
            foreach($resUserDetails as $key => $value){
                foreach($value as $k => $v){
                if(in_array($v['id'], $dealAssignUserIds)){
                    unset($resUserDetails[$key][$k]);
                }
                }
            }
        }
        $response = [];
        if(!empty($resUserDetails)){
            foreach($resUserDetails as $ks =>$dl){
                foreach($dl as $kk => $val){
                    $response[] = $val;
                }
               
            }
        }
        
        return $response;
    }

}
