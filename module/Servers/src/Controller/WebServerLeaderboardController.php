<?php

namespace Servers\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\RestaurantServer;
use User\Model\UserPoints;
use User\Model\UserReferrals;
use Restaurant\Model\Restaurant;
use Servers\Model\ServerRewards;
use Servers\Model\Servers;

class WebServerLeaderboardController extends AbstractRestfulController {

    public function getList() {
        $month = $this->getQueryParams('month', false);
        $year = $this->getQueryParams('year', false);
        if ($month == 8) {
            $startDate = '2016-08-01 00:00:00';
            $endDate = '2016-08-31 11:59:59';
        } else if ($month == 9) {
            $startDate = '2016-09-01 00:00:00';
            $endDate = '2016-09-30 11:59:59';
        } else if ($month == 10) {
            $startDate = '2016-10-01 00:00:00';
            $endDate = '2016-10-31 11:59:59';
        } else if ($month == 11) {
            $startDate = '2016-11-01 00:00:00';
            $endDate = '2016-11-30 11:59:59';
        } else if ($month == 12) {
            $startDate = '2016-12-01 00:00:00';
            $endDate = '2016-09-31 11:59:59';
        } else {
            $startDate = '2016-09-01 00:00:00';
            $endDate = date("Y-m-d") . " 11:59:59";
        }
        if ($year == 2016) {
            $ystartDate = '2016-08-01 00:00:00';
            $yendDate = '2016-12-31 11:59:59';
        }
        $session = $this->getUserSession();
        if ($session->isLoggedIn()) {
            $serverModel = new Servers();
            $servers = new RestaurantServer();
            $referals = new UserReferrals();
            $points = new UserPoints();
            $restModel = new Restaurant();
            $userData = $session->getUserDetail('server_user_detail');
            $serverData = $servers->getServers($startDate, $endDate);
            $localHero = $servers->getServers($ystartDate, $yendDate);
            $talentScout = $referals->gettingCustomersWhoReferMostFriends($ystartDate, $yendDate);
            $kingMaker = $points->gettingCustomersWhoEarnMostPoints($ystartDate, $yendDate);
            foreach ($talentScout as $key => $value) {
                $options = array(
                    'where' => array(
                        'id' => $value['restaurant_id']
                    )
                );
                $restDetail = $restModel->findByRestaurantId($options);
                $options1 = array(
                    'where' => array(
                        'code' => $value['code']
                    )
                );
                $serverName = $serverModel->getServerDetail($options1);
                $talentScout[$key]['server_name'] = $serverName['first_name'] . " " . $serverName['last_name'];
                $talentScout[$key]['restaurant_name'] = $restDetail->restaurant_name;
                if(empty($serverName)){
                    unset($talentScout[$key]);
                }
                
            }
            $talentScout = array_merge($talentScout);
            foreach ($kingMaker as $key => $value) {
                $options = array(
                    'where' => array(
                        'id' => $value['restaurant_id']
                    )
                );
                $restDetail = $restModel->findByRestaurantId($options);
                $options1 = array(
                    'where' => array(
                        'code' => $value['code']
                    )
                );
                $serverName = $serverModel->getServerDetail($options1);
                $kingMaker[$key]['server_name'] = $serverName['first_name'] . " " . $serverName['last_name'];
                $kingMaker[$key]['restaurant_name'] = $restDetail->restaurant_name;
                if(empty($serverName)){
                    unset($kingMaker[$key]);
                }
            }
            $kingMaker = array_merge($kingMaker);
            $options1 = array(
                'where' => array(
                    'code' => $userData['server_code']
                )
            );
            $sDetail = $serverModel->getServerDetail($options1);
            $data = array(
                'total_customers' => 0,
                'total_referals' => 0,
                'total_points' => 0,
                'date' => $sDetail['date'],
                'server_name' => $sDetail['first_name'] . " " . $sDetail['last_name']
            );
            $serverDetails = [];
            $serverCustomers = $servers->gettingServerCustomers($userData['server_code'], $startDate, $endDate);
            $serverCustomersYearly = $servers->gettingServerCustomers($userData['server_code'], $startDate, $endDate);
            $serverReferals = $referals->gettingServerReferals($userData['server_code'], $startDate, $endDate);
            $serverPoints = $points->gettingServerPoints($userData['server_code'], $startDate, $endDate);
            if (!empty($serverCustomers)) {
                $serverDetails['monthwise_customers'] = $serverCustomers[0];
            } else {
                $serverDetails['monthwise_customers'] = $data;
            }
            if (!empty($serverCustomersYearly)) {
                $serverDetails['yearwise_customers'] = $serverCustomersYearly[0];
            } else {
                $serverDetails['yearwise_customers'] = $data;
            }
            if (!empty($serverReferals)) {
                $serverDetails['yearwise_friends'] = $serverReferals[0];
            } else {
                $serverDetails['yearwise_friends'] = $data;
            }
            if (!empty($serverPoints)) {
                $serverDetails['yearwise_points'] = $serverPoints[0];
            } else {
                $serverDetails['yearwise_points'] = $data;
            }
            $rewardsModel = new ServerRewards();
            $pastWinners = $rewardsModel->getPastWinners($startDate, $endDate);
            if (!empty($pastWinners)) {
                foreach ($pastWinners as $key => $value) {
                    $date = date_create(substr($value['created_at'], 0, 10));
                    $pastWinners[$key]['created_at'] = date_format($date, "d M Y");
                }
            }
            //$pastWinnersYearly = $rewardsModel->getPastWinners($ystartDate, $yendDate);
            return array('superstar' => $serverData, 'speedster' => $serverData, 'local_hero' => $localHero, 'talent_scout' => $talentScout, 'king_maker' => $kingMaker, 'past_winners' => $pastWinners, 'server_details' => $serverDetails);
        } else {
            return array('superstar' => 0, 'speedster' => 0, 'local_hero' => 0, 'talent_scout' => 0, 'king_maker' => 0);
        }
    }

}
