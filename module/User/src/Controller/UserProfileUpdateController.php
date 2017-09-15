<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;
use User\Model\UserEatingHabits;
use MCommons\StaticOptions;
use Home\Model\City;

class UserProfileUpdateController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function getList() {
        $user = new User ();
        $session = $this->getUserSession();
        $user_id = $session->getUserId();       
        $response = current($user->getUserEatingHabitsDetail($user_id));
        $response['phone_number'] = $response['phone'];
        return $response;
    }

    public function update($id, $data) {
        $session = $this->getUserSession();
        $user_id = $session->getUserId();
        if (!$user_id) {
            throw new \Exception('User is not valid');
        }
        if (!isset($data['first_name']) && empty($data['first_name'])) {
            throw new \Exception('First name can not be empty');
        }
        if (!isset($data['phone']) && empty($data['phone'])) {
            throw new \Exception('We promise; No prank calls');
        }
        $insert = array();
        $insert['first_name'] = $data['first_name'];
        $insert['last_name'] = isset($data['last_name']) ? $data['last_name'] : '';
        $insert['phone'] = $data['phone'];
        $insert['city_id'] = $data['city_id'];
        $userModel = new User();
        $userModel->id = $user_id;
        $userModel->update($insert);
        
        $userEatingHabits = new UserEatingHabits();
        $eatingHabits = array();
        $eatingHabits['favorite_beverage'] = $data['favorite_beverage'];
        $eatingHabits['where_do_you_go'] = $data['where_do_you_go'];
        $eatingHabits['comfort_food'] = $data['comfort_food'];
        $eatingHabits['favorite_food'] = $data['favorite_food'];
        $eatingHabits['dinner_with'] = $data['dinner_with'];
        
        $selectedLocation = $session->getUserDetail('selected_location', array());
        $cityId = isset($selectedLocation ['city_id']) ? $selectedLocation ['city_id'] : false;
        $cityModel = new City ();
        $cityDetails = $cityModel->cityDetails($data['city_id']);        
        $currentDate = StaticOptions::getRelativeCityDateTime(array(
                            'state_code' =>$cityDetails [0] ['state_code']
                ))->format(StaticOptions::MYSQL_DATE_FORMAT);       
        
        if (isset($data['eating_habits_id']) && !empty($data['eating_habits_id'])) {
            $eatingHabits['updated_on'] = $currentDate;
            $userEatingHabits->id = $data['eating_habits_id'];
            $userEatingHabits->update($eatingHabits);
            $eatingHabits['eating_habits_id']=$data['eating_habits_id'];
        } else {
            $eatingHabits['created_on'] = $currentDate;
            $eatingHabits['user_id'] = $user_id;
            $isExist = $userEatingHabits->findUserEatingHabits($user_id);
           
            if(empty($isExist)){
                $last_inserted_id = $userEatingHabits->insert($eatingHabits);
                $eatingHabits['eating_habits_id'] = $last_inserted_id;
            }else{
                $eatingHabits['eating_habits_id']=$isExist->id;
            }
            
        }
        unset($data['token'], $eatingHabits['user_id'],$eatingHabits['created_on'],$eatingHabits['updated_on']);
        $response = array_merge($insert,$eatingHabits);
        return $response;
    }

}
