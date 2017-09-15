<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;
use User\Model\UserDealsCoupons;
use MCommons\StaticOptions;
use Restaurant\Model\Restaurant;
use Restaurant\Model\DealsCoupons;
use Restaurant\Model\City;

class WebUserDealsCouponsController extends AbstractRestfulController {

    public function getList() {
        return array(
            'all_count' => 0,
            'live_count' => 0,
            'total_deals' => 0,
            'city_name' => null,
            'coupon_code' => null,
            'icon_type' => null,
            'type' => null,
            'restaurant_name' => null,
            'title' => null,
            'purchase_at' => null,
            'expiry_at' => null,
            'redeem_at' => null
        );
        $userFunctions = new UserFunctions();
        $userDealsModel = new UserDealsCoupons();
        $restaurantModel = new Restaurant();
        $dealsCouponsModel = new DealsCoupons();
        $cityModel = new City();
        $cityId = 235;
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $locationData = $session->getUserDetail('selected_location');
        $currentDate = $userFunctions->userCityTimeZone($locationData);
        $orderby = $this->getQueryParams('orderby', 'date');
        $page = $this->getQueryParams('page', 1);
        $limit = $this->getQueryParams('limit', 50);
        $type = $this->getQueryParams('type');
        $offset = 0;
        if ($page > 0) {
            $page = ($page < 1) ? 1 : $page;
            $offset = ($page - 1) * (SHOW_PER_PAGE);
        }
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $dealsStatus = isset($config['constants']['dealsCoupons_status']) ? $config['constants']['dealsCoupons_status'] : array();
        /**
         * Get User Live Deals Coupons List
         */
        if ($type == 'live') {
            $data = array();
            $liveDeals = $userDealsModel->userLiveDealsCoupons($userId, $dealsStatus[0], $currentDate, $orderby);
            if (!empty($liveDeals) && $liveDeals != null) {
                foreach ($liveDeals as $key => $value) {
                    $value['purchase_at'] = StaticOptions::getFormattedDateTime($value['purchase_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $value['redeem_at'] = StaticOptions::getFormattedDateTime($value['redeem_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $value['expiry_at'] = $userFunctions->timeleft($value['expiry_at'], $currentDate);
                    $restaurantData = $restaurantModel->findRestaurant(array(
                        'columns' => array(
                            'restaurant_name'
                        ),
                        'where' => array(
                            'id' => $value['restaurant_id']
                        )
                    ));
                    $value['restaurant_name'] = $restaurantData->restaurant_name;
                    $data[] = $value;
                }
            }
            return isset($data) ? $data : array();

            /**
             * Get User Archived Deals Coupons List
             */
        } elseif ($type == 'archive') {
            $archiveDeals = $userDealsModel->userArchiveDealsCoupons($userId, $dealsStatus[0], $currentDate, $orderby, $limit, $offset);
            if (!empty($archiveDeals) && $archiveDeals != null) {
                foreach ($archiveDeals as $key => $value) {
                    $value['purchase_at'] = StaticOptions::getFormattedDateTime($value['purchase_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $value['redeem_at'] = StaticOptions::getFormattedDateTime($value['redeem_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $value['expiry_at'] = StaticOptions::getFormattedDateTime($value['expiry_at'], 'Y-m-d H:i:s', 'M d, Y');
                    $restaurantData = $restaurantModel->findRestaurant(array(
                        'columns' => array(
                            'restaurant_name'
                        ),
                        'where' => array(
                            'id' => $value['restaurant_id']
                        )
                    ));
                    $value['restaurant_name'] = $restaurantData->restaurant_name;
                    $data[] = $value;
                }
            }
            return isset($data) ? $data : array();
            /**
             * Get User Total Archive Deals/Coupons Count
             */
        } elseif ($type == 'count') {
            $archiveDealsCount = $userDealsModel->userArchiveDealsCouponsCount($userId, $dealsStatus[0], $currentDate);
            return isset($archiveDealsCount) ? $archiveDealsCount->getArrayCopy() : array();
        } elseif ($type == 'city_deals') {
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
            return $deals;
        } else {
            throw new \Exception("Type is not found", 404);
        }
    }

    /**
     *
     * @see Get User Deals/Coupons Details
     */
    public function get($id) {
        $userDealsModel = new UserDealsCoupons();
        $session = $this->getUserSession();
        $isLoggedIn = $session->isLoggedIn();
        if ($isLoggedIn) {
            $userId = $session->getUserId();
        } else {
            throw new \Exception('User detail not found', 404);
        }
        $voucherDetails = $userDealsModel->getDealsCouponsDetails($id);
        if (!empty($voucherDetails) && $voucherDetails != null) {
            $details = $voucherDetails->getArrayCopy();
            $details['purchase_at'] = StaticOptions::getFormattedDateTime($details['purchase_at'], 'Y-m-d H:i:s', 'F d, Y');
            $details['redeem_at'] = StaticOptions::getFormattedDateTime($details['redeem_at'], 'Y-m-d H:i:s', 'F d, Y');
            $details['expiry_at'] = StaticOptions::getFormattedDateTime($details['expiry_at'], 'Y-m-d H:i:s', 'F d, Y');
            $details['restaurant_address'] = $details['address'] . ', ' . $details['city_name'] . ', ' . $details['state_code'] . ' ' . $details['zipcode'];
            return $details;
        } else {
            throw new \ErrorException("Deals/Coupons Not Found", 404);
        }
    }

}
