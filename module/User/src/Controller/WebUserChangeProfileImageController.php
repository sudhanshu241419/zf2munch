<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use User\Model\User;
use User\Model\UserAccount;
use MCommons\CommonFunctions;

class WebUserChangeProfileImageController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function update($id, $data) {
        $session = $this->getUserSession();
        if ($session->isLoggedIn()) {
            $userId = $session->getUserId();
            if (!isset($data ['image_base_64'])) {
                throw new \Exception('Image field empty');
            }
            $image = $data ['image_base_64'];
            if (!empty($image)) {
                $response = StaticOptions::getImagePath($image, APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . 'profile' . DS . $userId . DS);
                if (empty($response)) {
                    throw new \Exception('Profile image not updated');
                }
                $imageName = array_pop(explode('/', $response));
                $userModel = new User();
                $userModel->id = $userId;
                $data = array(
                    'display_pic_url' => $imageName,
                    'display_pic_url_normal' => $imageName,
                    'display_pic_url_large' => $imageName
                );
                $userModel->update($data);
                return $response = array(
                    'id' => $userId,
                    'image' => $response,
                    'image_base_64' => ''
                );
            } else {
                throw new \Exception('Invalid Parameters');
            }
        }
    }

    public function get() {        
        $userModel = new User();
        $session = $this->getUserSession();
        $userId = $session->getUserId();
        $user_source = $this->getUserSession()->getUserDetail('user_source');
        $options = array(
            'columns' => array(
                'display_pic_url_large',
                'display_pic_url',
                'id'
            ),
            'where' => array(
                'id' => $userId
               
            )
        );
        $userModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        if($userModel->find($options)->current()){
            $user_data = $userModel->find($options)->current();
            $response ['id'] = $userId;
            $response ['image_base_64'] = '';
            $commonFunctions = new CommonFunctions();
            $userPic=$commonFunctions->checkProfileImageUrl($user_data);
            $response ['image'] =$userPic['display_pic_url_large']; 
        }else{            
           $response = array();         
        }
        return $response;
    }

}
