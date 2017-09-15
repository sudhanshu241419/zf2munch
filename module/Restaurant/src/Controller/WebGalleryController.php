<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\Gallery;
use MCommons\StaticOptions;
use User\Model\UserRestaurantimage;
use User\Model\User;
use Restaurant\Model\Restaurant;
class WebGalleryController extends AbstractRestfulController {

    public function get($restaurant_id = 0) {
        $response = array();
        $gallery = array();
        $config = $this->getServiceLocator()->get('config');
        $large = (bool) $this->getQueryParams('large', false);
        $limit = (int) $this->getQueryParams('limit', 0);
        
        $restaurantModel = new Restaurant();
        $restaurantDetailOption = array('columns'=>array('rest_code'),'where'=>array('id'=>$restaurant_id));
        $restCode = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray();
        /*
         * Get gallery detail of restaurant
         */
        $galleryModel = new Gallery ();
        $response = $galleryModel->getGallery($restaurant_id, $limit)->toArray();
        foreach ($response as $key => $val) {
            if (!empty($val ['image']) && !empty($val ['rest_code'])) {
                if (!empty($val ['image'])) {
                    $imageName = substr($val ['image'], 0, - 4);
                    $imageName = ucwords(str_replace('-', ' ', $imageName));
                    $galleryArr ['title'] = $imageName;
                    $galleryArr ['image'] = $val ['image'];
                }
                $gallery [] = $galleryArr;
            }
        }
        $galleryResponse ['restaurant_images'] ['base_url'] = IMAGE_PATH;
        $restaurantUserImagesModel = new UserRestaurantimage ();
        $restaurantUserImagesModel->getDbTable()->setArrayObjectPrototype('ArrayObject');
        $options = array(
            'columns' => array(
                'image'
            ),
            'where' => array(
                'image_status' => 0,
                'status' => 1,
                'restaurant_id' => $restaurant_id,
                'image_type'=>'g'
            )
        );
        $userImages = $restaurantUserImagesModel->find($options)->toArray();
        $galleryResponse ['restaurant_images'] ['images'] = $gallery;        
        $userUploadedImage = array();
        if (!empty($userImages)) {
            foreach ($userImages as $userImage) {
                $userUploadedImage ['images'] [] = array(
                    'title' => '',
                    'image' => strtolower($restCode['rest_code']) . DS .'gallery' . DS . $userImage ['image']
                );
            }
        }
        $userUploadedImage ['base_url'] = WEB_URL . USER_IMAGE_UPLOAD;
        $galleryResponse ['user_images'] = $userUploadedImage;
        return $galleryResponse;
    }

    public function create($data) {
        if (!isset($data ['restaurant_id'])) {
            throw new \Exception('Please provide restaurant id');
        } elseif (!isset($data ['image'])) {
            throw new \Exception('Please provide the image to upload');
        }
        $session = $this->getUserSession();
        if ($session->isLoggedIn()) {
            $userId = $session->getUserId();
            $files = $this->request->getFiles();
            $restaurantModel = new Restaurant();
            $restaurantDetailOption = array('columns'=>array('rest_code','restaurant_name'),'where'=>array('id'=>$data ['restaurant_id']));
            $restCode = $restaurantModel->findRestaurant($restaurantDetailOption)->toArray();
            
            $userModel = new User();
            $userDetailOption = array('columns' => array('first_name','last_name','email'), 'where' => array('id' => $userId));
            $userDetail = $userModel->getUser($userDetailOption);
            
            $currentDate = StaticOptions::getRelativeCityDateTime(array(
                        'restaurant_id' => $data ['restaurant_id']
                    ))->format('Y-m-d h:i');
            if (!empty($files)) {                
                $response = StaticOptions::getImagePath($data ['image'], APP_PUBLIC_PATH, USER_IMAGE_UPLOAD . strtolower($restCode['rest_code']) . DS .'gallery'.DS);
                if (empty($response)) {
                    throw new \Exception('Restaurant image not updated');
                }
                $userRestaurantImageModel = new UserRestaurantimage ();
                $userRestaurantImageModel->user_id = $userId;
                $userRestaurantImageModel->restaurant_id = $data ['restaurant_id'];
                $userRestaurantImageModel->created_on = $currentDate;
                $userRestaurantImageModel->updated_on = $currentDate;
                $userRestaurantImageModel->image_url = $response;
                $userRestaurantImageModel->image = end(explode('/', $response));
                $userRestaurantImageModel->status = 2;
                $userRestaurantImageModel->image_status = 0;
                $userRestaurantImageModel->image_type = 'g';
                $userRestaurantImageModel->source='0';
                $userRestaurantImageModel->createRestaurantImage();
                
                $userFunctions = new \User\UserFunctions();
                $userFunctions->imageUploadCount = 1;
                $userFunctions->userId = $userId;
                $userFunctions->restaurantId = $data ['restaurant_id'];
                $userFunctions->restaurant_name = $restCode['restaurant_name'];
                $userFunctions->typeKey = "image_id";
                $userFunctions->typeValue = "";
                $awardsPoints = $userFunctions->dineAndMoreAwards('awardsuploadpic');
                                
                $userPoints = new \User\Model\UserPoint();
                $totalPoints = $userPoints->countUserPoints($userId);
                $redeemPoint = $totalPoints[0]['redeemed_points'];
                $userPoint = strval($totalPoints[0]['points'] - $redeemPoint);
                
                if(isset($awardsPoints['points'])){
                    $userPoint = $userPoint + $awardsPoints['points'];
                }
                $cleverTap = array(
                    "gallery_id" => $userRestaurantImageModel->id,
                    "user_id" => $userId,
                    "name" => $userDetail['first_name'],
                    "email" => $userDetail['email'],
                    "identity"=>$userDetail['email'],
                    "restaurant_name" => $restCode['restaurant_name'],
                    "restaurant_id" => $data ['restaurant_id'],
                    "eventname" => "upload_pic",
                    "earned_points" => isset($awardsPoints['points'])?$awardsPoints['points']:"10",
                    "is_register" => "yes",
                    "gallery_date" => $currentDate,
                    'image_count' => 1,
                    "event"=>1
                );

                $userFunctions->createQueue($cleverTap, 'clevertap');

                return array(
                    'points' => $userPoint
                );
            } else {
                throw new \Exception('Invalid Parameters');
            }
        } else {
            throw new \Exception('User Not Logged In');
        }
    }

}
