<?php

class CreateUserReservationInvitationTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `user_reservation_invitation` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` int(11) unsigned NOT NULL,
		  `to_id` int(11) DEFAULT NULL,
		  `restaurant_id` int(11) NOT NULL,
		  `message` text NOT NULL,
		  `msg_status` enum('0','1','2','3') NOT NULL COMMENT '0=>invited, 1=>accepted, 2=>denied, 3=>submitted',
		  `reservation_id` int(11) DEFAULT NULL,
		  `friend_email` varchar(150) DEFAULT NULL,
		  `user_type` tinyint(2) DEFAULT NULL COMMENT '0=>External,1=>Internal',
		  `created_on` datetime NULL DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `FK_user_reservation_invitation` (`user_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ") ;
    }//up()

    public function down()
    {
    	$this->execute("drop table user_reservation_invitation") ;
    }//down()
}
