<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use User\Model\User;
use Zend\Http\PhpEnvironment\Request;

class UserChangeProfileImageController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function create($data) {
        $session = $this->getUserSession();
        if ($session->isLoggedIn()) {
            $userId = $session->getUserId();     
            $request = new Request ();
			$files = $request->getFiles ();            
            if (isset($files) && !empty($files)) {
                $response = StaticOptions::uploadUserImages($files, APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . 'profile' . DS . $userId . DS);
                
               if (!empty($response)) {
                   $userModel = new User ();
                   $userModel->id = $userId;
                   foreach ($response as $key => $val) {
                        $imagePath = $val['path'];
                        $arr_img_path = explode('/', $val['path']);
                        $length = count($arr_img_path);
                        $imageName = $arr_img_path [$length - 1];              
                       
                        $data1 = array(
                            'display_pic_url' => $imageName,
                            'display_pic_url_normal' => $imageName,
                            'display_pic_url_large' => $imageName
                        );

                        $userModel->update($data1);
                        return $response = array(
                            'id' => $userId,
                            'image' => $imageName,
                            'image_path'=>$imagePath
                        );
                   }
            } else {
                throw new \Exception('Profile image not uploaded');
            }
        }else{
             throw new \Exception('Profile Image Required');
        }
    }else{
         throw new \Exception('Invalid User');
    }
}

    public function get() {
        $userModel = new User ();
        $session = $this->getUserSession();
        $userId = $session->getUserId();
        $options = array(
            'columns' => array(
                'image' => 'display_pic_url_large'
            ),
            'where' => array(
                'id' => $userId
            )
        );
        $userModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $response = $userModel->find($options)->current()->getArrayCopy();
        $response ['id'] = $userId;
        $response ['image_base_64'] = '';
        if ($response ['image'] == 'noimage.jpg' || $response['image'] == null) {
            $response ['image'] = null;
        } elseif (count(explode('/', $response ['image'])) == 1) {
            if (!strpos($response ['image'], 'http')) {
                $response ['image'] = WEB_URL . USER_IMAGE_UPLOAD . 'profile' . DS . $userId . DS . $response ['image'];
            }
        }
        return $response;
    }

}
