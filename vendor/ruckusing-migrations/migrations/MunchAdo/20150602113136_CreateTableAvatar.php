<?php

class CreateTableAvatar extends Ruckusing_Migration_Base
{
    public function up()
    {
        $this->execute("CREATE TABLE IF NOT EXISTS `avatar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `avatar` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `avatar_image` text NOT NULL,
  `message` text NOT NULL,
  `action` varchar(255) NOT NULL,
  `action_number` tinyint(4) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '0=>inactive,1=>active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;");
    }//up()

    public function down()
    {
    }//down()
}
