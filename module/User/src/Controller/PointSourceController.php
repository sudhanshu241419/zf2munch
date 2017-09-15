<?php

namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\Model\PointSourceDetails;

class PointSourceController extends AbstractRestfulController {
	public function getList() {
        $pointSourceModel = new PointSourceDetails();

		$session = $this->getUserSession ();
		if ($session) {
			$login = $session->isLoggedIn ();
			if (! $login) {
				throw new \Exception ( 'No Active Login Found.' );
			}
		} else {
			throw new \Exception ( 'No Active Login Found.' );
		}
        //$pointSourceData = $pointSourceModel->getPointSource(array('columns'=>array('activity_type'=>'name', 'activity_identifier'=>'identifier', 'activity_points'=>'points')));
        $pointSourceData = array(
            array('activity_type'=>'Order takeout or delivery', 'activity_identifier' => 'orderConfirmed', 'activity_points'=> '1','description'=>'PER DOLLAR SPENT'),
            array('activity_type'=>'Make a reservation', 'activity_identifier' => 'reserveTable', 'activity_points'=> '10','description'=>''),
            array('activity_type'=>'Register via email', 'activity_identifier' => 'normalRegister', 'activity_points'=> '100','description'=>''),
            array('activity_type'=>'Register with a social account', 'activity_identifier' => 'socialRegister', 'activity_points'=> '100','description'=>''),
            //array('activity_type'=>'Register with .edu account', 'activity_identifier' => 'normalRegister', 'activity_points'=> '400','description'=>''),
            array('activity_type'=>'Friend accepts your invitation', 'activity_identifier' => 'madeFriend', 'activity_points'=> '15'),
            array('activity_type'=>'Friend joins your reservation', 'activity_identifier' => 'acceptReservation', 'activity_points'=> '5','description'=>''),
            array('activity_type'=>'Review a restaurant', 'activity_identifier' => 'i_ratereview', 'activity_points'=> '5','description'=>''),
            array('activity_type'=>'Add a restaurant to your crave list', 'activity_identifier' => 'craveitrestaurant', 'activity_points'=> '1','description'=>''),
            array('activity_type'=>'Add a food item to your crave list', 'activity_identifier' => 'craveFood', 'activity_points'=> '1','description'=>''),
            array('activity_type'=>'Mark a restaurant as "loved it"', 'activity_identifier' => 'loveRestaurant', 'activity_points'=> '1','description'=>''),
            array('activity_type'=>'Mark a food item as "loved it"', 'activity_identifier' => 'loveFood', 'activity_points'=> '1','description'=>''),
            array('activity_type'=>'Email us about a major bug on MunchAdo.com', 'activity_identifier' => 'reportError', 'activity_points'=> '5','description'=>''),
            array('activity_type'=>'Mark as "been there"', 'activity_identifier' => 'beenthererestaurant', 'activity_points'=> '1','description'=>''),
            array('activity_type'=>'Upload pictures from your Munch Ado experiences', 'activity_identifier' => 'imageApproved', 'activity_points'=> '10','description'=>''),
            //array('activity_type'=>'Facebook Share', 'activity_identifier' => 'facebookShare', 'activity_points'=> '3','description'=>''),
            //array('activity_type'=>'Twitter Share', 'activity_identifier' => 'twitterShare', 'activity_points'=> '3','description'=>''),
            //array('activity_type'=>'Reservation Accept', 'activity_identifier' => 'acceptReservation', 'activity_points'=> '5'),
            array('activity_type'=>'Check in', 'activity_identifier' => 'checkIn', 'activity_points'=> '2','description'=>''),
            array('activity_type'=>'Check in with menu item', 'activity_identifier' => 'checkIn', 'activity_points'=> '3','description'=>''),
            array('activity_type'=>'Check in with photo', 'activity_identifier' => 'checkIn', 'activity_points'=> '5','description'=>''),
            array('activity_type'=>'Check in with friend', 'activity_identifier' => 'checkIn', 'activity_points'=> '3','description'=>''),
            array('activity_type'=>'Check in with menu and photo', 'activity_identifier' => 'checkIn', 'activity_points'=> '7','description'=>''),
            array('activity_type'=>'Check in with menu and friend', 'activity_identifier' => 'checkIn', 'activity_points'=> '7','description'=>''),
            array('activity_type'=>'Check in with menu, photo and friend.', 'activity_identifier' => 'checkIn', 'activity_points'=> '7','description'=>''),
            array('activity_type'=>'Registrations with edu', 'activity_identifier' => 'eduRegister', 'activity_points'=> '400','description'=>''),
            array('activity_type'=>'Let us know about "secret" menu items on the down low', 'activity_identifier' => 'i_right_rightMark', 'activity_points'=> '25','description'=>''),
            array('activity_type'=>'Let us know about a missing menu item', 'activity_identifier' => 'i_right_rightMark', 'activity_points'=> '10','description'=>''),
            array('activity_type'=>'Email us about new, up-and-coming local chefs', 'activity_identifier' => 'i_right_rightMark', 'activity_points'=> '100','description'=>''),
            array('activity_type'=>'Correct a major mistake on MunchAdo.com', 'activity_identifier' => 'i_right_rightMark', 'activity_points'=> '100','description'=>''),
            array('activity_type'=>'Leave a tip at restaurants you\'ve tried.', 'activity_identifier' => 'leaveTip', 'activity_points'=> '3','description'=>''),
            array('activity_type'=>'Tell us everything about a local restaurant we don\'t know', 'activity_identifier' => 'i_right_rightMark', 'activity_points'=> '250','description'=>''),
            array('activity_type'=>'Check in with photo and friend', 'activity_identifier' => 'checkIn', 'activity_points'=> '7','description'=>''),
            );        
        return $pointSourceData;
	}
}