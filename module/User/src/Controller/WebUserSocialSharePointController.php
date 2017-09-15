<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\User;
use User\Model\PointSourceDetails;
use User\Model\UserPoint;
use MCommons\StaticOptions;

class WebUserSocialSharePointController extends AbstractRestfulController {

    public function create($data) { //$data(ref_id,type,token)
        $userId = $this->getUserSession()->getUserId();
        $userPoints = '';
        if ($userId) {
            $userModel = new User ();
            $pointSourceModel = new PointSourceDetails ();
            $userPointsModel = new UserPoint ();
            //$sl = $this->getServiceLocator();
            //$config = $sl->get('Config');
            //$pointID = isset($config ['constants'] ['point_source_detail']) ? $config ['constants'] ['point_source_detail'] : array();
            $userData = $userModel->getUserDetail(array(
                'column' => array(
                    'points',
                 ),
                'where' => array(
                    'id' => $userId
                )
            ));
        if($data['type']==='twitter'){
            $identifier = "twitterShare";
        }elseif($data['type']==='facebook'){
            $identifier = "facebookShare";
        }else{
           return array('result'=>False);
        }           
            //$pointId = $pointID['socialShare'];
            $socialSharePoints = $pointSourceModel->getPointSourceDetail(array(
                    'column' => array(
                        'points',
                        'id'
                    ),
                    'where' => array(
                        'identifier' => $identifier                    )
                ));
            $options = array('columns'=>array('id'),'where'=>array('user_id'=>$userId,'ref_id'=>$data['ref_id'],'point_source'=>$socialSharePoints['id']));
            $userPointDetails=$userPointsModel->getUserPointDetail($options);
            
            if(!empty($userPointDetails)){
                return array('result'=>False);
            }
                $userModel->id = $userId;
                $userPoints = $userData['points'] + $socialSharePoints['points'];
                $userModel->update(array(
                    'points' => $userPoints
                ));

               
                $dataPoins = array(
                    'user_id' => $userId,
                    'point_source' => $socialSharePoints['id'],
                    'points' => $socialSharePoints['points'],
                    'created_at' => StaticOptions::getDateTime ()->format ( StaticOptions::MYSQL_DATE_FORMAT ),
                    'status' => '1',
                    'points_descriptions' => 'You have share with '.$data['type'].'! This calls for a celebration, here are 10 points!',
                    'ref_id' => $data['ref_id']
                );
                $userPointsModel->createPointDetail($dataPoins);
                 return array("result"=>True);
            }     
            return array("result"=>False);
    }
}

   