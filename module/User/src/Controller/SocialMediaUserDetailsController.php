<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\UserSocialMediaDetails;
use User\UserFunctions;

class SocialMediaUserDetailsController extends AbstractRestfulController {

    public function create($data) {
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $user_id=0;
        if ($isLoggedIn) {
            $user_id = $session->getUserId();
        }
       
        if (!$session->isLoggedIn()) {
            throw new \Exception('No Active Login Found');
        }
        //$data['socialid'] = array('8374830340','038439700');
        //$data['socialtype']='fb';
        if (!isset($data['socialtype']) || empty($data['socialtype'])) {
            throw new \Exception('Social type is required');
        }
        
        $userSocialMedeaDetails = new UserSocialMediaDetails();
        
        if (isset($data['socialid']) && !empty($data['socialid'])) {
            $socialIdArray = $data['socialid'];
            $joins = array();
             $joins [] = array(
                'name' => array(
                    'u' => 'users'
                ),
                //'on' => 'u.id = user_social_media_details.user_id',
                 'on' => new \Zend\Db\Sql\Expression("u.id = user_social_media_details.user_id and user_social_media_details.user_id!='".$user_id."'"),
                'columns' => array(
                    'id',
                    'first_name',
                    'last_name',
                    'join_date' => 'created_at',
                    'email',
                    'profile_pic' => 'display_pic_url',                    
                    
                ),
                'type' => 'inner'
            );
            $options = array(
                'columns' => array(
                    'social_id',                    
                ),
                'where' => array(
                    'user_social_media_details.social_id' => $socialIdArray,
                    'user_social_media_details.user_source'=>$data['socialtype']
                ),
                'joins'=>$joins,
            );
            $userSocialMedeaDetails->getDbTable()->setArrayObjectPrototype('ArrayObject');
            $userFunctions = new UserFunctions();
            if ($userSocialMedeaDetails->find($options)->toArray()) {
                 $response = $userSocialMedeaDetails->find($options)->toArray();
                 
                 foreach($response as $key => $val){
                    $response[$key]['profile_pic']=$userFunctions->findImageUrlNormal($val['profile_pic'], $val['id']);
                 }
                return $response;
            } else {
                throw new \Exception('User details not found');
            }
        } else {
            throw new \Exception('User details not found');
        }
    }

    public function update($id, $data) {

        $session = $this->getUserSession();
        if (!$session->isLoggedIn()) {
            throw new \Exception('No Active Login Found');
        }
        if (!isset($data['socialtype']) || empty($data['socialtype'])) {
            throw new \Exception('Social type is required');
        }
        if (!isset($data['socialid']) || empty($data['socialid'])) {
            throw new \Exception('Social id is required');
        }
       
        $userId = $session->getUserId();
        $userSocialMedeaDetails = new UserSocialMediaDetails();
        $options = array('where' => array('user_id' => $userId, 'user_source' => $data['socialtype']));
        $userSocialMedeaDetails->getDbTable()->setArrayObjectPrototype('ArrayObject');

        if ($userSocialMedeaDetails->find($options)->toArray()) {
            $response = $userSocialMedeaDetails->find($options)->toArray()[0];
            $userSocialMedeaDetails->id = $response['id'];
            $data = array('access_token' => $data['socialtoken']);
            $results = $userSocialMedeaDetails->update($data);
        } else {
            $data = array('user_id' => $userId, 'social_id' => $data['socialid'], 'access_token' => $data['socialtoken'], 'user_source' => $data['socialtype']);
            $results = $userSocialMedeaDetails->insert($data);
        }
      
        return array('success' => true);
       
    }

}
