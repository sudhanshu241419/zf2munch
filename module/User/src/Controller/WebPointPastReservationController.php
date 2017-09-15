<?php
namespace User\Controller;

use MCommons\Controller\AbstractRestfulController;
use User\UserFunctions;

use User\Model\User;
use Restaurant\Model\Restaurant;
use User\Model\UserReservation;
use User\Model\PointSourceDetails;
use User\Model\UserPoint;
use User\Model\UserInvitation;

class WebPointPastReservationController extends AbstractRestfulController
{
	public function update($id, $data)
	{
        $userFunction = new UserFunctions();
		$restaurants = new Restaurant();	
		$user = new User();
		$pointSourceModel = new PointSourceDetails();
		$userId = $this->getUserSession()->getUserId();
		$result = array();
		$totalInvitationPoint = 0;
		$sl = $this->getServiceLocator ();
		$config = $sl->get ( 'Config' );
		$pointID = isset ( $config ['constants'] ['point_source_detail'] ) ? $config ['constants'] ['point_source_detail'] : array ();
		
		if($userId && $id){
			$userReservationModel = new UserReservation();
			// check existing reservation of user
			$userReservationModel->id = $id;
            
				$existData = $userReservationModel->getUserReservation ( array (
						'columns' => array (
								'restaurant_id',
								'user_id',
								'time_slot',
								'receipt_no',
								'reserved_seats',
								'restaurant_name',
								'status',
								'reserved_on',
                                'order_id'
						),
						'where' => array (
								'id' => $id 
						) 
				) );
				$existData = current ( $existData );
			if ($existData) {
               if($existData['user_id'] == 0 || $existData['user_id']==NULL || empty($existData['user_id'])){
					//Associate user with Reservation
					$data = array('user_id'=>$userId);
					$response = $userReservationModel->update ($data);
                    
                    if(isset($existData['order_id']) && !empty($existData['order_id']) && $existData['order_id']>0){
                        $userOrderModel = new \User\Model\UserOrder();
                        $userOrderModel->id = $existData['order_id'];
                        $udata = array('user_id'=>$userId);                       
                        $userOrderModel->update($udata);	
                    }                    
                    
					if($existData['status']==4){			
						//Associate user with Reservation Invitation
						$reservationInvitationModel = new UserInvitation();
						$reservationInvitationModel->getDbTable ()->setArrayObjectPrototype ( 'ArrayObject' );
						$options = array (
								'columns' => array (
										'user_id',										
								),
								'where' => array (
										'reservation_id'=>$userReservationModel->id
								)
						);
						$reservationInvitationDetails = $reservationInvitationModel->find ( $options )->toArray ();
						if($reservationInvitationDetails){							
							$data = array('user_id'=>$userId);
							$where = array('reservation_id'=>$userReservationModel->id);
							$reservationInvitationModel->update($data,$where);						
						}
												
						//Calculate Invitation point
						$options = array (
								'columns' => array (
										'user_id',
										'msg_status'
								),
								'where' => array (
										'reservation_id'=>$userReservationModel->id,
										'msg_status' => '1'
								)
						);
						$reservationInvitationAcceptedDetails = $reservationInvitationModel->find ( $options )->toArray ();
												
						if(count($reservationInvitationAcceptedDetails)>0){
							$totalInvitation=count($reservationInvitationAcceptedDetails);
							$key = 'acceptReservation';
							$points = $userFunction->getAllocatedPoints ( $key );
							$totalInvitationPoint = $points ['points']*$totalInvitation;
						}
						
						//Assign point to user
						$pointId = $pointID['reserveATable'];
						$reservationPoints = $pointSourceModel->getPointSourceDetail(array(
								'column' => array(
										'points',
										'id'
								)
								,
								'where' => array(
										'id' => $pointId
								)
						));
						
						$user->id = $userId;
						$userData = $user->getUserDetail ( array (
								'column' => array (
										'points'
								),
								'where' => array (
										'id' => $userId
								)
						) );
						if(isset($userData ['points']) && !empty($userData ['points']) && $userData ['points']>0){
							$userPrevieousPoints = $userData ['points'];
						}else{
							$userPrevieousPoints = 0;
						}
						//echo $userPrevieousPoints."=>".$reservationPoints['points']."=>".$totalInvitationPoint;
						
						$userPoints = $userPrevieousPoints+$reservationPoints['points']+$totalInvitationPoint;
						$user->update ( array (
								'points' => $userPoints
						) );

						//Update user point table
						$userPointsModel = new UserPoint();
						$dataPoins = array (
								'user_id' => $userId,
								'point_source' => $reservationPoints['id'],
								'points' => $reservationPoints['points'],
								'created_at' => $existData['reserved_on'],
								'status'=>'1',								
								'points_descriptions' => 'You have upcoming plans! This calls for a celebration, here are 10 points!',
								'ref_id' => $userReservationModel->id
						);
						 
						$userPointsModel->createPointDetail ( $dataPoins );
											
						$options = array('columns'=>array('points'),'where'=>array('id'=>$userId));
						$userPoints = $user->getUserDetail($options);
                        $userPointsModelNew = new \User\Model\UserPoint();
                        $totalPoints = $userPointsModelNew->countUserPoints($userId);
						$result = array("points"=>$totalPoints[0]['points'],'reservationpoints'=>$reservationPoints['points'],'acceptedinvitationpoint'=>$totalInvitationPoint);
						
					} else {
					$options = array('columns'=>array('points'),'where'=>array('id'=>$userId));
					$userPoints = $user->getUserDetail($options);
                    $userPointsModelNew = new \User\Model\UserPoint();
                    $totalPoints = $userPointsModelNew->countUserPoints($userId);
					$result = array("points"=>$totalPoints[0]['points'],'reservationpoints'=>'0','acceptedinvitationpoint'=>'0');
				}
			
			}else{
				$result = array("points"=>'0','reservationpoints'=>'0','acceptedinvitationpoint'=>'0');
			}
		}else{
			$result = array("points"=>'0','reservationpoints'=>'0','acceptedinvitationpoint'=>'0');
		}
	}else{
		$result = array("points"=>'0','reservationpoints'=>'0','acceptedinvitationpoint'=>'0');
	}
	return $result;
 }
}