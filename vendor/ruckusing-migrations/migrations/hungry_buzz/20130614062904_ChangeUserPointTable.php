<?php

class ChangeUserPointTable extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("ALTER TABLE `user_points` CHANGE `point_source` `point_source` SMALLINT NULL DEFAULT NULL COMMENT 'check point_source_detail table'");
    	$this->execute("CREATE TABLE IF NOT EXISTS `point_source_detail` (
						`id` int(11) NOT NULL AUTO_INCREMENT,
						`name` varchar(100) NOT NULL,
						`points` int(11) NOT NULL,
						`csskey` varchar(60) NOT NULL,
						`create_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
						PRIMARY KEY (`id`)
						) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
						");
    	$this->execute("INSERT INTO `point_source_detail` (`id`, `name`, `points`, `csskey`, `create_at`) VALUES
						(1, 'Order placed/Takeout', 250, 'dishIconPoint active', '2013-06-14 06:11:52'),
						(2, 'Group order placed', 250, 'groupIconPoint active', '2013-06-14 06:11:52'),
						(3, 'Reserve a table', 2500, 'resIconPoint active', '2013-06-14 06:11:52'),
						(4, 'Purchase a Deal/Coupon', 250, 'dealIconPoint active', '2013-06-14 06:11:52'),
						(5, 'Invite Friends', 250, 'twoFriIconPoint', '2013-06-14 06:11:52'),
						(6, 'Rate & Review', 250, 'reviewIconPoint', '2013-06-14 06:18:17'),
						(7, 'Post pictures', 250, 'postPicIconPoint', '2013-06-14 06:18:17'),
						(8, 'Report errors', 250, '', '2013-06-14 06:18:17'),
						(9, 'Complete profile', 250, 'profileIconPoint', '2013-06-14 06:18:17'),
						(10, 'Post on Facebook', 250, 'facebookIconPoint', '2013-06-14 06:18:17'),
						(11, 'Post on Twitter', 250, 'twitterIconPoint', '2013-06-14 06:19:22')
						");
    }//up()

    public function down()
    {
    	$this->execute("Drop Table point_source_detail");

    }//down()
}
