<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use User\Model\User;
use User\Model\UserFriendsInvitation;
use Zend\Cache\Storage\Adapter\Redis;

class SugestedFriendListController extends AbstractRestfulController {

    const FORCE_LOGIN = true;
    protected $contactListType = false;

    public function create($data) {
        $userModel = new User();       
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
            $options = array('where' => array('id' => $userId));
            $userDetail = $userModel->getUser($options);
            $userPhone = $userDetail['phone'];
        } else {
            throw new \Exception("Ivalid user", 400);
        }

        //$data = array('email'=>array('sumca2004@gmail.com','fithyboyx@yahoo.com','burger1@munchado.in','pbhutani@hungrybuzz.info', 'sushi@munchado.in', 'sfsdfdfhure@gmail.com', 'munchtesting@gmail.com', 'sudhanshuk@munchado.in', 'sudhanshuk@hungrybuzz.info', 'test1we23@munchado.in'),'social_media'=>'G+');
        //$data = array('email'=>array('swati.awas88@gmail.com','dchandna@adventit.in','vrathore@hungrybuzz.info'),'social_media'=>'fb');
        //'dchandna@adventit.in','8787887777',
        //$data = array('phone' => array('3456789876', '8765432456', '8765154563', '6354263746', '9568765465'));
        $filterPhoneData = array();
        if (isset($data['phone']) && !empty($data['phone'])) {
            $filterPhoneData = array_filter($data['phone'], function($e) use ($userPhone) {
                return ($e !== $userPhone);
            });
        }      
        $sugestedUserList = array();
        if (!empty($filterPhoneData)) {
            $sugestedUserList = $this->createSugestedListByPhone($filterPhoneData);
        } else {
            return array();
        }
        return $sugestedUserList;
    }

    public function createSugestedListByPhone($filterPhoneData) {

        $user = new User();
        $userFriendInvitation = new UserFriendsInvitation();
        $session = $this->getUserSession();
        $userId = $session->getUserId();
        $i = 0;
        $userDetails = array();     
        $sugestedUserList = array();
        foreach ($filterPhoneData as $key => $val) {
            $userDetails = $user->getUserDetail(array('columns' => array('id', 'user_name', 'first_name', 'last_name', 'email', 'phone', 'display_pic_url'), 'where' => array('phone' => $val)));
            if (!empty($userDetails)) {               
                $invitedFriend = $userFriendInvitation->getSugetionListByPhone($userDetails['email'], $userId);
                if (!$invitedFriend) {
                    $sugestedUserList[$i]['id'] = $userDetails['id'];
                    $sugestedUserList[$i]['user_name'] = $userDetails['user_name'];
                    $sugestedUserList[$i]['first_name'] = $userDetails['first_name'];
                    $sugestedUserList[$i]['last_name'] = $userDetails['last_name'];
                    $sugestedUserList[$i]['email'] = $userDetails['email'];
                    $sugestedUserList[$i]['phone'] = $userDetails['phone'];
                    $sugestedUserList[$i]['display_pic_url'] = $userDetails['display_pic_url'];
                    $i++;
                }
            }
        }
        return $sugestedUserList;
    }

    public function check_diff_multi($array1, $array2) {
        $result = array();
        foreach ($array1 as $key => $val) {
            if (isset($array2[$key])) {
                if (is_array($val) && $array2[$key]) {
                    $result[$key] = $this->check_diff_multi($val, $array2[$key]);
                }
            } else {
                $result[$key] = $val;
            }
        }

        return $result;
    }

}
