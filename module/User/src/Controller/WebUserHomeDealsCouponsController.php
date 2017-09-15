<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\UserDealsCoupons;
use MCommons\StaticOptions;
use Restaurant\Model\Restaurant;
use Restaurant\Model\DealsCoupons;
use Restaurant\Model\City;

class WebUserHomeDealsCouponsController extends AbstractRestfulController {

    public function getList() {
        $session = $this->getUserSession();
        $selected_location = $session->getUserDetail('selected_location', false);
        $sessionData = $session->getUserDetail();
        return array(
            'all_count' => 0,
            'live_count' => 0,
            'total_deals' => 0,
            /**
             * Get User City name for Deals Coupons Home page
             */
            'city_name' => $selected_location['city_name']
        );
        $userFunctions = new UserFunctions();
        $userDealsModel = new UserDealsCoupons();
        $restaurantModel = new Restaurant();
        $dealsCouponsModel = new DealsCoupons();
        $cityModel = new City();
        $session = $this->getUserSession();
        $data = array();
        /**
         * Get User City By Session When Impliment
         */
        $cityId = 235;
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $type = $this->getQueryParams('type');
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $dealsStatus = isset($config['constants']['dealsCoupons_status']) ? $config['constants']['dealsCoupons_status'] : array();
        if ($type == 'live') {
            $data = array();
            /**
             * Get User Live Deal
             */
            $liveDeals = $userDealsModel->getHomeUserDealsCoupons($userId, $dealsStatus[0], $currentDate, $type = 'd');
            if (!empty($liveDeals) && $liveDeals != null) {
                $deals = $liveDeals->getArrayCopy();
                $deals['expiry_at'] = $userFunctions->timeLater($deals['expiry_at'], $currentDate);
                $restaurantData = $restaurantModel->findRestaurant(array(
                    'columns' => array(
                        'restaurant_name'
                    ),
                    'where' => array(
                        'id' => $deals['restaurant_id']
                    )
                ));
                $deals['restaurant_name'] = $restaurantData->restaurant_name;
                $deals['data'] = 'true';
            } else {
                /**
                 * Get Live Deals/Coupons User Select City
                 */
                $dealsCount = $dealsCouponsModel->getUserCityDealsCouponsCount($cityId, $currentDate);

                if (!empty($dealsCount) && $dealsCount != null) {
                    $deals = $dealsCount->getArrayCopy();

                    $cityName = $cityModel->getCity(array(
                        'columns' => array(
                            'city_name'
                        ),
                        'where' => array(
                            'id' => $cityId
                        )
                    ));
                    $deals['city_name'] = $cityName->city_name;
                    $deals['data'] = 'false';
                }
            }

            array_push($data, $deals);
            /**
             * Get User Live Coupon
             */
            $liveCoupons = $userDealsModel->getHomeUserDealsCoupons($userId, $dealsStatus[0], $currentDate, $type = 'c');

            if (!empty($liveCoupons) && $liveCoupons != null) {
                $coupons = $liveCoupons->getArrayCopy();
                $coupons['expiry_at'] = $userFunctions->timeLater($coupons['expiry_at'], $currentDate);
                $restaurantData = $restaurantModel->findRestaurant(array(
                    'columns' => array(
                        'restaurant_name'
                    ),
                    'where' => array(
                        'id' => $coupons['restaurant_id']
                    )
                ));
                $coupons['restaurant_name'] = $restaurantData->restaurant_name;
                $coupons['data'] = 'true';
                array_push($data, $coupons);
            } elseif (empty($liveCoupons) || $liveCoupons == null) {
                /**
                 * Get User Last Redeemed Coupons
                 */
                $lastRedeem = $userDealsModel->lastRedeemDealsCoupons($userId, $dealsStatus[3], $currentDate);
                if (!empty($lastRedeem) && $lastRedeem != null) {
                    $coupons = $lastRedeem->getArrayCopy();
                    $coupons['purchase_at'] = StaticOptions::getFormattedDateTime($coupons['purchase_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $coupons['redeem_at'] = StaticOptions::getFormattedDateTime($coupons['redeem_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $coupons['expiry_at'] = StaticOptions::getFormattedDateTime($coupons['expiry_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $restaurantData = $restaurantModel->findRestaurant(array(
                        'columns' => array(
                            'restaurant_name'
                        ),
                        'where' => array(
                            'id' => $coupons['restaurant_id']
                        )
                    ));
                    $coupons['restaurant_name'] = $restaurantData->restaurant_name;
                    $coupons['data'] = 'true';
                    array_push($data, $coupons);
                }
            } elseif (empty($lastRedeem) || $lastRedeem == null) {
                $dealsCount = $dealsCouponsModel->getUserCityDealsCouponsCount($cityId, $currentDate);
                if (!empty($dealsCount) && $dealsCount != null) {
                    $coupons = $dealsCount->getArrayCopy();

                    $cityName = $cityModel->getCity(array(
                        'columns' => array(
                            'city_name'
                        ),
                        'where' => array(
                            'id' => $cityId
                        )
                    ));
                    $coupons['city_name'] = $cityName->city_name;
                    $coupons['data'] = 'false';
                    array_push($data, $coupons);
                }
            } else {
                throw new \Exception('No Deals/Coupons Found', 404);
            }

            return $data;
        }
        /**
         * Get User All Deals/Coupons Count
         */
        if ($type == 'count') {
            $allDealsCount = $userDealsModel->userAllDealsCouponsCount($userId, $currentDate);
            if (!empty($allDealsCount) && $allDealsCount != null) {
                $allCount = $allDealsCount->getArrayCopy();
            }
            $liveDealsCount = $userDealsModel->userliveDealsCouponsCount($userId, $dealsStatus[0], $currentDate);
            if (!empty($liveDealsCount) && $liveDealsCount != null) {
                $liveCount = $liveDealsCount->getArrayCopy();
            }
            return array(
                'all_count' => $allCount['total_deals'],
                'live_count' => $liveCount['live_deals']
            );
        }
    }

}
