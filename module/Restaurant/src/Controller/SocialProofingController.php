<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use User\Model\UserFriends;
use Bookmark\Model\RestaurantBookmark;
use Restaurant\Model\MenuBookmark;
use User\Model\UserOrder;

class SocialProofingController extends AbstractRestfulController {

    public function get($restaurantId) {
        $userFriendModel = new UserFriends();
        $restaurantBookmark = new RestaurantBookmark();
        $menuBookmark = new MenuBookmark();
        $userOrder = new UserOrder();
        $userFunctions = new \User\UserFunctions();
        $userTip = new \User\Model\UserTip();
        $userCheckin = new \User\Model\UserCheckin();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $bookmarkTypes = array('wl', 'bt', 'lo', 'ti','tip','checkin');
        $totalLoveItFriend = 0;
        $totalBeenThereFriend = 0;
        $totalTryItFriend = 0;
        $totalCraveItFriend = 0;
        $totalOrderFriend = 0;
        $totalCheckinFriend = 0;
        $totalTipFriend= 0;
        $myDoneTryit = false;
        $myDoneOrder = false;
        $myDoneLoveIt = false;
        $myDoneCraveIt = false;
        $myDoneBeenThere = false;
        $myDoneTip = false;
        $myDoneCheckin = false;
        $ti = array();
        $lo = array();
        $wl = array();
        $bt = array();
        $tip = array();
        $checkin = array();
        $msg = '';
       
        $userId = $session->getUserId();       

        $myFriends = $userFriendModel->getUserFriendList($userId, 'name');
        $myTotalFriend = count($myFriends);
        $mySocialAction = array();
        $friendSocialAction = array();
        $friendProfilePic = array();
        foreach ($bookmarkTypes as $key => $bookmarkType) {

            if (!empty($myFriends)) {
                foreach ($myFriends as $fkey => $fdetail) {
                    
                    if ($bookmarkType == 'ti') {
                        $friendSocialActivity1 = $menuBookmark->getMenuBookmarkActivity($restaurantId, $fdetail['friend_id'], $bookmarkType);
                        if (!empty($friendSocialActivity1))
                            $friendSocialAction[$bookmarkType][] = $fdetail['friend_id'];
                        $firstName = explode('@', $fdetail['email']);
                        $ti[] = ($fdetail['first_name']) ? $fdetail['first_name'] : $firstName;
                        $totalTryItFriend = isset($friendSocialAction[$bookmarkType]) ? count($friendSocialAction[$bookmarkType]) : 0;
                        if(count(isset($friendSocialAction[$bookmarkType]) && $friendSocialAction[$bookmarkType]) > 0){
                            $friendProfilePic[]=$userFunctions->findImageUrlNormal($fdetail['display_pic_url'], $fdetail['friend_id']);
                        }
                    }
                    
                    if($bookmarkType === 'tip'){
                        $friendSocialTip = $userTip->getTipActivity($restaurantId, $fdetail['friend_id'], $bookmarkType);
                        if (!empty($friendSocialTip))
                            $friendSocialAction[$bookmarkType][] = $fdetail['friend_id'];
                        $firstName = explode('@', $fdetail['email']);
                        $tip[] = ($fdetail['first_name']) ? $fdetail['first_name'] : $firstName;
                        $totalTipFriend = isset($friendSocialAction[$bookmarkType]) ? count($friendSocialAction[$bookmarkType]) : 0;
                        if(isset($friendSocialAction[$bookmarkType]) && count($friendSocialAction[$bookmarkType]) > 0){
                            $friendProfilePic[]=$userFunctions->findImageUrlNormal($fdetail['display_pic_url'], $fdetail['friend_id']);
                        }
                    }
                    
                    if($bookmarkType === 'checkin'){
                         $friendSocialCheckin = $userCheckin->getCheckinActivity($restaurantId, $fdetail['friend_id'], $bookmarkType);
                        if (!empty($friendSocialCheckin))
                            $friendSocialAction[$bookmarkType][] = $fdetail['friend_id'];
                        $firstName = explode('@', $fdetail['email']);
                        $checkin[] = ($fdetail['first_name']) ? $fdetail['first_name'] : $firstName;
                        $totalCheckinFriend = isset($friendSocialAction[$bookmarkType]) ? count($friendSocialAction[$bookmarkType]) : 0;
                        if(isset($friendSocialAction[$bookmarkType]) && count($friendSocialAction[$bookmarkType]) > 0){
                            $friendProfilePic[]=$userFunctions->findImageUrlNormal($fdetail['display_pic_url'], $fdetail['friend_id']);
                        }
                    }

                    $option = array('restaurant_id' => $restaurantId, 'user_id' => $fdetail['friend_id'], 'type' => $bookmarkType);
                    $friendSocialActivity = $restaurantBookmark->isAlreadyBookmark($option);
                    if ($friendSocialActivity) {
                        if ($bookmarkType == 'lo') {
                            $friendSocialAction[$bookmarkType][] = $fdetail['friend_id'];
                            $firstName = explode('@', $fdetail['email']);
                            $lo[] = ($fdetail['first_name']) ? $fdetail['first_name'] : $firstName;
                            $totalLoveItFriend = isset($friendSocialAction[$bookmarkType]) ? count($friendSocialAction[$bookmarkType]) : 0;
                            if(isset($friendSocialAction[$bookmarkType]) && count($friendSocialAction[$bookmarkType]) > 0){
                                $friendProfilePic[]=$userFunctions->findImageUrlNormal($fdetail['display_pic_url'], $fdetail['friend_id']);
                            }
                        }
                        if ($bookmarkType == 'wl') {
                            $friendSocialAction[$bookmarkType][] = $fdetail['friend_id'];
                            $firstName = explode('@', $fdetail['email']);
                            $wl[] = ($fdetail['first_name']) ? $fdetail['first_name'] : $firstName;
                            $totalCraveItFriend = isset($friendSocialAction[$bookmarkType]) ? count($friendSocialAction[$bookmarkType]) : 0;
                            if(isset($friendSocialAction[$bookmarkType]) && count($friendSocialAction[$bookmarkType]) > 0){
                               $friendProfilePic[]=$userFunctions->findImageUrlNormal($fdetail['display_pic_url'], $fdetail['friend_id']);
                            }
                        }
                        if ($bookmarkType == 'bt') {
                            $friendSocialAction[$bookmarkType][] = $fdetail['friend_id'];
                            $firstName = explode('@', $fdetail['email']);
                            $bt[] = ($fdetail['first_name']) ? $fdetail['first_name'] : $firstName;
                            $totalBeenThereFriend = isset($friendSocialAction[$bookmarkType]) ? count($friendSocialAction[$bookmarkType]) : 0;
                            if(isset($friendSocialAction[$bookmarkType]) && count($friendSocialAction[$bookmarkType]) > 0){
                                $friendProfilePic[]=$userFunctions->findImageUrlNormal($fdetail['display_pic_url'], $fdetail['friend_id']);
                            }
                        }
                    }
                }
            }

            ######### Get My social Activity ########


            if ($bookmarkType == 'ti') {
                $mySocialActivity1 = $menuBookmark->getMenuBookmarkActivity($restaurantId, $userId, $bookmarkType);

                if (!empty($mySocialActivity1)) {
                    $mySocialAction[$bookmarkType] = $userId;
                    $myDoneTryit = true;
                }
            }elseif($bookmarkType == 'tip'){
                $myTipActivity = $userTip->getTipActivity($restaurantId, $userId, $bookmarkType);
                if (!empty($myTipActivity)) {
                    $mySocialAction[$bookmarkType] = $userId;
                    $myDoneTip = true;
                }
            }elseif($bookmarkType == 'checkin'){
                 $myCheckinActivity = $userCheckin->getCheckinActivity($restaurantId, $userId, $bookmarkType);
                if (!empty($myCheckinActivity)) {
                    $mySocialAction[$bookmarkType] = $userId;
                    $myDoneCheckin = true;
                }
            }else{
                $option1 = array('restaurant_id' => $restaurantId, 'user_id' => $userId, 'type' => $bookmarkType);
                $mySocialActivity = $restaurantBookmark->isAlreadyBookmark($option1);

                if ($mySocialActivity) {
                    if ($bookmarkType == 'lo') {
                        $mySocialAction[$bookmarkType] = $userId;
                        $myDoneLoveIt = true;
                    }
                    if ($bookmarkType == 'wl') {
                        $mySocialAction[$bookmarkType] = $userId;
                        $myDoneCraveIt = true;
                    }
                    if ($bookmarkType == 'bt') {
                        $mySocialAction[$bookmarkType] = $userId;
                        $myDoneBeenThere = true;
                    }
                }
            }
        }// end of foreach $bookmarkTypes
        ################################ Start Messaging ###################################
        //pr($myDoneTip,true);
        if($totalTipFriend > 2 && $myDoneTip){
            $msg = 'You and 2 others tipped others off';
        }elseif($totalTipFriend == 1 && $myDoneTip){
             $msg = 'You and Friend have left a tip or two';
        }elseif($myDoneTip){
              $msg = "You've left a tip for you and others";
            }elseif($totalTipFriend > 2){
              $msg = 'Friend and 2 others have shared tips';
        }elseif($totalTipFriend==2){
             $msg = 'Friend and Friend left a tip for you';
        }elseif($totalTipFriend==1){
             $msg = "Check out Friend's tip before you check in";
        }elseif ($totalLoveItFriend > 2 && $myDoneLoveIt) { ######### Love it ########
            $msg = 'You and 2 others love this place. But, not equally';
        } elseif ($totalLoveItFriend > 2) {
            $msg = ucfirst($lo[0]) . ' and 3 others want some more';
        } elseif ($totalLoveItFriend == 2 && $myDoneLoveIt) {
            $msg = 'You and 2 others love this place. But, not equally';
        } elseif ($totalLoveItFriend == 2) {
            $msg = ucfirst($lo[0]) . ' and ' . ucfirst($lo[1]) . ' and a food love triangle';
        } elseif ($totalLoveItFriend == 1 && $myDoneLoveIt) {
            $msg = 'You and ' . ucfirst($lo[0]) . ' have common love interests';
        } elseif ($totalLoveItFriend == 1) {
            $msg = ucfirst($lo[0]) . ' has heard of it, it must be good';
        } elseif ($myDoneLoveIt) {
            $msg = 'You hit it and could not quit it';
        } elseif ($totalBeenThereFriend > 2 && $myDoneBeenThere) { ####### Been there ####
            $msg = 'You and 2 others have come and gone';
        } elseif ($totalBeenThereFriend > 2) {
            $msg = ucfirst($bt[0]) . ' and 3 others got some';
        } elseif ($totalBeenThereFriend == 2 && $myDoneBeenThere) {
            $msg = 'You and 2 others have come and gone';
        } elseif ($totalBeenThereFriend == 2) {
            $msg = ucfirst($bt[0]) . ' and ' . ucfirst($bt[1]) . ' checked it out';
        } elseif ($totalBeenThereFriend == 1 && $myDoneBeenThere) {
            $msg = 'You and ' . ucfirst($bt[0]) . ' have been here…at the same time?';
        } elseif ($totalBeenThereFriend == 1) {
            $msg = ucfirst($bt[0]) . ' was here';
        } elseif ($myDoneBeenThere) {
            $msg = 'You’ve been here; go again?';
        } elseif ($totalTryItFriend > 2 && $myDoneTryit) {  ######## try it ########
            $msg = 'You and 2 others came, saw and ate';
        } elseif ($totalTryItFriend > 2) {
            $msg = ucfirst($ti[0]) . ' and 3 others had some';
        } elseif ($totalTryItFriend == 2 && $myDoneTryit) {
            $msg = 'You and 2 others came, saw and ate';
        } elseif ($totalTryItFriend == 2) {
            $msg = ucfirst($ti[0]) . ' and ' . ucfirst($ti[1]) . ' sampled it';
        } elseif ($totalTryItFriend == 1 && $myDoneTryit) {
            $msg = 'You and ' . ucfirst($ti[0]) . ' have both tried it out';
        } elseif ($totalTryItFriend == 1) {
            $msg = ucfirst($ti[0]) . ' gave it a shot';
        } elseif ($myDoneTryit) {
            $msg = 'You’ve tried it, did ya like it?';
        } elseif ($totalCraveItFriend > 2 && $myDoneCraveIt) {  ######## Crave it ########
            $msg = 'You and 2 others are craving this right now';
        } elseif ($totalCraveItFriend > 2) {
            $msg = ucfirst($wl[0]) . ' and 3 others want some';
        } elseif ($totalCraveItFriend == 2 && $myDoneCraveIt) {
            $msg = 'You and 2 others are craving this right now';
        } elseif ($totalCraveItFriend == 2) {
            $msg = ucfirst($wl[0]) . ' and ' . ucfirst($wl[1]) . ' are interested';
        } elseif ($totalCraveItFriend == 1 && $myDoneCraveIt) {
            $msg = 'You and ' . ucfirst($wl['0']) . ' are craving it';
        } elseif ($totalCraveItFriend == 1) {
            $msg = ucfirst($wl[0]) . ' has an eye on it';
        } elseif ($myDoneCraveIt) {
            $msg = 'You crave, they cook';
        }
        
        $var_uni = array_unique($friendProfilePic);
        $frImg=array();
        foreach($var_uni as $key => $val){
            $frImg[] = $val;
        }      
        
        ####################################################################################
        $response = (!empty($msg)) ? array('action' => $msg,'image'=>$frImg) : array('action' => '','image'=>array());
        return $response;
    }

}
