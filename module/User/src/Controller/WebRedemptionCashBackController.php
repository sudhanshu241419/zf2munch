<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use User\Model\UserPoint;
use User\UserFunctions;
use User\Model\Promotions;
use User\Model\UserNotification;
class WebRedemptionCashBackController extends AbstractRestfulController {

    public function create($data) {
        $session = $this->getUserSession();
        $config = $this->getServiceLocator()->get('Config'); 
        $userModel = new \User\Model\User();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
            $uoptions = array('where'=>array('id'=>$userId));
            $userEmail = $userModel->getUserDetail($uoptions);
            $userDetail = $userModel->getUserEmailSubscriber($userEmail['email']);
        } else {
            throw new \Exception('User detail not found', 404);
        }
                      
        $promotions = new Promotions();
        $userPoint = new UserPoint();
        $userFunctions = new UserFunctions();
        $userNotificationModel = new UserNotification();
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);          
        $redemptionSpecial = $config['constants']['redemptionSpecial'];
        $options = array('where' => array('promotionId' =>$redemptionSpecial['cashback'],'promotionStatus'=>1));
        $promotionsData = $promotions->getPromotions($options);
        
        if (!$promotionsData) {
            throw new \Exception('Redemption special detail is not valid', 404);
        }
        
        $totalPoints = $userPoint->countUserPoints($userId);
        $previousRedeemPoint = ($totalPoints[0]['redeemed_points'] > 0) ? intval($totalPoints[0]['redeemed_points']) : intval(0);
        $balancePoint = $totalPoints[0]['points'] - $previousRedeemPoint;
        $redeemPoint = $promotionsData[0]['promotionPoints'];
        $promotionId = $promotionsData[0]['promotionId'];
        if ($redeemPoint > $balancePoint) {
            throw new \Exception('You have not balanced to redeem point', 404);
        }
        
        $pointDescription = "Redemption using " . $promotionsData[0]['promotionName'];
        $insertData = array(
            'user_id' => $userId,
            'point_source' => '47',
            'points_descriptions' => $pointDescription,
            'redeemPoint' => $redeemPoint,
            'promotionId' => $promotionId,
            'points' => '0',
            'created_at' => $currentDate,
            'status' => '1');
        if ($userPoint->createPointDetail($insertData)) {
            $munchadoDebitCard = new \User\Model\MunchAdoDebitCard();
            $munchAdoCardDetail = $munchadoDebitCard->countUserMunchAdoCard($userId, $currentDate);
            if ($munchAdoCardDetail) {
                $message = 2;
                $cardNumber = $munchAdoCardDetail;
                $template = 'redemption_cashback';
            } else {
                $message = 1;
                $cardNumber = '';
                $template = 'newcard';
            }
            
            ############# Send Mail to user #################
            $webUrl = PROTOCOL . $config ['constants'] ['web_url'];
            $layout = 'default_new';
            $subject = 'Congrats! $'.intval($promotionsData[0]['promotionCategoryAmount']).' Cash Back is credited in your account.';
            $variables = array(
                'first_name' => ucfirst($userDetail['first_name']),
                'cash_back'=>intval($promotionsData[0]['promotionCategoryAmount']),
                'web_url'=>$webUrl
            );         
            $mailData = array('recievers' => $userDetail['email'], 'template' => $template, 'layout' => $layout, 'variables' => $variables, 'subject' => $subject);
            $userFunctions->emailSubscription($mailData);
            ###################################################
            //if ($munchAdoCardDetail) {
            //$notificationMsg = "You redeemed, we abliged. We've adeed cash-like currency to your card.";
            //$channel = "mymunchado_".$userId;
              //  $notificationArray = array(
                //    "msg" => $notificationMsg,
                  //  "channel" => $channel,
                   // "userId" => $userId,
                   // "type" => 'others',
                   // "restaurantId" => 0,
                   // 'curDate' => $currentDate,
                   // 'username'=>ucfirst($userDetail['first_name'])
                //);
                //$notificationJsonArray = array("userId" => $userId,'username'=>ucfirst($userDetail['first_name']),"user_id" => $userId);
                //$responsePubnub = $userNotificationModel->createPubNubNotification($notificationArray,$notificationJsonArray);
                //$pubnub = StaticOptions::pubnubPushNotification($notificationArray); 
            //}
            ///////////Update Point Reminder set to 0/////
            $update = new \Zend\Db\Sql\Update();
            $users = new \User\Model\User();
            $update->table($users->getDbTable()->getTableName());
            $where = new \Zend\Db\Sql\Where();
            $where->equalTo('id', $userId);
            $update->where($where);
            $update->set(array('pointsreminder' => 0));
            $rows_affected = $users->getDbTable()->getWriteGateway()->updateWith($update);
            ########### User Transaction Detail ###############
            $userTransaction = new \User\Model\UserTransactions();
            $transactionData = array(
            'user_id'=>$userId,
            'transaction_type'=>'credit',
            'transaction_amount'=> $promotionsData[0]['promotionCategoryAmount'],
            'remark' => $pointDescription,
            'transaction_date'=>$currentDate,
            );
            $userTransaction->doTransaction($transactionData);
            ###################################################
            return $returnInfo = array(
                'userId' => $userId,
                'currentRedeemPoint' => intval($redeemPoint),
                'redeemptionName' => $promotionsData[0]['promotionName'],
                'totalRedeemPoint' => $previousRedeemPoint + $redeemPoint,
                'balancePoint' => $balancePoint - $redeemPoint,
                'messageType' => $message,
                'munchAdoDebitCardNo' => $cardNumber,
                'currentDate' => $currentDate
            );
        } else {
            throw new \Exception('We are unable to save Redemption detail', 404);
        }
    }

}
