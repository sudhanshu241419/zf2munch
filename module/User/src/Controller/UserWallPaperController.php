<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use User\Model\User;
use Zend\Http\PhpEnvironment\Request;

class UserWallPaperController extends AbstractRestfulController {

    const FORCE_LOGIN = true;

    public function create($data) {
        $session = $this->getUserSession();
        if ($session->isLoggedIn()) {
            $userId = $session->getUserId();     
            $request = new Request ();
			$files = $request->getFiles ();            
            if (isset($files) && !empty($files)) {
                $response = StaticOptions::uploadUserImages($files, APP_PUBLIC_PATH, USER_IMAGE_WALLPAPER. DS . $userId . DS);
                
               if (!empty($response)) {
                   $userModel = new User ();
                   $userModel->id = $userId;
                   foreach ($response as $key => $val) {
                        $imagePath = $val['path'];
                        $arr_img_path = explode('/', $val['path']);
                        $length = count($arr_img_path);
                        $imageName = $arr_img_path [$length - 1];              
                       
                        $data1 = array(
                            'wallpaper' => $imageName,
                        );

                        $userModel->update($data1);
                        return $response = array(
                            'id' => $userId,
                            'image' => $imageName,
                            'image_path'=>$imagePath
                        );
                   }
            } else {
                throw new \Exception('Wallpaper Image not uploaded',404);
            }
        }else{
             throw new \Exception('Wallpaper Image Required',404);
        }
    }else{
         throw new \Exception('Invalid User',404);
    }
}

    public function getList() {
        $userModel = new User ();
        $session = $this->getUserSession();
        $userId = $session->getUserId();
        $response = array();
        $options = array(
            'columns' => array(
                'wallpaper'
            ),
            'where' => array(
                'id' => $userId
            )
        );
        $userModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        if ($userModel->find($options)->current()) {
            $response = $userModel->find($options)->current()->getArrayCopy();
            $response ['id'] = $userId;
            if ($response ['wallpaper'] == 'noimage.jpg' || $response['wallpaper'] == null) {
                $response ['wallpaper'] = null;
            } elseif (count(explode('/', $response ['wallpaper'])) == 1) {
                if (!strpos($response ['wallpaper'], 'http')) {
                    $response ['wallpaper'] = WEB_URL . USER_IMAGE_WALLPAPER . DS . $userId . DS . $response ['wallpaper'];
                }
            }
        }
        return $response;
    }

}
