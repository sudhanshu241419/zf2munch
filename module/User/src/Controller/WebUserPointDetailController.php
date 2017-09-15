<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserPoint;
use User\Model\User;
use User\UserFunctions;
use User\Model\PointSourceDetails;

class WebUserPointDetailController extends AbstractRestfulController {

    public function getList() {
        $totalCount = "0";
        $pointsSourceDetailsModel = new PointSourceDetails();
        $userFunctionModel = new UserFunctions();
        $pointModel = new UserPoint();
        $userModel = new User();
        $response = array();
        $pointSource = array();
        $archiveValue = array();
        $archiveList = array();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }

        $page = $this->getQueryParams('page', 1);
        $orderby = $this->getQueryParams('orderby');
        $type = $this->getQueryParams('type');
        $activityFromDate = $this->getQueryParams('fromDate',false);
        $activityToDate = $this->getQueryParams('toDate',false);
        $activityDate = $this->getDateForFilter($activityFromDate,$activityToDate);
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * (SHOW_PER_PAGE);
        }

        /**
         * Get User Points Archive List
         */
        if ($type == 'archive_list') {
            $options = array(
                'userId' => $userId,
                'offset' => $offset,
                'orderby' => $orderby,
                'limit' => 50
            );
            $response = $pointModel->getUsersPointsDetailList($options,$activityDate);
            if (!empty($response)) {
                
                foreach ($response as $archiveValue) {
                    $userFunctionModel->userId = $archiveValue['user_id'];
                    $userFunctionModel->restaurantId=$archiveValue['restaurant_id'];
                    $redeemPoints = ($archiveValue['redeemPoint']==NULL)?0:$archiveValue['redeemPoint'];
                    $list['id'] = intval($archiveValue['id']);
                    $list['user_id'] = intval($archiveValue['user_id']);
                    $list['restaurant_id'] = (int)$archiveValue['restaurant_id'];
                    $list['points'] = intval($archiveValue['points']);                   
                    $list['redeemPoints'] = $redeemPoints;
                    $list['status'] = intval($archiveValue['status']);
                    $list['ref_id'] = intval($archiveValue['ref_id']);
                    $list['point_source'] = intval($archiveValue['point_source']);
                    $list['readable_created_on'] = date('M d, Y', strtotime($archiveValue['created_at']));
                    $list['readable_created_at'] = $archiveValue['created_at'];
                    $list['points_descriptions'] = $archiveValue['points_descriptions'];
                    $list['css_class'] = $archiveValue['csskey'];
                    $list['dine_and_more'] = (!$userFunctionModel->isRegisterWithRestaurant($list['user_id']))?1:0;
                    $archiveList[] = $list;
                }               
            }
           
            return $archiveList;
        }
        /**
         * Get User DoMore Points
         */ elseif ($type == 'domore') {
            $response = array();
            $pointSource = array();
            $pointSource = $pointsSourceDetailsModel->getPointsSourceDetail();
            return $pointSource;
        }
        /**
         * Get User All Points
         */ elseif ($type == 'points') {
            $munchadoUserCard = new \User\Model\MunchadoUserCard();
            $session = $this->getUserSession();
            $isLoggedIn = $session->isLoggedIn();
            if (!$isLoggedIn) {
                throw new \Exception('User detail not found', 404);
            }
            $userId = $session->getUserId();
            $munchadoCard = $munchadoUserCard->fetchUserCard($userId);             
            $munchCc = isset($munchadoCard[0]['card_number'])?substr(preg_replace('/\s+/','',$munchadoCard[0]['card_number']),-4):'';
            $response = $pointModel->getUsersPointsDetailList($userId);
            $locationData = $session->getUserDetail('selected_location');
            $currentDate = $userFunctionModel->userCityTimeZone($locationData);
            $userPoints = new \User\Model\UserPoint();
            $totalPoints = $userPoints->countUserPoints($userId);

            if ($totalPoints[0]['points'] == null) {
                
                return array(
                    'points' =>intval(0), 
                    'total_point'=>0,
                    'redeemed_points' => intval(0),
                    'history_count' => 0,
                    'munchado_card'=>$munchCc
                );
            } else {
                $mypoint = $totalPoints[0]['points'];
                $pointslength = (int) strlen($totalPoints[0]['points']);
                switch ($pointslength) {

                    case 1:
                        $mypoint = "00" . $totalPoints[0]['points'];
                        break;
                    case 2:
                        $mypoint = "0" . $totalPoints[0]['points'];
                        break;
                    case 3:
                        $mypoint = $totalPoints[0]['points'];
                        break;

                    default:
                        $mypoint = $totalPoints[0]['points'];
                        break;
                }
                $points = $userFunctionModel->getNumberToString($mypoint);
                $redeemedPoints = ($totalPoints[0]['redeemed_points']>0)?intval($totalPoints[0]['redeemed_points']):intval(0);
                $count = $pointModel->getUserTotalArchiveCountNew($userId);
                if (!empty($count) && $count != null) {
                    $total = count($count);
                    $totalCount = $total; //$total['total_count'];
                }
                return array(
                    'points' => intval($mypoint),
                    'total_point'=>$points,
                    'redeemed_points'=>$redeemedPoints,
                    'history_count' => $totalCount,
                    'munchado_card'=>$munchCc
                );
            }
        } else {
            throw new \Exception("Type Not Found", 404);
        }
    }
     
    public function getDateForFilter($activityFromDate, $activityToDate) {
        $filterDate = array();
        $currentDate = strtotime(date("Y-m-d"));
        $validator = new \Zend\Validator\Date();
        
        if($activityFromDate && $activityToDate){
             $activityFromDateTimeStamp = strtotime($activityFromDate);
             $activityToDateTimeStamp = strtotime($activityToDate);
            if ($activityFromDateTimeStamp > $activityToDateTimeStamp) {
                 throw new \Exception('Enter valid activity from date and activity to date', 404);
            }
        }
        
        if ($activityFromDate) {              
            $activityFromDateTimeStamp = strtotime($activityFromDate);
            //if($activityFromDateTimeStamp > $currentDate){
                // throw new \Exception('Activity from date is not valid', 404);
            //}
            $activityFromDate = date("Y-m-d", $activityFromDateTimeStamp);
            
            if ($validator->isvalid($activityFromDate)) {
                $filterDate['fromDate'] = $activityFromDate;
            } else {
               $filterDate['fromDate'] = false;
            }
        }else{
            $filterDate['fromDate'] = false;
        }
        if($activityToDate){
            $activityToDateTimeStamp = strtotime($activityToDate);
             //if($activityToDateTimeStamp > $currentDate){
                // throw new \Exception('Activity to date is not valid', 404);
            //}
            $activityToDate = date("Y-m-d", $activityToDateTimeStamp);
            if ($validator->isvalid($activityToDate)) {
                $filterDate['toDate'] = $activityToDate;
            } else {
               $filterDate['toDate'] = false;
            }
        }else{
            $filterDate['toDate'] = false;
        }
       
       return $filterDate;
    }
 }

