<?php

namespace Restaurant\Controller;

use MCommons\Controller\AbstractRestfulController;
use Restaurant\Model\DealsCoupons;
use MCommons\StaticOptions;

class DealsCouponsController extends AbstractRestfulController {
	
	// get list deals or coupons with details of the given restaurant id
	public function get($restaurant_id) {
        $response = array ();
        $restaurantAccount = new \Restaurant\Model\RestaurantAccounts();
        $isRegisterRestaurant = $restaurantAccount->getRestaurantAccountDetail(array(
                'columns' => array(
                    'restaurant_id'
                ),
                'where' => array(
                    'restaurant_id' => $restaurant_id,
                    'status'=>1
                )
            ));
            
        if(isset($isRegisterRestaurant['restaurant_id']) && !empty($isRegisterRestaurant['restaurant_id'])){
           $dealsCouponsModel = new DealsCoupons ();
           $dealsCoupons = $dealsCouponsModel->findDetailedDeals ( $restaurant_id );
           return $dealsCoupons;
        }else{           
           return $response;
        }          
    }
	
	/**
	 * Create a new deal or coupon with the following mandatory fields
	 * restaurant_id
	 * city_id
	 * type = deals/coupons
	 * end_date
	 * coupon_code
	 */
	public function create($data) {
		
		$dealsModel = new DealsCoupons ();
		if (! empty ( $data ['id'] )) {
			$currentDealCoupon = $dealsModel->findDealsCoupons ( $data ['id'] );
			if (! $currentDealCoupon) {
				return $this->sendError ( array (
						'error' => 'No record exists for this id: ' . $data ['id'] 
				), 500 );
			} else {
				$restaurant_id = $currentDealCoupon->restaurant_id;
			}
		} elseif (empty ( $data ['restaurant_id'] ) || empty ( $data ['city_id'] ) || empty ( $data ['type'] ) || empty ( $data ['deal_for'] ) || empty ( $data ['title'] ) || empty ( $data ['description'] ) || empty ( $data ['fine_print'] ) || empty ( $data ['price'] ) || empty ( $data ['discount_type'] ) || empty ( $data ['discount'] ) || empty ( $data ['max_daily_quantity'] ) || empty ( $data ['start_on'] ) || empty ( $data ['end_date'] ) || empty ( $data ['expired_on'] )) {
			
			return $this->sendError ( array (
					'error' => 'Please post the all mandatory data' 
			), 500 );
			$restaurant_id = $data ['restaurant_id'];
		}
		
		if (! empty ( $restaurant_id )) {
			$currDateTime = StaticOptions::getRelativeCityDateTime ( array (
					'restaurant_id' => $restaurant_id 
			) );
			$currentDateTime = $currDateTime->format ( StaticOptions::MYSQL_DATE_FORMAT );
		} else {
			$currentDateTime = StaticOptions::getDateTime ()->format ( StaticOptions::MYSQL_DATE_FORMAT );
		}
		
		$data ['updated_at'] = $currentDateTime;
		
		if (! empty ( $data ['id'] )) {
			
			if (! empty ( $data ['user_id'] ) && $data ['user_id'] == 1) {
				$data ['status'] = $data ['status'];
				unset ( $data ['user_id'] );
			} else {
				$data ['status'] = $dealsModel::PROCESSING;
			}
			$id = $data ['id'];
			unset ( $data ['token'] );
			$dealCouponUpdated = $dealsModel->updateDealsCoupons ( $data, $id );
			
			if (! $dealCouponUpdated) {
				return $this->sendError ( array (
						'error' => 'Unable to update deals or coupons' 
				), 500 );
			}
			return $dealCouponUpdated;
		} else {
			$data ['created_on'] = $currentDateTime;
			$data ['status'] = $dealsModel::PROCESSING;
			$data ['sold'] = 0;
			$data ['redeemed'] = 0;
			$data ['coupon_code'] = time ();
			
			$dealsModel->exchangeArray ( $data );
			
			$response = $dealsModel->addDealsCoupons ();
			if (! $response) {
				return $this->sendError ( array (
						'error' => 'Unable to save deals or coupons' 
				), 500 );
			}
		}
		
		return $response;
	}
	public function delete($id) {
		$dealsModel = new DealsCoupons ();
		$dealsModel->id = $id;
		$deleted = $dealsModel->delete ();
		
		return array (
				"deleted" => ( bool ) $deleted 
		);
	}
}
