<?php

class UserPromocodes extends Ruckusing_Migration_Base
{
    public function up()
    {
    	$this->execute("CREATE TABLE IF NOT EXISTS `user_promocodes` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `promo_id` int(11) NOT NULL,
		  `order_id` int(11) NOT NULL,
		  `reedemed` tinyint(4) NOT NULL COMMENT '0=>not-redeemed,1=>redeemed',
		  `user_id` int(11) NOT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
    }//up()

    public function down()
    {
    }//down()
}
