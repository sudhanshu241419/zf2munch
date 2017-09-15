<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use MCommons\StaticOptions;
use User\Model\UserDealsCoupons;
use Restaurant\Model\DealsCoupons;

class DealsCouponsController extends AbstractRestfulController {
	const LIVE = 1;
	const REDEEMED = 'redeemed';
	const PURCHASED = 'purchased';
	public function get($user_id) {
		$response = array ();
		$dealsCouponsModel = new UserDealsCoupons ();
		$dealsCoupons = $dealsCouponsModel->myDealsCoupons ( $user_id );
		$total_deals_coupons = count ( $dealsCoupons );
		$response = $dealsCoupons;
		
		return $response;
	}
	public function create($data) {
		$dealsCouponsModel = new UserDealsCoupons ();
		$restaurantDealsCouponsModel = new DealsCoupons ();
		
		if (! empty ( $data ['id'] )) {
			
			if (empty ( $data ['id'] ) || empty ( $data ['order_id'] ) || empty ( $data ['quantity'] )) {
				return $this->sendError ( array (
						'error' => 'Please post all the mandatory data to redeem this deal/coupon' 
				), 500 );
			}
			
			$userDealCoupon = $dealsCouponsModel->findDealsCoupons ( $data ['id'] );
			$restaurant_id = $userDealCoupon->restaurant_id;
			$deal_id = $userDealCoupon->deal_id;
		} else {
			$deal_id = $data ['deal_id'];
		}
		
		if (! empty ( $deal_id ) && $deal_id > 0) {
			$dealsDetails = $restaurantDealsCouponsModel->findDealsCoupons ( $deal_id );
		} else {
			return $this->sendError ( array (
					'error' => 'Deal or coupon does not exist, please check the id: ' . $deal_id 
			), 500 );
		}
		
		if (empty ( $data ['id'] ) && ! empty ( $data ['deal_id'] )) {
			
			if (empty ( $data ['user_id'] ) || empty ( $data ['deal_id'] ) || empty ( $data ['quantity'] )) {
				return $this->sendError ( array (
						'error' => 'Please post all the mandatory data for purchaging a deal/coupon' 
				), 500 );
			}
			
			if (empty ( $dealsDetails )) {
				return $this->sendError ( array (
						'error' => 'Deal or coupon does not exist, please check the id: ' . $deal_id 
				), 500 );
			}
			
			if ($dealsDetails->status != self::LIVE) {
				return $this->sendError ( array (
						'error' => 'Deal or coupon does not exist, please check the status of the id: ' . $deal_id 
				), 500 );
			}
			
			$quantityNeeded = $data ['quantity'];
			$soldQuantity = $dealsDetails->sold + $quantityNeeded;
			$quantityAvalable = $dealsDetails->max_daily_quantity - $dealsDetails->sold;
			if ($dealsDetails->max_daily_quantity < $soldQuantity) {
				return $this->sendError ( array (
						'error' => 'Quantity unavailable, ' . $quantityNeeded . ' out of ' . $quantityAvalable 
				), 500 );
			}
			
			$restaurant_id = $dealsDetails->restaurant_id;
		}
		
		if (! empty ( $restaurant_id )) {
			$currDateTime = StaticOptions::getRelativeCityDateTime ( array (
					'restaurant_id' => $restaurant_id 
			) );
			$currentDateTime = $currDateTime->format ( StaticOptions::MYSQL_DATE_FORMAT );
		} else {
			$currentDateTime = StaticOptions::getDateTime ()->format ( StaticOptions::MYSQL_DATE_FORMAT );
		}
		
		if (empty ( $data ['id'] )) {
			$data ['restaurant_id'] = $dealsDetails->restaurant_id;
			$data ['title'] = $dealsDetails->title;
			$data ['price'] = $dealsDetails->price;
			$data ['type'] = substr ( $dealsDetails->type, 0, 1 );
			$data ['expiry_at'] = $dealsDetails->expired_on;
			$data ['status'] = self::PURCHASED;
			$data ['coupon_code'] = $dealsDetails->coupon_code;
			$data ['purchase_at'] = $currentDateTime;
			
			$dealsCouponsModel->exchangeArray ( $data );
			$response = $dealsCouponsModel->addtoDealsCoupons ();
			
			if (! $response) {
				return $this->sendError ( array (
						'error' => 'Unable to purchage a deal or coupon' 
				), 500 );
			}
			
			$updateData ['sold'] = $dealsDetails->sold + $quantityNeeded;
			$updateData ['updated_at'] = $currentDateTime;
			$dealsCouponsUpdated = $restaurantDealsCouponsModel->updateDealsCoupons ( $updateData, $deal_id );
		} elseif (! empty ( $data ['order_id'] )) {
			
			if ($data ['quantity'] > $userDealCoupon->quantity) {
				return $this->sendError ( array (
						'error' => 'The entered quantity is not available in your account' 
				), 500 );
			}
			
			if (strtotime ( $currentDateTime ) > strtotime ( $userDealCoupon->expiry_at )) {
				return $this->sendError ( array (
						'error' => 'This record is expired, so cannot be redeem' 
				), 500 );
			}
			
			$redeem_data = array ();
			$redeem_data ['id'] = $data ['id'];
			$redeem_data ['order_id'] = $data ['order_id'];
			$redeem_data ['redeem_at'] = $currentDateTime;
			$redeem_data ['quantity'] = $userDealCoupon->quantity - $data ['quantity'];
			$redeem_data ['status'] = self::REDEEMED;
			
			$response = $dealsCouponsModel->redeemDealCoupon ( $redeem_data, $redeem_data ['id'] );
			
			$updateData ['redeemed'] = $dealsDetails->redeemed + $data ['quantity'];
			$updateData ['updated_at'] = $currentDateTime;
			$dealsCouponsUpdated = $restaurantDealsCouponsModel->updateDealsCoupons ( $updateData, $deal_id );
		}
		
		return $response;
	}
}
