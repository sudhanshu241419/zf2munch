<?php

class CreateRestaurantDinein extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `restaurant_dinein` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` varchar(200) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `restaurant_name` varchar(200) NOT NULL,
  `city_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(200) NOT NULL,
  `last_name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `phone` varchar(200) NOT NULL,
  `reservation_date` datetime NOT NULL,
  `hold_time` datetime NOT NULL,
  `seats` int(11) NOT NULL,
  `alternate_date` datetime NOT NULL,
  `user_instruction` text NOT NULL,
  `restaurant_offer` text NOT NULL,
  `restaurant_instruction` text NOT NULL,
  `host_name` varchar(200) NOT NULL,
  `user_ip` varchar(200) NOT NULL,
  `is_modify` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '0=new,1=confirm,2=reject,3=alternate time,4=not respond',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
