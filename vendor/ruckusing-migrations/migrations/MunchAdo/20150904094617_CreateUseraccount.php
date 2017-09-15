<?php

class CreateUseraccount extends Ruckusing_Migration_Base
{
    public function up()
    {
   $this->execute("CREATE TABLE IF NOT EXISTS `user_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `user_name` varchar(50) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `user_source` enum('fb','tw','ws','gp') DEFAULT NULL,
  `display_pic_url` varchar(255) DEFAULT NULL,
  `display_pic_url_normal` varchar(255) DEFAULT NULL,
  `display_pic_url_large` varchar(255) DEFAULT NULL,
  `session_token` varchar(255) DEFAULT NULL,
  `access_token` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");     
    }//up()

    public function down()
    {
    }//down()
}
