<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use User\Model\UserAddress;
use User\Model\User;
use Home\Model\City;
use User\UserFunctions;
use Search\CityDeliveryCheck;

class WebUserAddressController extends AbstractRestfulController {

    public function create($data) {
        if (empty($data)) {
            throw new \Exception("Invalid Parameters", 400);
        } else {
            $addressModel = new UserAddress ();
            $userFunctions = new UserFunctions();
            $cityModel = new City();
            $user = new User();
            $session = $this->getUserSession();
            $isLoggedIn = $session->isLoggedIn();
            $address = array();
            if ($isLoggedIn) {
                $locationData = $session->getUserDetail ( 'selected_location', array () );
                $currentDate = $userFunctions->userCityTimeZone($locationData);
                $data ['user_id'] = $session->getUserId();
                $userDetail = $session->getUserDetail();
                if (isset($data['email'])) {
                    
                } elseif (isset($userDetail['email'])) {
                    $data['email'] = $userDetail['email'];
                }

                if (!isset($data['email'])) {
                    $options = array('column' => array('email'), 'where' => array('id' => $data ['user_id']));
                    $userEmail = $user->getUserDetail($options);
                    $data['email'] = $userEmail['email'];
                }
            } else if ($data['email']) {
                throw new \Exception("Invalid user", 400);
            }

            
            if (!isset($data ['address_name']) && empty($data ['address_name'])) {
                throw new \Exception("Address can't be empty", 400);
            }
            if (!isset($data ['street']) && empty($data ['street'])) {
                throw new \Exception("Street can't be empty", 400);
            }

            if (!isset($data ['city']) && empty($data ['city'])) {
                throw new \Exception("City can't be empty", 400);
            }

            if (!isset($data ['state']) && empty($data ['state'])) {
                throw new \Exception("State can't be empty", 400);
            }

            if (!isset($data ['phone']) && empty($data ['phone'])) {
                throw new \Exception("Phone number can't be empty", 400);
            }
            if (!isset($data ['zipcode']) && empty($data ['zipcode'])) {
                throw new \Exception("Zip code can't be empty", 400);
            }
             if(!isset($data['address_lat']) && empty($data['address_lat'])){
                throw new \Exception("Address latitude is not valid", 400);
            }
            if(!isset($data['address_lng']) && empty($data['address_lng'])){
                throw new \Exception("Address longitude is not valid", 400);
            }
                      
            $data ['apt_suite'] = (isset($data ['apt_suite'])) ? $data ['apt_suite'] : '';          
            $options = array(
                'columns' => array('id'),
                'where' => array(
                    'user_id' => $data['user_id'],
                    'latitude'=> $data['address_lat'],
                    'longitude'=>$data['address_lng'],                    
                )
            );
            $userAddressDetail = $addressModel->getUserAddressInfo($options);

            try {
                $address['latitude']=$data['address_lat'];
                $address['longitude']=$data['address_lng'];
                $address['google_addrres_type']="street";
                $address['address_name']=$data ['address_name'];
                $address['email'] = $data['email'];
                $address['street']=$data ['street'];
                $address['city']=$data ['city'];
                $address['zipcode']=$data ['zipcode'];
                $address['state']=$data ['state'];
                $address['phone']=$data['phone'];
                $address['apt_suite']= (isset($data ['apt_suite'])) ? $data ['apt_suite'] : '';
                $address['status'] = 1;
                $address ['created_on'] = $currentDate;
                $address['updated_at']= $currentDate;
                $address['address_type']= 's';
                $address['user_id']=$session->getUserId();
                if (isset($userAddressDetail['id']) && $userAddressDetail['id'] >0) {                    
                    $addressModel->id = $userAddressDetail['id'];
                    $addressModel->update($address);
                    $address['id'] = $userAddressDetail['id'];
                    $response = $address;                    
                }else{
                    $response = $userFunctions->addUserAddress($address);
                }
                
               
            } catch (\Exception $ex) {
                return $this->sendError(array(
                            'error' => $ex->getMessage()
                                ), $ex->getCode());
            }
            if (!$response) {
                throw new \Exception('Unable to save user address', 400);
            }
            return $response;
        }
    }

