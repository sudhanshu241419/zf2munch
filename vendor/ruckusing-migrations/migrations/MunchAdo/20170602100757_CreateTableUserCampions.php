<?php

class CreateTableUserCampions extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `user_campions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `destination` varchar(255) NOT NULL,
  `campion_code` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `status` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
    }//up()

    public function down()
    {
    }//down()
}
