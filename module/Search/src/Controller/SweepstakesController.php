<?php

namespace Search\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserCheckin;
use User\Model\UserReview;
use User\Model\User;
use Restaurant\Model\Restaurant;
use User\Model\UserPoints;
use User\Model\UserPoint;


class SweepstakesController extends AbstractRestfulController {

    public function getList() {
        $response['status'] = 'success';
        $req = $this->getRequest()->getQuery()->toArray();
        try {
            if (!isset($req['reqtype'])) {
                throw new \Exception('invalid reqtype');
            }
            
            switch ($req['reqtype']) {
                case 'winnerslist':
                    $response['data'] = $this->getWinnersList();
                    break;
                default:
                    throw new \Exception('invalid reqtype');
            }
        } catch (\Exception $e) {
            return array('status' => 'fail', 'data' => [], 'error' => $e->getMessage());
        }



        return $response;
    }

    private function getWinnersList() {
        $checkinModel = new UserCheckin();
        $varcheckinWinnersUserRestaurantIds=$checkinModel->getWinnersUserRestaurantIds();
        //$reviewModel=new UserReview();
       // $varReviewWinnersUserRestaurantIds=$reviewModel->getWinnersUserRestaurantIds();
        //$varReviewMenuWinnersUserRestaurantIds=$reviewModel->getMenuWinnersUserRestaurantIds();
        $restaurantUserImagesModel = new \User\Model\UserRestaurantimage();
        $restaurantUserImagesModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'id',
                'user_id','created_at'=>new \Zend\Db\Sql\Expression ('created_on'),'type' => new \Zend\Db\Sql\Expression ("'userRestaurantImage'"),'restaurant_id','image_path'=>new \Zend\Db\Sql\Expression ('image_url')
            ),
            'where' => array(
                'sweepstakes_status_winner' => '2')
        );
        $userImages = $restaurantUserImagesModel->find($options)->toArray();
        $winnerList=  array_merge($varcheckinWinnersUserRestaurantIds,$userImages);
        $winners=[];
        if(count($winnerList)>0 && !empty($winnerList)){
        foreach ($winnerList as $key => $val) {
            $sortDate[$key] = strtotime($val['created_at']);           
        }
        array_multisort($sortDate, SORT_DESC, $winnerList);
        $winners=$this->getResUserDetails($winnerList);
        } 
        return $winners;
    }
    /* This function is used to find the winner user and restaurant details
     * parameter winner list array
     */
    public function getResUserDetails($winnerList){
       $getRefineWinnerList=[];
       $getUniqueResList=array();   
       if(count($winnerList)>0 && !empty($winnerList)){
           $x=0;
            foreach($winnerList as $key=>$val){
                if(!in_array($val['restaurant_id'],$getUniqueResList)){
                $getUniqueResList[]=$val['restaurant_id'];
                $varWinnerMonth=date("F", strtotime($val['created_at']));
                $userModel=new User();
                $varFindUserDetails=$userModel->getFirstName($val['user_id']);
               
                $userPointModel=new UserPoint();
                $varFindUserPoints=$userPointModel->countUserPoints($val['user_id']); 
                $userPoint=isset($varFindUserPoints[0]['points'])?$varFindUserPoints[0]['points']:0;
                $userLavel=$this->userLavel($userPoint); 
                $restaurantModel = new Restaurant(); 
                $restaurantDetailOption = array('columns' => array('restaurant_name'), 'where' => array('id' => $val['restaurant_id']));
                $restDetail = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray(); 
                $getRefineWinnerList[$x]['month']=$varWinnerMonth;
                $getRefineWinnerList[$x]['user_name']=$varFindUserDetails; 
                $getRefineWinnerList[$x]['user_level']=$userLavel;
                $getRefineWinnerList[$x]['won_at']=isset($restDetail['restaurant_name'])?$restDetail['restaurant_name']:'';
                $getRefineWinnerList[$x]['img_url']=$val['image_path'];
                $getRefineWinnerList[$x]['win_type']=$val['type'];
                $x++;
                }
            }
        }    
        //pr($getRefineWinnerList,1)
       return $getRefineWinnerList;
    }
    
    public function userLavel($points=''){
        $lavel='';
        switch ($points){
         case $points<2000:
         $lavel='The Muncher';
         break;
         case $points>=2000:
         $lavel='The Warrior';
         break;
         case $points>=4000:
         $lavel='The Fool';
         break;
         case $points>=10000:
         $lavel='The Royal';
         break;
        }
        return $lavel;
    }

}
