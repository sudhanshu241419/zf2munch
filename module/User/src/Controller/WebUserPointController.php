<?php
namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\User;
use User\Model\PointSourceDetails;
use MCommons\StaticOptions;

class WebUserPointController extends AbstractRestfulController
{

    public function getList()
    {
        $userFunctions = new UserFunctions();
        $userModel = new User();
        $pointsSourceDetailsModel = new PointSourceDetails();
        $userPoints = new \User\Model\UserPoint();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        
        $totalPoints = $userPoints->countUserPoints($userId);
        $redeemPoint = $totalPoints[0]['redeemed_points'];
        $balancePoints = $totalPoints[0]['points'] - $redeemPoint;
        $locationData = $session->getUserDetail('selected_location');
        $mypoint = "00";
        if ($balancePoints>0) {
            $mypoint = $balancePoints;
            $pointslength = (int) strlen($mypoint);
            switch ($pointslength) {
               
                case 1:
                    $mypoint = "00" . $balancePoints;
                    break;
                case 2:
                    $mypoint = "0" . $balancePoints;
                    break;
                case 3:
                    $mypoint = $balancePoints;
                    break;
                
                default:
                    $mypoint = $balancePoints;
                    break;
            }
        }
               
        $sorcePoints = $pointsSourceDetailsModel->getPoint(array(1,2,3,4,5,6,7,8,9,10,11));

        $orderPlaced = $userFunctions->getPointStringFormatNew($sorcePoints[0]['points']);
        $groupOrderPlaced = $userFunctions->getPointStringFormatNew($sorcePoints[1]['points']);
        $reserveTable = $userFunctions->getPointStringFormatNew($sorcePoints[2]['points']);
        $purchaseDealCoupons = $userFunctions->getPointStringFormatNew($sorcePoints[3]['points']);
        $inviteFriend = $userFunctions->getPointStringFormatNew($sorcePoints[4]['points']);
        $rateReview = $userFunctions->getPointStringFormatNew($sorcePoints[5]['points']);
        $postPicture = $userFunctions->getPointStringFormatNew($sorcePoints[6]['points']);
        $reportError = $userFunctions->getPointStringFormatNew($sorcePoints[7]['points']);
        $complateProfile = $userFunctions->getPointStringFormatNew($sorcePoints[8]['points']);
        $postOnFacebook = $userFunctions->getPointStringFormatNew($sorcePoints[9]['points']);
        $postOnTwitter = $userFunctions->getPointStringFormatNew($sorcePoints[10]['points']);
        $cash_available=StaticOptions::amtRedeemPoint($mypoint);
        return array(
            'points' => strval($mypoint),
            'total_points'=>strval($totalPoints[0]['points']),
            'redeemed_point'=>($redeemPoint)?strval($redeemPoint):strval(0),
            'cash_available'=>strval($cash_available),
            'order_placed' => $orderPlaced,
            'group_order_placed' => $groupOrderPlaced,
            'reserve_table' => $reserveTable,
            'purchase_deals_coupons' => $purchaseDealCoupons,
            'invite_friend' => $inviteFriend,
            'rate_review' => $rateReview,
            'post_picture' => $postPicture,
            'report_error' => $reportError,
            'complate_profile' => $complateProfile,
            'post_on_facebook' => $postOnFacebook,
            'post_on_twitter' => $postOnTwitter
        );
    }
}