<?php

class CreateTablePointSourceDetailNew extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `point_source_detail_new` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `name` varchar(100) NOT NULL,
					  `points` int(11) NOT NULL,
					  `csskey` varchar(60) NOT NULL,
					  `created_at` datetime DEFAULT NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
    	
    	$this->execute("
    			INSERT INTO `point_source_detail_new` (`id`, `name`, `points`, `csskey`, `created_at`) VALUES
				(1, 'Order placed/Takeout', 250, 'i_order active', '2014-03-07 06:11:52'),
				(2, 'Group order placed', 250, 'i_invitefriends active', '2014-03-07 06:11:52'),
				(3, 'Reserve a table', 350, 'i_reserve_a_table', '2014-03-07 06:11:52'),
				(4, 'Purchase a Deal/Coupon', 450, 'i_purchase_a_deal', '2014-03-07 06:11:52'),
				(5, 'Invite Friends', 500, 'i_twopeople', '2014-03-07 06:11:52'),
				(6, 'Rate & Review', 600, 'i_ratereview', '2014-03-07 06:18:17'),
				(7, 'Post pictures', 700, 'i_postpictures', '2014-03-07 06:18:17'),
				(8, 'Report errors', 800, '', '2014-03-07 06:18:17'),
				(9, 'Complete profile', 900, 'i_completeprofile', '2014-03-07 06:18:17'),
				(10, 'Post on Facebook', 1000, 'i_postonfacebook', '2014-03-07 06:18:17'),
				(11, 'Post on Twitter', 1200, 'i_postontwitter', '2014-03-07 06:19:22');
    			");
    }//up()

    public function down()
    {
    }//down()
}
