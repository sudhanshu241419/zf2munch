<?php

class RestaurantEmailSent extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `restaurant_email_sent` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `restaurant_id` int(11) NOT NULL,
  `total_sent` int(11) DEFAULT NULL,
  `total_opened` int(11) DEFAULT NULL,
  `total_clicked` int(11) DEFAULT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
