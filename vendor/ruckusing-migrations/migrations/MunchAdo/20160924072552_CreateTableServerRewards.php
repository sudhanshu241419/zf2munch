<?php

class CreateTableServerRewards extends Ruckusing_Migration_Base
{
    public function up() {
        $this->execute("CREATE TABLE IF NOT EXISTS `server_rewards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(11) NOT NULL,
  `reward` varchar(255) NOT NULL,
  `earning` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;");
    }

    public function down()
    {
    }//down()
}
