<?php

class CreateUserDeals extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `user_deals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `deal_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;');
    }//up()

    public function down()
    {
    }//down()
}
