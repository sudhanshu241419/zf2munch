<?php

class CreateTableUserreservationCardDetails extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `user_reservation_card_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reservation_id` int(11) NOT NULL,
  `card_number` varchar(20) DEFAULT NULL,
  `encrypt_card_number` varchar(255) DEFAULT NULL,
  `name_on_card` varchar(50) DEFAULT NULL,
  `card_type` varchar(20) DEFAULT NULL,
  `expired_on` varchar(20) DEFAULT NULL,
  `billing_zip` varchar(20) DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `stripe_card_id` varchar(255) DEFAULT NULL,
  `stripe_cus_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");     
    }//up()

    public function down()
    {
    }//down()
}
