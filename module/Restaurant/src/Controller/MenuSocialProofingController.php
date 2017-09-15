<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use User\Model\UserFriends;
use Bookmark\Model\RestaurantBookmark;
use Restaurant\Model\MenuBookmark;
use User\Model\UserOrder;

class MenuSocialProofingController extends AbstractRestfulController {

    public function get($menuId) {
        $userFriendModel = new UserFriends();
        $restaurantBookmark = new RestaurantBookmark();
        $menuBookmark = new MenuBookmark();
        $userOrder = new UserOrder();
        $userTip = new \User\Model\UserTip();
        $userCheckIn = new \User\Model\UserCheckin();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        $bookmarkTypes = array('wi','lo', 'ti','tip');
        $totalLoveItFriend = 0;        
        $totalTryItFriend = 0;
        $totalCraveItFriend = 0;
        $totalOrderFriend = 0;
        $totalCheckInFriend = 0;
        $totalTipFriend = 0;
        $myDoneTryit = false;
        $myDoneOrder = false;
        $myDoneLoveIt = false;
        $myDoneCraveIt = false;
        $myDoneTip=false;
        $myDoneCheckIn=false;
        
        $ti = array();
        $lo = array();
        $wi = array();
        $tip = array();
        
        $msg = '';
       
        $userId = $session->getUserId();    
        $myFriends = $userFriendModel->getUserFriendList($userId, 'name');
        $myTotalFriend = count($myFriends);
        $mySocialAction = array();
        $friendSocialAction = array();
        foreach ($bookmarkTypes as $key => $bookmarkType) {

            if (!empty($myFriends)) {
                foreach ($myFriends as $fkey => $fdetail) {

                    if ($bookmarkType == 'ti') {
                        $friendSocialActivity1 = $menuBookmark->getMenuSocialProofing($menuId, $fdetail['friend_id'], $bookmarkType);
                        if (!empty($friendSocialActivity1))
                            $friendSocialAction[$bookmarkType][] = $fdetail['friend_id'];
                        $firstName = explode('@', $fdetail['email']);
                        $ti[] = ($fdetail['first_name']) ? $fdetail['first_name'] : $firstName;
                        $totalTryItFriend = isset($friendSocialAction[$bookmarkType]) ? count($friendSocialAction[$bookmarkType]) : 0;
                    }
                    if($bookmarkType == 'lo'){
                        $friendSocialActivity2 = $menuBookmark->getMenuSocialProofing($menuId, $fdetail['friend_id'], $bookmarkType);
                        if (!empty($friendSocialActivity2))
                            $friendSocialAction[$bookmarkType][] = $fdetail['friend_id'];
                        $firstName = explode('@', $fdetail['email']);
                        $lo[] = ($fdetail['first_name']) ? $fdetail['first_name'] : $firstName;
                        $totalLoveItFriend = isset($friendSocialAction[$bookmarkType]) ? count($friendSocialAction[$bookmarkType]) : 0;
                    
                    }
                    if($bookmarkType == 'wi'){
                        $friendSocialActivity3 = $menuBookmark->getMenuSocialProofing($menuId, $fdetail['friend_id'], $bookmarkType);
                        if (!empty($friendSocialActivity3))
                            $friendSocialAction[$bookmarkType][] = $fdetail['friend_id'];
                        $firstName = explode('@', $fdetail['email']);
                        $wi[] = ($fdetail['first_name']) ? $fdetail['first_name'] : $firstName;
                        $totalCraveItFriend = isset($friendSocialAction[$bookmarkType]) ? count($friendSocialAction[$bookmarkType]) : 0;
                    
                    }              
                   
                }
            }

            ######### Get My social Activity ########

            if ($bookmarkType == 'ti') {
                $mySocialActivity1 = $menuBookmark->getMenuSocialProofing($menuId, $userId, $bookmarkType);

                if (!empty($mySocialActivity1)) {
                    $mySocialAction[$bookmarkType] = $userId;
                    $myDoneTryit = true;
                }
            } elseif($bookmarkType == 'lo') {
                $mySocialActivity2 = $menuBookmark->getMenuSocialProofing($menuId, $userId, $bookmarkType);

                if (!empty($mySocialActivity2)) {
                    $mySocialAction[$bookmarkType] = $userId;
                    $myDoneLoveIt = true;
                }
            }elseif($bookmarkType == 'wi'){
                 $mySocialActivity3 = $menuBookmark->getMenuSocialProofing($menuId, $userId, $bookmarkType);

                if (!empty($mySocialActivity3)) {
                    $mySocialAction[$bookmarkType] = $userId;
                    $myDoneCraveIt = true;
                }
            }
        }// end of foreach $bookmarkTypes
        ################################ Start Messaging ###################################

        if ($totalLoveItFriend > 2 && $myDoneLoveIt) { ######### Love it ########
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
        ####################################################################################
        $response = (!empty($msg)) ? array('action' => $msg) : array('action' => '');
        return $response;
    }

}