    public function update($id, $data) {
   
        if (empty($data)) {
            throw new \Exception("Invalid Parameters", 400);
        } else {            
            $address = array();
            $userFunctions = new UserFunctions();
            $addressModel = new UserAddress ();           
            $session = $this->getUserSession();
            $isLoggedIn = $session->isLoggedIn();
            if (!$isLoggedIn) {
                throw new \Exception("Invalid user", 400);
            }
            $userId = $session->getUserId();
            $locationData = $session->getUserDetail ( 'selected_location', array () );
            $currentDate = $userFunctions->userCityTimeZone($locationData);
           
            if (!isset($data ['address_name']) && empty($data ['address_name'])) {
                throw new \Exception("Address can't be empty", 400);
            }

            if (!isset($data ['street']) && empty($data ['street'])) {
                throw new \Exception("Street can't be empty", 400);
            }

            if (!isset($data ['city']) && empty($data ['city'])) {
                throw new \Exception("City can't be empty", 400);
            }

            if (!isset($data ['state']) && empty($data ['state'])) {
                throw new \Exception("State name can't be empty", 400);
            }

            if (!isset($data ['phone']) && empty($data ['phone'])) {
                throw new \Exception("Phone number can't be empty", 400);
            }

            if (!isset($data ['zipcode']) && empty($data ['zipcode'])) {
                throw new \Exception("Zip code can't be empty", 400);
            }
            
            if(!isset($data['address_lat']) && empty($data['address_lat'])){
                throw new \Exception("Address latitude is not valid", 400);
            }
            if(!isset($data['address_lng']) && empty($data['address_lng'])){
                throw new \Exception("Address longitude is not valid", 400);
            }
           
            $address['latitude']=$data['address_lat'];
            $address['longitude']=$data['address_lng'];
            $address['google_addrres_type']="street";
            $address['address_name']=$data ['address_name'];
            $address['street']=$data ['street'];
            $address['city']=$data ['city'];
            $address['zipcode']=$data ['zipcode'];
            $address['state']=$data ['state'];
            $address['phone']=$data['phone'];
            $address['apt_suite']= (isset($data ['apt_suite'])) ? $data ['apt_suite'] : '';
            $address['status'] = 1;
            $address['updated_at']= $currentDate;
            $address['address_type']= 's';
//            $options = array(
//                'columns' => array('street', 'apt_suite'),
//                'where' => array(
//                    'address_name' => $data ['address_name'],
//                    'user_id' => $userId,
//                    'street' => $data['street'],
//                    'apt_suite'=>$address['apt_suite'],
//                    'city' => $data['city'],
//                    'state' => $data['state'],
//                    'phone' => $data['phone'],
//                    'zipcode' => $data['zipcode'],                    
//                )
//            );
//            $userAddressDetail = $addressModel->getUserAddressInfo($options);
//
//            if (!empty($userAddressDetail)) {
//                throw new \Exception("Address is already exist.");
//            }
          
            try {                      
                 $addressModel->id = $id;
                 $rowAffected = $addressModel->update($address);
                if ($rowAffected == 0) {
                    throw new \Exception('Address has not been updated', 400);
                }
            } catch (\Exception $ex) {
                return $this->sendError(array(
                            'error' => $ex->getMessage()
                                ), $ex->getCode());
            }
            
            return $data;
        }
    }

    public function getList() {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        } else {
            throw new \Exception('User unavailable', 400);
        }
        
        $selectedLocation = $session->getUserDetail('selected_location', array());
        $cityId = $selectedLocation ['city_id'];
        $cityModel = new \Home\Model\City();
        $cityDetails = $cityModel->cityDetails($cityId);
        $city_name = $cityDetails[0]['city_name'];
        
        $user_address_model = new UserAddress ();
        $user_address_model->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'id',
                'address_name',
                'phone',
                'street',
                'city',
                'zipcode',
                'state',
                'apt_suite',
                'latitude',
                'longitude',
                'google_addrres_type'
            ),
            'where' => array(
                'user_id' => $user_id,
                'user_addresses.status' => '1',
                'city'=> $city_name,                
            ),
            'order'=>array('id'=>'desc'),
            'group'=>array('latitude','longitude'),
           );

        $response = $user_address_model->find($options)->toArray();
        $cities = new City();
        $res_id = $this->getQueryParams('res_id');
        if ($response) {
            foreach ($response as $key => $val) {
                if($val['latitude']!=0 && $val['longitude']!=0){
                    $opt = array('columns' => array('id'), 'where' => array('city_name' => $val['city'], 'state_code' => $val['state']));
                    $res = $cities->find($opt)->toArray();
                    $response[$key]['city_id'] = $res?$res[0]['id']:''; 
                    $response[$key]['city_name'] = $val['city'];
                    $response[$key]['can_deliver'] = CityDeliveryCheck::canDeliver($res_id, $response[$key]['latitude'], $response[$key]['longitude']);
                    $response[$key]['distance'] = '0';
                    if($response[$key]['can_deliver']){
                        $response[$key]['distance'] = StaticOptions::getResDistanceInMiles($res_id, $response[$key]['latitude'], $response[$key]['longitude']);
                    }
                }else{
                    unset($response[$key]);
                }
            }
        }
        return $response;
    }

    public function delete($id) {
        $userAddressModel = new UserAddress ();
        $data = array(
            'status' => '0'
        );
        $userAddressModel->id = $id;
        $rowAffected = $userAddressModel->update($data);
        if ($rowAffected > 0) {
            return $data;
        }
        throw new \Exception('Invalid Id Provided');
    }

}
